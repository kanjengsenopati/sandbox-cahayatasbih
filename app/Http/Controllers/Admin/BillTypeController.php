<?php

namespace App\Http\Controllers\Admin;

use App\Models\BillType;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BillTypeRequest;
use App\Models\AcademicYear;
use App\Models\PaymentRate;

class BillTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = BillType::with('billItem', 'academicYear')->latest()->get();
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
                ->addColumn('action', function ($data) {
                    $actionEdit = route('bill-type.edit', $data->id);
                    $actionDelete = route('bill-type.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action', 'type', 'payment_rates'])
                ->make(true);
        }
        return view('admins.bill-type.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.bill-type.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BillTypeRequest $request)
    {
        $validated = $request->validated();
        BillType::create($validated);
        return redirect()->route('bill-type.index')->with('success', 'Data berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(BillType $billType)
    {
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
                    $actionEdit = route('bill-type.edit', $data->id);
                    $actionDelete = route('bill-type.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
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
        return view('admins.bill-type.create-edit', compact('billType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BillTypeRequest $request, BillType $billType)
    {
        $validated = $request->validated();
        $billType->update($validated);
        return redirect()->route('bill-type.index')->with('success', 'Data berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BillType $billType)
    {
        $billType->delete();
        return redirect()->route('bill-type.index')->with('success', 'Data berhasil dihapus');
    }
}
