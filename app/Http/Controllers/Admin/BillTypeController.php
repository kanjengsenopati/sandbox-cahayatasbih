<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bank;
use App\Models\BillType;
use App\Models\PaymentRate;
use App\Models\AcademicYear;
use App\Models\BillTypeBank;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\BillTypeRequest;

class BillTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()?->can('Manage Jenis Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            $data = BillType::with('billItem', 'academicYear', 'billTypeBank')
                ->when(request()->academic_year_id, function ($query) {
                    $query->where('academic_year_id', request()->academic_year_id);
                })
                ->when(request()->type, function ($query) {
                    $query->where('type', request()->type);
                })
                ->latest();

            return DataTables::of($data)
                ->addColumn('payment_rates', function ($data) {
                    // show button to link to payment rate with default academic year parameter
                    $action = route('bill-type.show', $data->id) . '?academic_year_id=' . $data->academic_year_id;
                    return "<i class='fas fa-money-bill-wave'></i> <a href='$action'>Tarif Pembayaran</a>";
                })
                ->editColumn('type', function ($data) {
                    $typeStr = $data->type == BillType::TYPE_MONTHLY ? 'Bulanan' : 'Bebas';
                    $badgeClass = $data->type == BillType::TYPE_MONTHLY ? 'primary' : 'success';
                    $inputStr = $data->payment_input_type == 'FREE' ? ' (Cicilan)' : ' (Fix)';
                    return '<span class="badge badge-light-' . $badgeClass . ' fw-bolder px-2 py-1">' . $typeStr . $inputStr . '</span>';
                })
                ->addColumn('bank', function ($data) {
                    $bank = "";
                    if ($data->billTypeBank->isEmpty()) {
                        return "<span class='badge badge-light-danger m-1'>Belum ada bank</span>";
                    } else {
                        foreach ($data->billTypeBank as $value) {
                            $bank .= "<span class='badge badge-light-info m-1'>{$value->bank?->name} - {$value->bank?->account_number}</span>";
                        }
                    }
                    return $bank;
                })
                ->addColumn('bill_item', function ($data) {
                    return $data->billItem?->name ?? '-';
                })

                ->addColumn('action', function ($data) {
                    $actionEdit = route('bill-type.edit', $data->id);
                    $actionDelete = route('bill-type.destroy', $data->id);
                    return "<div class='d-flex justify-content-center gap-2'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Jenis Bayar']) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Jenis Bayar']) .
                        "</div>";
                })
                ->rawColumns(['action', 'type', 'payment_rates', 'bank'])
                ->make(true);
        }
        $academicYears = AcademicYear::orderBy('name', 'DESC')->get();
        return view('admins.bill-type.index', compact('academicYears'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()?->can('Create Jenis Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $banks = Bank::orderBy('name')->where('is_active', true)->get();
        $bankValue = [];
        $paymentNames = $this->getPaymentNames();
        return view('admins.bill-type.create-edit', compact('banks', 'bankValue', 'paymentNames'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BillTypeRequest $request)
    {
        if (!Auth::user()?->can('Create Jenis Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        // Start the database transaction
        DB::beginTransaction();

        try {
            $validated = $request->validated();

            $existing = BillType::where('name', $validated['name'])
                ->where('academic_year_id', $validated['academic_year_id'] ?? null)
                ->when(!empty($validated['bill_item_id']), function($q) use ($validated) {
                    $q->where('bill_item_id', $validated['bill_item_id']);
                })
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                DB::rollBack();
                return redirect()->route('bill-type.show', $existing->id)->with('info', 'Jenis bayar ini sudah ada. Anda diarahkan ke halaman tarif pembayaran.');
            }

            $billType = BillType::create($validated);
            if ($request->has('bank_ids')) {
                foreach ($request->bank_ids as $bankId) {
                    $billType->billTypeBank()->create(['bank_id' => $bankId]);
                }
            }

            // Commit the transaction
            DB::commit();

            return redirect()->route('bill-type.index')->with('success', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            // Rollback the transaction
            DB::rollBack();

            // Log the error
            Log::error('Error storing bill type: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);

            return redirect()->route('bill-type.index')->with('error', 'Terjadi kesalahan saat menambahkan data');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($arg1, $arg2 = null)
    {
        $targetId = $arg1 instanceof \Illuminate\Http\Request ? $arg2 : $arg1;

        if ($targetId instanceof BillType) {
            $billType = $targetId;
        } else {
            $billType = BillType::find($targetId);
            if (!$billType) {
                return redirect()->route('bill-type.index')->with('error', 'Data tipe pembayaran tidak ditemukan atau telah dihapus.');
            }
        }

        if (!Auth::user()?->can('Manage Jenis Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $rawAcademicYearId = request('academic_year_id');
        if ($rawAcademicYearId === 'all') {
            $academicYearIds = [];
        } elseif (is_array($rawAcademicYearId)) {
            $academicYearIds = array_values(array_filter($rawAcademicYearId));
        } elseif (is_string($rawAcademicYearId) && trim($rawAcademicYearId) !== '') {
            $academicYearIds = array_values(array_filter(explode(',', $rawAcademicYearId)));
        } else {
            // Default to target bill type's academic year if no filter explicitly provided
            $academicYearIds = !empty($billType->academic_year_id) ? [$billType->academic_year_id] : [];
        }

        // Get related bill types matching Pos Bayar, Nama Pembayaran, & Tipe Pembayaran for cross-year rate filtering
        $relatedQuery = BillType::query()
            ->where('name', $billType->name);

        if (!empty($billType->bill_item_id)) {
            $relatedQuery->where('bill_item_id', $billType->bill_item_id);
        }
        if (!empty($billType->type)) {
            $relatedQuery->where('type', $billType->type);
        }
        if (!empty($billType->payment_input_type)) {
            $relatedQuery->where('payment_input_type', $billType->payment_input_type);
        }

        $relatedBillTypeIds = $relatedQuery->pluck('id')->toArray();
        if (empty($relatedBillTypeIds)) {
            $relatedBillTypeIds = [$billType->id];
        }

        // Regular Rates (Classroom Based)
        $regularRates = PaymentRate::with(['billType.academicYear', 'paymentRateClassrooms.classroom.school'])
            ->whereIn('bill_type_id', $relatedBillTypeIds)
            ->where('type', 'REGULAR')
            ->when(!empty($academicYearIds), function ($query) use ($academicYearIds) {
                $query->whereHas('billType', function ($q) use ($academicYearIds) {
                    $q->whereIn('academic_year_id', $academicYearIds);
                });
            })
            ->latest()
            ->get();

        // Transfer Rates (Student Based)
        $transferRates = PaymentRate::with(['billType.academicYear', 'paymentRateStudents.student.classroom.school'])
            ->whereIn('bill_type_id', $relatedBillTypeIds)
            ->where('type', 'TRANSFER')
            ->when(!empty($academicYearIds), function ($query) use ($academicYearIds) {
                $query->whereHas('billType', function ($q) use ($academicYearIds) {
                    $q->whereIn('academic_year_id', $academicYearIds);
                });
            })
            ->latest()
            ->get();

        $academicYears = AcademicYear::orderBy('name', 'DESC')->get();

        return view('admins.bill-type.show', compact('billType', 'academicYears', 'regularRates', 'transferRates', 'academicYearIds'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BillType $billType)
    {
        if (!Auth::user()?->can('Edit Jenis Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $banks = Bank::orderBy('name')->where('is_active', true)->get();
        $bankValue = $billType->billTypeBank->pluck('bank_id')->toArray();
        $paymentNames = $this->getPaymentNames();
        return view('admins.bill-type.create-edit', compact('billType', 'banks', 'bankValue', 'paymentNames'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BillTypeRequest $request, BillType $billType)
    {
        if (!Auth::user()?->can('Edit Jenis Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        // Start the database transaction
        DB::beginTransaction();

        try {
            $validated = $request->validated();
            $billType->update($validated);

            if ($request->has('bank_ids')) {
                $billType->billTypeBank()->delete();
                foreach ($request->bank_ids as $bankId) {
                    $billType->billTypeBank()->create(['bank_id' => $bankId]);
                }
            }

            // Commit the transaction
            DB::commit();

            return redirect()->route('bill-type.index')->with('success', 'Data berhasil diubah');
        } catch (\Exception $e) {
            // Rollback the transaction
            DB::rollBack();

            // Log the error
            Log::error('Error updating bill type: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);

            return redirect()->route('bill-type.index')->with('error', 'Terjadi kesalahan saat mengubah data');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BillType $billType)
    {
        if (!Auth::user()?->can('Delete Jenis Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $billType->billTypeBank()->delete();
        $billType->delete();
        return redirect()->route('bill-type.index')->with('success', 'Data berhasil dihapus');
    }

    /**
     * Compile dynamic payment names for the create/edit dropdown
     */
    private function getPaymentNames()
    {
        $defaultNames = [
            'SYAHRIAH',
            'LKS SEMESTER 1',
            'LKS SEMESTER 2',
            'ZARKASI',
            'KALENDER',
            'BIAYA APLIKASI',
            'REGISTRASI',
            'BIAYA UJIAN / AKHIR TAHUN',
            'INFAQ KENAIKAN KELAS'
        ];
        
        $existingNames = BillType::select('name')->distinct()->pluck('name')->toArray();
        $paymentNames = array_values(array_unique(array_merge($defaultNames, $existingNames)));
        sort($paymentNames);
        
        return $paymentNames;
    }
}
