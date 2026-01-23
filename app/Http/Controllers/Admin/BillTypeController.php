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
        if (!Auth::user()->can('Manage Jenis Bayar')) {
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
                    // show button to link to payment rate
                    $action = route('bill-type.show', $data->id);
                    return "<i class='fas fa-money-bill-wave'></i> <a href='$action'>Tarif Pembayaran</a>";
                })
                ->editColumn('type', function ($data) {
                    if ($data->type == BillType::TYPE_MONTHLY) {
                        return '<span class="badge badge-light-primary fw-bolder px-2 py-1">Bulanan</span>';
                    }
                    return '<span class="badge badge-light-success fw-bolder px-2 py-1">Bebas</span>';
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
        if (!Auth::user()->can('Create Jenis Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $banks = Bank::orderBy('name')->where('is_active', true)->get();
        $bankValue = [];
        return view('admins.bill-type.create-edit', compact('banks', 'bankValue'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BillTypeRequest $request)
    {
        if (!Auth::user()->can('Create Jenis Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        // Start the database transaction
        DB::beginTransaction();

        try {
            $validated = $request->validated();
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
    public function show(BillType $billType)
    {
        if (!Auth::user()->can('Manage Jenis Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $academicYearId = request('academic_year_id');

        // Regular Rates (Classroom Based)
        $regularRates = PaymentRate::with(['billType', 'paymentRateClassrooms.classroom.school'])
            ->where('bill_type_id', $billType->id)
            ->where('type', 'REGULAR')
            ->when($academicYearId, function ($query) use ($academicYearId) {
                $query->whereHas('billType', function ($q) use ($academicYearId) {
                    $q->where('academic_year_id', $academicYearId);
                });
            })
            ->latest()
            ->get();

        // Transfer Rates (Student Based)
        $transferRates = PaymentRate::with(['billType', 'paymentRateStudents.student'])
            ->where('bill_type_id', $billType->id)
            ->where('type', 'TRANSFER')
            ->when($academicYearId, function ($query) use ($academicYearId) {
                $query->whereHas('billType', function ($q) use ($academicYearId) {
                    $q->where('academic_year_id', $academicYearId);
                });
            })
            ->latest()
            ->get();

        $academicYears = AcademicYear::orderBy('name', 'DESC')->get();

        return view('admins.bill-type.show', compact('billType', 'academicYears', 'regularRates', 'transferRates'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BillType $billType)
    {
        if (!Auth::user()->can('Edit Jenis Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $banks = Bank::orderBy('name')->where('is_active', true)->get();
        $bankValue = $billType->billTypeBank->pluck('bank_id')->toArray();
        return view('admins.bill-type.create-edit', compact('billType', 'banks', 'bankValue'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BillTypeRequest $request, BillType $billType)
    {
        if (!Auth::user()->can('Edit Jenis Bayar')) {
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
        if (!Auth::user()->can('Delete Jenis Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $billType->billTypeBank()->delete();
        $billType->delete();
        return redirect()->route('bill-type.index')->with('success', 'Data berhasil dihapus');
    }
}
