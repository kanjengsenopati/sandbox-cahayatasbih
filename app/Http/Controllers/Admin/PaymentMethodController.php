<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\PaymentMethodRequest;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Metode Pembayaran')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
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
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Metode Pembayaran']) . '&nbsp;' .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Metode Pembayaran']) .
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
        if (!Auth::user()->can('Create Metode Pembayaran')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $paymentMethodType = (new PaymentMethod)->getTypeList();
        return view('admins.payment-method.create-edit', compact('paymentMethodType'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentMethodRequest $request)
    {
        if (!Auth::user()->can('Create Metode Pembayaran')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
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
        if (!Auth::user()->can('Edit Metode Pembayaran')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $paymentMethodType = (new PaymentMethod)->getTypeList();
        return view('admins.payment-method.create-edit', compact('paymentMethod', 'paymentMethodType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaymentMethodRequest $request, PaymentMethod $paymentMethod)
    {
        if (!Auth::user()->can('Edit Metode Pembayaran')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $validated = $request->validated();
        $paymentMethod->update($validated);
        return redirect()->route('payment-method.index')->with('success', 'Metode pembayaran berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        if (!Auth::user()->can('Delete Metode Pembayaran')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $paymentMethod->delete();
        return redirect()->route('payment-method.index')->with('success', 'Metode pembayaran berhasil dihapus');
    }
}
