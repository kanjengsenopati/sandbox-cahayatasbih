<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bill;
use App\Models\User;
use App\Models\School;
use App\Models\Student;
use App\Models\BillType;
use App\Models\Classroom;
use App\Models\PaymentRate;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\SendNotifWaService;
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
        try {
            DB::beginTransaction();

            $billType = BillType::findOrFail($request->bill_type_id);

            $paymentRate = $billType->paymentRates()->create([
                'amount' => $request->price,
            ]);

            // create payment rate classroom
            foreach ($request->classrooms as $classroom) {
                $paymentRate->paymentRateClassrooms()->create([
                    'classroom_id' => $classroom,
                ]);
            }

            // create payment rate item for each month
            $months = [];
            if ($billType->type == BillType::TYPE_MONTHLY) {
                $months = range(1, 12);
                foreach ($months as $month) {
                    $paymentRate->paymentRateItems()->create([
                        'month' => $month,
                        'year' => $request->{"tahun_$month"},
                        'amount' => $request->{"bulan_$month"},
                    ]);
                }
            } else {
                $months = $request->months;
                foreach ($months as $month) {
                    $paymentRate->paymentRateItems()->create([
                        'month' => $month,
                        'year' => $request->year,
                        'amount' => $request->price,
                    ]);
                }
            }

            // create bill to all student where classroom_id in classrooms
            $classrooms = Classroom::whereIn('id', $request->classrooms)->get();
            $months = [];
            if ($billType->type == BillType::TYPE_MONTHLY) {
                $months = range(1, 12);
                foreach ($months as $month) {
                    foreach ($classrooms as $classroom) {
                        foreach ($classroom->students as $student) {
                            $billAmount = $request->{"bulan_$month"}; // Get amount for current month
                            $billYear = $request->{"tahun_$month"}; // Get year for current month
                            if ($billAmount == 0) {
                                continue; // Skip if the bill amount is 0
                            }
                            $paymentRateItem = $paymentRate->paymentRateItems()->where('month', $month)->first();
                            $existingBill = $student->bills()->where('bill_type_id', $billType->id)
                                ->where('month', $month)
                                ->where('year', $billYear)
                                ->first();
                            if (!$existingBill) {
                                $student->bills()->create([
                                    'bill_type_id' => $billType->id,
                                    'classroom_id' => $classroom->id,
                                    'academic_year_id' => $billType->academic_year_id,
                                    'month' => $month,
                                    'year' => $billYear,
                                    'amount' => $billAmount,
                                    'status' => 'UNPAID',
                                    'payment_rate_item_id' => $paymentRateItem?->id ?? null,
                                ]);
                            }
                        }
                    }
                }
            } else {
                $months = $request->months;
                foreach ($months as $month) {
                    foreach ($classrooms as $classroom) {
                        foreach ($classroom->students as $student) {
                            $billAmount = $request->price; // Get amount for current month
                            if ($billAmount == 0) {
                                continue; // Skip if the bill amount is 0
                            }
                            $billYear = $request->year;
                            $paymentRateItem = $paymentRate->paymentRateItems()->where('month', $month)->first();
                            $existingBill = $student->bills()->where('bill_type_id', $billType->id)
                                ->where('month', $month)
                                ->where('year', $billYear)
                                ->first();
                            if (!$existingBill) {
                                $student->bills()->create([
                                    'bill_type_id' => $billType->id,
                                    'classroom_id' => $classroom->id,
                                    'academic_year_id' => $billType->academic_year_id,
                                    'month' => $month,
                                    'year' => $billYear,
                                    'amount' => $billAmount,
                                    'status' => 'UNPAID',
                                    'payment_rate_item_id' => $paymentRateItem?->id ?? null,
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('bill-type.index')->with('success', 'Tarif pembayaran berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
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
        $paymentRate = PaymentRate::findOrFail($id);
        $students = $this->getFilteredStudents($paymentRate, $dateRange);
        $this->calculateStudentTotals($students, $paymentRate);

        return DataTables::of($students)
            ->addColumn('classroom', fn($student) => $student->classroom->name ?? '-')
            ->addColumn('total_unpaid', fn($student) => $this->formatCurrency($student->total_unpaid))
            ->addColumn('total_paid', fn($student) => $this->formatCurrency($student->total_paid))
            ->addColumn('total', fn($student) => $this->formatCurrency($student->total))
            ->addColumn('status', fn($student) => $student->status)
            ->addColumn('action', fn($data) => $this->renderActions($data, $paymentRate->bill_type_id))
            ->addColumn('id', fn($student) => $student->id)
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    private function getFilteredStudents($paymentRate, $dateRange)
    {
        return Student::select('students.*')
            ->join('bills', 'bills.student_id', '=', 'students.id')
            ->where('bills.bill_type_id', $paymentRate->bill_type_id)
            ->whereHas('bills.paymentRateItems', fn($q) => $q->where('payment_rate_id', $paymentRate->id))
            ->when(request()->school_id && request()->school_id !== 'null', $this->schoolFilter())
            ->when(request()->classroom_id && request()->classroom_id !== 'null', $this->classroomFilter())
            ->hasSchool()
            ->orderBy('students.name', 'asc')
            ->distinct()
            ->get();
    }

    private function schoolFilter()
    {
        return function ($query) {
            return $query->join('classrooms', 'classrooms.id', '=', 'students.classroom_id')
                ->where('classrooms.school_id', request()->school_id);
        };
    }

    private function classroomFilter()
    {
        return fn($query) => $query->where('students.classroom_id', request()->classroom_id);
    }

    private function dateRangeFilter(array $dateRange)
    {
        return function ($query) use ($dateRange) {
            $query->whereBetween('bills.year', [$dateRange['startYear'], $dateRange['endYear']])
                ->whereBetween('bills.month', [$dateRange['startMonth'], $dateRange['endMonth']]);
        };
    }

    private function calculateStudentTotals(&$students, $paymentRate)
    {
        $totalPaidSum = 0;
        $totalUnpaidSum = 0;

        $students->map(function ($student) use (&$totalPaidSum, &$totalUnpaidSum, $paymentRate) {
            $bills = $student->bills()->where('bill_type_id', $paymentRate->bill_type_id)
                ->whereHas('paymentRateItems', function ($query) use ($paymentRate) {
                    $query->where('payment_rate_id', $paymentRate->id);
                });

            $total = $bills->sum('amount');
            $studentTotalPaid = $bills->where('status', Bill::STATUS_PAID)->sum('amount');
            $studentTotalUnpaid = $total - $studentTotalPaid;

            $totalPaidSum += $studentTotalPaid;
            $totalUnpaidSum += $studentTotalUnpaid;

            $student->total_paid = $studentTotalPaid;
            $student->total_unpaid = $studentTotalUnpaid;
            $student->total = $total;
            $student->status = $this->getPaymentStatus($studentTotalPaid, $total);

            return $student;
        });
    }


    private function getPaymentStatus($paid, $total)
    {
        if ($paid === 0) {
            return '<span class="badge bg-danger">Belum Bayar</span>';
        } elseif ($paid === $total) {
            return '<span class="badge bg-success">Lunas</span>';
        } else {
            return '<span class="badge bg-warning">Belum Lunas</span>';
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
        try {
            DB::beginTransaction();

            $billType = BillType::findOrFail($request->bill_type_id);
            $paymentRate = PaymentRate::findOrFail($id);

            // Update payment rate amount
            $paymentRate->update([
                'amount' => $request->price,
            ]);

            // Update payment rate items for each month
            if ($billType->type == BillType::TYPE_MONTHLY) {
                for ($month = 1; $month <= 12; $month++) {
                    $paymentRateItem = $paymentRate?->paymentRateItems()?->where('month', $month)?->first();
                    if ($paymentRateItem) {
                        $paymentRateItem->update([
                            'year' => $request->input("tahun_$month"),
                            'amount' => $request->input("bulan_$month"),
                        ]);

                        Bill::where('payment_rate_item_id', $paymentRateItem->id)
                            ->update(['amount' => $request->input("bulan_$month")]);
                    }
                }
            } else {
                foreach ($request->months as $month) {
                    $paymentRateItem = $paymentRate?->paymentRateItems()?->where('month', $month)?->first();
                    if ($paymentRateItem) {
                        $paymentRateItem->update([
                            'year' => $request->year,
                            'amount' => $request->price,
                        ]);

                        // Update corresponding bills
                        Bill::where('payment_rate_item_id', $paymentRateItem->id)
                            ->update(['amount' => $request->price]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('bill-type.index')->with('success', 'Tarif pembayaran berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
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
}
