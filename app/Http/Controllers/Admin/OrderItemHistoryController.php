<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Models\PointOfSaleTransaction;
use App\Models\PointOfSaleTransactionDetail;

class OrderItemHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = PointOfSaleTransaction::with('pointOfSaleTransactionDetails', 'student', 'admins')->latest()->get();
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
                ->editColumn('type', function ($data) {
                    if ($data->type === PointOfSaleTransaction::TYPE_SANTRI) {
                        return '<span class="badge bg-primary">' . $data->type . '</span>';
                    } elseif ($data->type === PointOfSaleTransaction::TYPE_UMUM) {
                        return '<span class="badge bg-info">' . $data->type . '</span>';
                    } else {
                        return '<span class="badge bg-warning">' . $data->type . '</span>';
                    }
                })
                ->editColumn('paid_at', function ($data) {
                    $data->paid_at ? $data->paid_at->format('d F Y') : '-';
                })
                ->addColumn('admin', function ($data) {
                    return $data->admins ? $data->admins->name : '-';
                })
                ->addColumn('action', function ($data) {
                    return '<a href="' . route('order-item-history.show', $data->id) . '" class="btn btn-primary btn-sm">Detail</a>';
                })
                ->rawColumns(['status', 'action', 'type'])
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
        if (request()->ajax()) {
            $order = PointOfSaleTransactionDetail::with('pointOfSaleTransaction', 'item')->where('point_of_sale_transaction_id', $id)->get();
            return DataTables::of($order)
                ->editColumn('price', function ($data) {
                    return 'Rp ' . number_format($data->price, 0, ',', '.');
                })
                ->editColumn('total_price', function ($data) {
                    return 'Rp ' . number_format($data->total_price, 0, ',', '.');
                })
                ->make(true);
        }
        $order = PointOfSaleTransaction::with('pointOfSaleTransactionDetails', 'student', 'admins')->findOrFail($id);
        return view('admins.order-item.history.show', compact('order'));
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
