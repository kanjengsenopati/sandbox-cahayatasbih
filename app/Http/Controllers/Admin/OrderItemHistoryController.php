<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Models\PointOfSaleTransaction;

class OrderItemHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = PointOfSaleTransaction::with('pointOfSaleTransactionDetails', 'student')->latest()->get();
            return DataTables::of($data)
                ->editColumn('pay_amount', function ($data) {
                    return 'Rp ' . number_format($data->pay_amount, 0, ',', '.');
                })
                ->editColumn('status', function ($data) {
                    if ($data->status === PointOfSaleTransaction::STATUS_SUCCESS) {
                        return '<span class="badge bg-success">' . $data->status . '</span>';
                    } elseif ($data->status === PointOfSaleTransaction::STATUS_PENDING) {
                        return '<span class="badge bg-warning">' . $data->status . '</span>';
                    } else {
                        return '<span class="badge bg-danger">' . $data->status . '</span>';
                    }
                })
                ->editColumn('paid_at', function ($data) {
                    $data->paid_at ? $data->paid_at->format('d F Y') : '-';
                })
                ->editColumn('admin_id', function ($data) {
                    return $data->cashier ? $data->cashier->name : '-';
                })
                ->rawColumns(['status'])
                ->make(true);
        }
        return view('admins.order-item.history.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
