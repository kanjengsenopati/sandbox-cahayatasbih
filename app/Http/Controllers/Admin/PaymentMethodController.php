<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentMethodRequest;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = PaymentMethod::latest()->get();
            return DataTables::of($data)
                ->editColumn('status', function ($data) {
                    return $data->is_active == PaymentMethod::STATUS_ACTIVE ?
                        '<span class="badge badge-primary">Aktif</span>' :
                        '<span class="badge badge-secondary">Tidak Aktif</span>';
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('payment-method.edit', $data->id);
                    $actionDelete = route('payment-method.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        return view('admins.payment-method.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.payment-method.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentMethodRequest $request)
    {
        $validated = $request->validated();
        PaymentMethod::create($validated);
        return redirect()->route('payment-method.index')->with('success', 'Metode pembayaran berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentMethod $paymentMethod)
    {
        return view('admins.payment-method.create-edit', compact('paymentMethod'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaymentMethodRequest $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validated();
        $paymentMethod->update($validated);
        return redirect()->route('payment-method.index')->with('success', 'Metode pembayaran berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();
        return redirect()->route('payment-method.index')->with('success', 'Metode pembayaran berhasil dihapus');
    }
}
