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
            $data = BillType::with('billItem', 'academicYear', 'billTypeBank')->latest();
            return DataTables::of($data)
                ->addColumn('payment_rates', function ($data) {
                    // show button to link to payment rate
                    $action = route('bill-type.show', $data->id);
                    return "<i class='fas fa-money-bill-wave'></i> <a href='$action'>Tarif Pembayaran</a>";
                })
                ->editColumn('type', function ($data) {
                    return $data->type == BillType::TYPE_MONTHLY ? '<span class="badge badge-primary">Bulanan</span>' :
                        '<span class="badge badge-secondary">Bebas</span>';
                })
                ->addColumn('bank', function ($data) {
                    $bank = "";
                    if ($data->billTypeBank->isEmpty()) {
                        return "<span class='badge bg-danger m-1'>Belum ada bank</span>";
                    } else {
                        foreach ($data->billTypeBank as $value) {
                            $bank .= "<span class='badge bg-success m-1'>{$value->bank?->name} - {$value->bank?->account_number}</span>";
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
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Jenis Bayar']) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Jenis Bayar']) .
                        "</div>";
                })
                ->rawColumns(['action', 'type', 'payment_rates', 'bank'])
                ->make(true);
        }
        return view('admins.bill-type.index');
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

            // If request has billTypeBanks, create new BillTypeBank entries
            if ($request->billTypeBanks) {
                $billType->billTypeBank()->createMany($request->billTypeBanks);
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
        if (request()->ajax()) {
            $academicYear = AcademicYear::whereIsActive(true)->first();
            $data = PaymentRate::with('billType', 'paymentRateClassrooms')
                ->where('bill_type_id', $billType->id)->whereHas('billType', function ($query) use ($academicYear) {
                    $query->where('academic_year_id', $academicYear->id);
                })->latest()->get();
            return DataTables::of($data)
                ->editColumn('amount', function ($data) {
                    return 'Rp. ' . number_format($data->amount, 0, ',', '.');
                })
                ->addColumn('classrooms', function ($data) {
                    $classrooms = "";
                    foreach ($data->paymentRateClassrooms as $classroom) {
                        $classrooms .= "<span class='badge bg-success m-1'>{$classroom->classroom->name}</span>";
                    }
                    return $classrooms;
                })
                ->addColumn('school', function ($data) {
                    return $data->paymentRateClassrooms->first()->classroom->school->name;
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('payment-rate.edit', $data->id);
                    $actionShow = route('payment-rate.show', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Jenis Bayar']) .
                        view('components.action.show', ['action' => $actionShow,]) .
                        "</div>";
                })
                ->rawColumns(['action', 'classrooms'])
                ->make(true);
        }
        return view('admins.bill-type.show', compact('billType'));
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
