<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bill;
use App\Models\User;
use App\Models\School;
use App\Models\Student;
use App\Models\BillType;
use App\Models\Classroom;
use App\Models\PaymentRate;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\SendNotifWaService;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\Admin\PaymentRateRequest;

class PaymentRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $billType = BillType::findOrFail(request()->get('bill_type_id'));
        $schools = School::orderBy('name')->get();
        $classroomValue = [];
        return view('admins.payment-rate.create-edit', compact('billType', 'schools', 'classroomValue'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentRateRequest $request)
    {
        // 1. ATOMIC LOCK: Mencegah tombol diklik 2x (Anti Double Submit)
        // Kunci akan aktif selama 10 detik untuk user yang sedang login ini.
        $lock = Cache::lock('store_payment_rate_' . auth()->id(), 10);

        if (!$lock->get()) {
            return redirect()->back()->with('error', 'Proses sedang berjalan, mohon tunggu sebentar...');
        }

        try {
            DB::beginTransaction();

            $billType = BillType::findOrFail($request->bill_type_id);

            // 2. Buat Parent Payment Rate
            $paymentRate = $billType->paymentRates()->create([
                'amount' => $request->price,
            ]);

            // 3. Attach Classrooms
            // Kita loop manual untuk create agar trigger event (jika ada) tetap jalan, 
            // tapi karena ini ringan, tidak masalah di loop.
            foreach ($request->classrooms as $classroomId) {
                $paymentRate->paymentRateClassrooms()->create([
                    'classroom_id' => $classroomId,
                ]);
            }

            // 4. Create Payment Rate Items & Build Memory Map
            // Kita simpan ID item yang baru dibuat ke array agar tidak perlu query ulang nanti.
            $months = ($billType->type == BillType::TYPE_MONTHLY) ? range(1, 12) : $request->months;
            $rateItemsMap = []; // Format: "bulan_tahun" => ID

            foreach ($months as $month) {
                $amount = ($billType->type == BillType::TYPE_MONTHLY) ? $request->{"bulan_$month"} : $request->price;
                $year   = ($billType->type == BillType::TYPE_MONTHLY) ? $request->{"tahun_$month"} : $request->year;

                $item = $paymentRate->paymentRateItems()->create([
                    'month'  => $month,
                    'year'   => $year,
                    'amount' => $amount,
                ]);

                // Key untuk map: "1_2026", "2_2026", dst.
                $rateItemsMap["{$month}_{$year}"] = $item->id;
            }

            // 5. DATA FETCHING (OPTIMASI BERAT)
            // Ambil semua santri dari kelas terpilih + Load Bill yang sudah ada
            // Tujuannya agar pengecekan "Bill Sudah Ada?" dilakukan di memori RAM (Cepat), bukan Database Query.
            $classrooms = Classroom::with(['students' => function ($q) use ($billType) {
                $q->select('id', 'classroom_id') // Select kolom seperlunya
                    ->with(['bills' => function ($b) use ($billType) {
                        $b->where('bill_type_id', $billType->id)
                            ->select('student_id', 'month', 'year'); // Load history tagihan tipe ini saja
                    }]);
            }])->whereIn('id', $request->classrooms)->get();

            $billsToInsert = [];
            $timestamp = now(); // Waktu create seragam

            // 6. LOGIC PEMBUATAN TAGIHAN (IN-MEMORY PROCESSING)
            foreach ($months as $month) {
                // Tentukan Tahun & Nominal
                $targetYear = ($billType->type == BillType::TYPE_MONTHLY) ? $request->{"tahun_$month"} : $request->year;
                $targetAmount = ($billType->type == BillType::TYPE_MONTHLY) ? $request->{"bulan_$month"} : $request->price;

                // Skip jika nominal 0
                if ($targetAmount == 0) continue;

                // Ambil ID Item dari Map (Tanpa Query)
                $rateItemId = $rateItemsMap["{$month}_{$targetYear}"] ?? null;

                foreach ($classrooms as $classroom) {
                    foreach ($classroom->students as $student) {

                        // Cek Duplicate via Collection (RAM), bukan DB Query
                        // "Apakah student ini sudah punya bill di bulan X tahun Y?"
                        $exists = $student->bills
                            ->where('month', $month)
                            ->where('year', $targetYear)
                            ->first();

                        if (!$exists) {
                            $billsToInsert[] = [
                                'id'                 => Str::uuid()->toString(),
                                'bill_type_id'       => $billType->id,
                                'classroom_id'       => $classroom->id,
                                'student_id'         => $student->id,
                                'academic_year_id'   => $billType->academic_year_id,
                                'month'              => $month,
                                'year'               => $targetYear,
                                'amount'             => $targetAmount,
                                'status'             => Bill::STATUS_UNPAID,
                                'payment_rate_item_id' => $rateItemId,
                                'created_at'         => $timestamp,
                                'updated_at'         => $timestamp,
                            ];
                        }
                    }
                }
            }

            // 7. BULK INSERT (EKSEKUSI FINAL)
            // Memasukkan ribuan data dalam potongan-potongan kecil (Chunk) agar aman.
            if (!empty($billsToInsert)) {
                foreach (array_chunk($billsToInsert, 500) as $chunk) {
                    Bill::insert($chunk);
                }
            }

            DB::commit();
            $lock->release(); // Lepas kunci manual agar user bisa input lagi segera jika mau

            return redirect()->route('bill-type.show', $billType->id)
                ->with('success', 'Tarif pembayaran dan tagihan berhasil digenerate.');
        } catch (\Exception $e) {
            DB::rollBack();
            $lock->release(); // Lepas kunci jika error
            Log::error("Error Generate Tagihan: " . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        if (request()->ajax()) {
            return $this->handleAjaxRequest($id);
        }

        $paymentRate = PaymentRate::findOrFail($id);

        return view('admins.payment-rate.show', compact('paymentRate'));
    }

    private function handleAjaxRequest(string $id)
    {
        $dateRange = $this->getDateRange();

        if (request()->type === 'bill') {
            return $this->getBillData($id, $dateRange);
        }

        if (request()->type === 'total') {
            return $this->getTotalData($id, $dateRange);
        }
    }

    private function getDateRange()
    {
        return [
            'startMonth' => request()->start_date ? date('n', strtotime(request()->start_date)) : null,
            'endMonth' => request()->end_date ? date('n', strtotime(request()->end_date)) : null,
            'startYear' => request()->start_date ? date('Y', strtotime(request()->start_date)) : null,
            'endYear' => request()->end_date ? date('Y', strtotime(request()->end_date)) : null,
        ];
    }

    private function getBillData(string $id, array $dateRange)
    {
        $paymentRate = PaymentRate::with(['paymentRateItems'])->findOrFail($id);

        // Get all relevant payment rate item IDs once
        $paymentRateItemIds = $paymentRate->paymentRateItems->pluck('id')->toArray();

        // Base query on Students
        $query = Student::query()
            ->select('students.*')
            ->with(['classroom' => function ($q) {
                // Optimize loading classroom
                $q->select('id', 'name');
            }])
            // Optimization: Calculate aggregates in DB using withSum/withCount or raw selects
            // We use withSum for readability and Laravel conventions
            ->withSum(['bills as total' => function ($q) use ($paymentRate, $paymentRateItemIds, $dateRange) {
                $q->where('bill_type_id', $paymentRate->bill_type_id)
                    ->whereIn('payment_rate_item_id', $paymentRateItemIds);

                if ($dateRange['startYear'] && $dateRange['endYear']) {
                    ($this->dateRangeFilter($dateRange))($q);
                }
            }], 'amount')
            ->withSum(['bills as total_paid' => function ($q) use ($paymentRate, $paymentRateItemIds, $dateRange) {
                $q->where('bill_type_id', $paymentRate->bill_type_id)
                    ->whereIn('payment_rate_item_id', $paymentRateItemIds)
                    ->where('status', Bill::STATUS_PAID);

                if ($dateRange['startYear'] && $dateRange['endYear']) {
                    ($this->dateRangeFilter($dateRange))($q);
                }
            }], 'amount')
            // Only include students who ACTUALLY have bills for this payment rate
            ->whereHas('bills', function ($q) use ($paymentRate, $paymentRateItemIds) {
                $q->where('bill_type_id', $paymentRate->bill_type_id)
                    ->whereIn('payment_rate_item_id', $paymentRateItemIds);
            });

        // Apply School Filter
        if (request()->school_id && request()->school_id !== 'null') {
            $query->whereHas('classroom', function ($q) {
                $q->where('school_id', request()->school_id);
            });
        }

        // Apply Classroom Filter
        if (request()->classroom_id && request()->classroom_id !== 'null') {
            $query->where('classroom_id', request()->classroom_id);
        }

        return DataTables::of($query)
            ->addColumn('classroom', fn($student) => $student->classroom->name ?? '-')
            ->addColumn('total_unpaid', function ($student) {
                return $this->formatCurrency(($student->total ?? 0) - ($student->total_paid ?? 0));
            })
            ->addColumn('total_paid', fn($student) => $this->formatCurrency($student->total_paid ?? 0))
            ->addColumn('total', fn($student) => $this->formatCurrency($student->total ?? 0))
            ->addColumn('status', function ($student) {
                return $this->getPaymentStatus($student->total_paid ?? 0, $student->total ?? 0);
            })
            ->addColumn('action', fn($student) => $this->renderActions($student, $paymentRate->bill_type_id))
            ->addColumn('id', fn($student) => $student->id)
            ->rawColumns(['status', 'action']) // 'action' and 'status' contain HTML
            ->make(true);
    }

    private function getPaymentStatus($paid, $total)
    {
        if ($paid == 0) {
            return '<span class="badge badge-light-danger fw-bolder px-2 py-1">Belum Bayar</span>';
        } elseif ($paid >= $total && $total > 0) {
            return '<span class="badge badge-light-success fw-bolder px-2 py-1">Lunas</span>';
        } else {
            return '<span class="badge badge-light-warning fw-bolder px-2 py-1">Belum Lunas</span>';
        }
    }

    private function getTotalData(string $id, array $dateRange)
    {
        $billQuery = Bill::where('bill_type_id', $id)
            ->when(request()->school_id && request()->school_id !== 'null', $this->schoolBillFilter())
            ->when(request()->classroom_id && request()->classroom_id !== 'null', $this->classroomBillFilter())
            ->when(request()->status === 'UNPAID', fn($query) => $query->where('status', Bill::STATUS_UNPAID))
            ->when(request()->status === 'PAID', fn($query) => $query->where('status', Bill::STATUS_PAID))
            ->when($dateRange['startYear'] && $dateRange['endYear'], $this->dateRangeFilter($dateRange));

        $total = $billQuery->sum('amount');
        $totalPaid = $billQuery->where('status', Bill::STATUS_PAID)->sum('amount');
        $totalUnpaid = $total - $totalPaid;

        return response()->json([
            'total' => number_format($total, 0, ',', '.'),
            'total_paid' => number_format($totalPaid, 0, ',', '.'),
            'realisasion_percentage' => $total == 0 ? 0 : number_format(($totalPaid / $total) * 100, 2, ',', '.') . '%',
            'total_unpaid' => number_format($totalUnpaid, 0, ',', '.')
        ]);
    }

    private function schoolBillFilter()
    {
        return fn($query) => $query->whereHas('student.classroom', fn($q) => $q->where('school_id', request()->school_id));
    }

    private function classroomBillFilter()
    {
        return fn($query) => $query->whereHas('student', fn($q) => $q->where('classroom_id', request()->classroom_id));
    }

    private function dateRangeFilter(array $dateRange)
    {
        return function ($query) use ($dateRange) {
            $startMonth = $dateRange['startMonth'];
            $endMonth = $dateRange['endMonth'];
            $startYear = $dateRange['startYear'];
            $endYear = $dateRange['endYear'];

            $query->where(function ($q) use ($startMonth, $endMonth, $startYear, $endYear) {
                if ($startYear == $endYear) {
                    $q->where('year', $startYear)
                        ->whereBetween('month', [$startMonth, $endMonth]);
                } else {
                    $q->where(function ($q2) use ($startMonth, $startYear) {
                        $q2->where('year', $startYear)
                            ->where('month', '>=', $startMonth);
                    })->orWhere(function ($q2) use ($endMonth, $endYear) {
                        $q2->where('year', $endYear)
                            ->where('month', '<=', $endMonth);
                    })->orWhereBetween('year', [$startYear + 1, $endYear - 1]);
                }
            });
        };
    }

    private function formatCurrency($amount)
    {
        return 'Rp. ' . number_format($amount, 0, ',', '.');
    }

    private function filterByStatus($query, $status, $billTypeId)
    {
        if ($status === 'UNPAID') {
            return $query->whereHas('bills', fn($q) => $q->where('status', Bill::STATUS_UNPAID)->where('bill_type_id', $billTypeId));
        } elseif ($status === 'PAID') {
            return $query->whereDoesntHave('bills', fn($q) => $q->where('status', Bill::STATUS_UNPAID)->where('bill_type_id', $billTypeId));
        }
        return $query;
    }

    private function renderActions($data, $billTypeId)
    {
        $deleteForm = '<form action="' . route('delete-student-bill') . '" method="POST" style="display:inline;">
            ' . csrf_field() . '
            ' . method_field('DELETE') . '
            <input type="hidden" name="student_id" value="' . $data->id . '">
            <input type="hidden" name="bill_type_id" value="' . $billTypeId . '">
            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Apakah Anda yakin ingin menghapus tagihan siswa ini?\')">Hapus</button>
        </form>';

        return "<div class='d-flex justify-content-center'>" . $deleteForm . "</div>";
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentRate $paymentRate)
    {
        $schools = School::orderBy('name')->get();
        $billType = $paymentRate->billType;
        $classrooms = Classroom::orderBy('name')->get();
        return view('admins.payment-rate.create-edit', compact('paymentRate', 'schools', 'billType', 'classrooms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaymentRateRequest $request, $id)
    {
        // 1. ATOMIC LOCK (Konsistensi)
        $lock = Cache::lock('update_payment_rate_' . auth()->id(), 10);

        if (!$lock->get()) {
            return redirect()->back()->with('error', 'Proses update sedang berjalan, mohon tunggu...');
        }

        DB::beginTransaction();
        try {
            $billType = BillType::findOrFail($request->bill_type_id);

            // Load PaymentRate beserta Items-nya sekaligus (Eager Load)
            // agar tidak perlu query berkali-kali di dalam loop.
            $paymentRate = PaymentRate::with('paymentRateItems')->findOrFail($id);

            // Update nominal di induk (hanya referensi)
            $paymentRate->update([
                'amount' => $request->price,
            ]);

            // Siapkan Collection Items agar pencarian di loop dilakukan di RAM (Cepat)
            // Gunakan keyBy('month') agar mudah dipanggil: $items[1], $items[2], dst.
            $items = $paymentRate->paymentRateItems->keyBy('month');

            if ($billType->type == BillType::TYPE_MONTHLY) {
                for ($month = 1; $month <= 12; $month++) {
                    // Ambil item dari memori RAM (bukan query DB lagi)
                    $item = $items->get($month);

                    if ($item) {
                        $newAmount = $request->input("bulan_$month");
                        $newYear   = $request->input("tahun_$month");

                        // 1. Update Master Data Tarif (Item)
                        $item->update([
                            'year'   => $newYear,
                            'amount' => $newAmount,
                        ]);

                        // 2. SAFETY UPDATE BILLS (PENTING!)
                        // Hanya update tagihan siswa yang statusnya MASIH UNPAID.
                        // Tagihan yang sudah PAID tidak boleh diubah agar laporan keuangan valid.
                        Bill::where('payment_rate_item_id', $item->id)
                            ->where('status', Bill::STATUS_UNPAID) // <--- PENJAGA GAWANG
                            ->update([
                                'amount' => $newAmount,
                                'year'   => $newYear // Update tahun juga jika berubah
                            ]);
                    }
                }
            } else {
                // Tipe Bebas / Non-Bulanan
                foreach ($request->months as $month) {
                    $item = $items->get($month);

                    if ($item) {
                        // 1. Update Master Data Tarif
                        $item->update([
                            'year'   => $request->year,
                            'amount' => $request->price,
                        ]);

                        // 2. SAFETY UPDATE BILLS
                        Bill::where('payment_rate_item_id', $item->id)
                            ->where('status', Bill::STATUS_UNPAID) // <--- PENJAGA GAWANG
                            ->update([
                                'amount' => $request->price,
                                'year'   => $request->year
                            ]);
                    }
                }
            }

            DB::commit();
            $lock->release();

            return redirect()->route('bill-type.show', $billType->id)
                ->with('success', 'Tarif pembayaran berhasil diperbarui. (Catatan: Tagihan yang sudah lunas tidak ikut berubah)');
        } catch (\Exception $e) {
            DB::rollBack();
            $lock->release();
            Log::error("Error Update PaymentRate: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // 1. ATOMIC LOCK
        $lock = Cache::lock('destroy_payment_rate_' . auth()->id(), 10);

        if (!$lock->get()) {
            return redirect()->back()->with('error', 'Proses penghapusan sedang berjalan, mohon tunggu...');
        }

        DB::beginTransaction();
        try {
            $paymentRate = PaymentRate::findOrFail($id);

            // 2. SAFETY CHECK: Cek apakah ada tagihan yang statusnya SUDAH DIBAYAR?
            // PERUBAHAN DI SINI: Gunakan 'paymentRateItems' (pakai 's') sesuai nama function di Model Bill Anda.
            $hasPaidBills = Bill::whereHas('paymentRateItems', function ($q) use ($id) {
                $q->where('payment_rate_id', $id);
            })->where('status', Bill::STATUS_PAID)->exists();

            if ($hasPaidBills) {
                $lock->release();
                return redirect()->back()->with('error', 'GAGAL HAPUS! Terdapat siswa yang sudah membayar tagihan ini. Harap batalkan pembayaran siswa tersebut terlebih dahulu.');
            }

            // 3. HAPUS BILLS (Direct Query)
            // Bagian ini TIDAK PERLU DIUBAH karena langsung pakai nama tabel database ('payment_rate_items'), bukan nama relasi Eloquent.
            Bill::whereIn('payment_rate_item_id', function ($query) use ($id) {
                $query->select('id')
                    ->from('payment_rate_items') // Nama tabel di database (biasanya plural)
                    ->where('payment_rate_id', $id);
            })->delete();

            // 4. Hapus Item & Classrooms
            $paymentRate->paymentRateItems()->delete();
            $paymentRate->paymentRateClassrooms()->delete();

            // 5. Hapus Induk
            $paymentRate->delete();

            DB::commit();
            $lock->release();

            return redirect()->back()->with('success', 'Data tarif dan seluruh tagihan berhasil dihapus bersih.');
        } catch (\Exception $e) {
            DB::rollBack();
            $lock->release();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function getClassroom(Request $request)
    {
        $school = School::findOrFail($request->school_id);
        $classrooms = Classroom::where('school_id', $school->id)->orderBy('name')->get();
        return response()->json($classrooms);
    }

    /**
     * Get bill details for a student
     */
    public function getBillDetails(Request $request)
    {
        try {
            $bills = Bill::where('student_id', $request->student_id)
                ->where('bill_type_id', $request->bill_type_id)
                ->whereHas('paymentRateItems', function ($q) use ($request) {
                    $q->where('payment_rate_id', $request->payment_rate_id);
                })
                ->orderByRaw("CASE 
                    WHEN month >= 7 THEN month - 6 
                    ELSE month + 6 
                END")
                ->orderBy('year')
                ->get()
                ->map(function ($bill) {
                    return [
                        'id' => $bill->id,
                        'month' => $bill->month,
                        'year' => $bill->year,
                        'amount' => $bill->amount,
                        'status' => $bill->status,
                        'translated_month' => $bill->translated_month,
                        'status_badge' => $bill->status === Bill::STATUS_PAID
                            ? '<span class="badge bg-success">Lunas</span>'
                            : '<span class="badge bg-danger">Belum Lunas</span>'
                    ];
                });

            return response()->json([
                'success' => true,
                'bills' => $bills
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data tagihan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a single bill
     */
    public function deleteBill(Request $request)
    {
        try {
            $bill = Bill::findOrFail($request->bill_id);

            if ($bill->status === Bill::STATUS_PAID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus tagihan yang sudah dibayar'
                ], 400);
            }

            $bill->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tagihan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Delete Bill Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus tagihan'
            ], 500);
        }
    }

    /**
     * Mass delete bills
     */
    public function deleteBillsMass(Request $request)
    {
        try {
            DB::beginTransaction();

            $billIds = $request->bill_ids;

            if (empty($billIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada tagihan yang dipilih'
                ], 400);
            }

            // Check if any bills are already paid
            $paidBills = Bill::whereIn('id', $billIds)
                ->where('status', Bill::STATUS_PAID)
                ->count();

            if ($paidBills > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Beberapa tagihan sudah dibayar dan tidak dapat dihapus'
                ], 400);
            }

            // Delete bills
            Bill::whereIn('id', $billIds)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menghapus ' . count($billIds) . ' tagihan'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mass Delete Bills Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus tagihan'
            ], 500);
        }
    }

    /**
     * Update a single bill amount
     */
    public function updateBill(Request $request)
    {
        try {
            $request->validate([
                'bill_id' => 'required|exists:bills,id',
                'amount' => 'required|numeric|min:0'
            ]);

            $bill = Bill::findOrFail($request->bill_id);

            if ($bill->status === Bill::STATUS_PAID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengubah tagihan yang sudah dibayar'
                ], 400);
            }

            $bill->update([
                'amount' => $request->amount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tagihan berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            Log::error('Update Bill Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui tagihan'
            ], 500);
        }
    }
}
