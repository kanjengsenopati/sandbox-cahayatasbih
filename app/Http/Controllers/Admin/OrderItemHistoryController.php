<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Item;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PointOfSaleTransaction;
use App\Models\PointOfSaleTransactionDetail;

class OrderItemHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!Auth::user()->can('Manage Laporan Pos Kasir')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax() && request()->query('type') === 'santri') {
            $data = PointOfSaleTransaction::with('pointOfSaleTransactionDetails', 'student.classroom', 'admins')->where('type', PointOfSaleTransaction::TYPE_SANTRI)->latest();
            return DataTables::of($data)
                ->editColumn('pay_amount', function ($data) {
                    return 'Rp ' . number_format($data->pay_amount, 0, ',', '.');
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
        if (request()->ajax() && request()->query('type') === 'umum') {
            $data = PointOfSaleTransaction::with('pointOfSaleTransactionDetails', 'admins')->where('type', PointOfSaleTransaction::TYPE_UMUM)->latest();
            return DataTables::of($data)
                ->editColumn('pay_amount', function ($data) {
                    return 'Rp ' . number_format($data->pay_amount, 0, ',', '.');
                })
                ->addColumn('admin', function ($data) {
                    return $data->admins ? $data->admins->name : '-';
                })
                ->editColumn('profit', function ($data) {
                    return 'Rp ' . number_format($data->profit, 0, ',', '.');
                })
                ->addColumn('action', function ($data) {
                    return '<a href="' . route('order-item-history.show', $data->id) . '" class="btn btn-primary btn-sm">Detail</a>';
                })
                ->rawColumns(['status', 'action', 'type'])
                ->make(true);
        }

        if (request()->ajax() && request()->query('type') === 'top-items') {
            // $data = Item::whereIsActive(true)->order(); get item with most transaction
            $data = Item::whereIsActive(true)->withCount('pointOfSaleTransactionDetails')->orderBy('point_of_sale_transaction_details_count', 'desc');
            return DataTables::of($data)
                ->addColumn('total_transaction', function ($data) {
                    return $data->point_of_sale_transaction_details_count ?? 0;
                })
                ->rawColumns(['total_transaction'])
                ->make(true);
        }


        $year = request('year', now()->year);
        $chartIncomesCategories = collect(range(1, 12))->map(function ($month) use ($year) {
            return Carbon::create($year, $month, 1)->locale('id')->monthName;
        })->toArray();

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $totalProduct = Item::whereIsActive(true)->count();

        $transactionQuery = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS);

        if ($startDate && $endDate) {
            $transactionQuery->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        } else {
            $year = now()->year;
            $month = now()->month;
            $transactionQuery->whereYear('created_at', $year)
                ->whereMonth('created_at', $month);
        }

        $totalTransaction = $transactionQuery->count();
        $totalSales = $transactionQuery->sum('pay_amount');
        $totalIncome = $transactionQuery->sum('profit');

        $incomesCashier = [
            'totalProduct' => $totalProduct,
            'totalTransaction' => $totalTransaction,
            'totalSales' => $totalSales,
            'totalIncome' => $totalIncome,
        ];

        $chartCashierOmzet = collect(range(1, 12))->map(function ($month) use ($year) {
            return intval(PointOfSaleTransaction::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                ->sum('pay_amount'));
        })->toArray();

        $chartCashierProfit = collect(range(1, 12))->map(function ($month) use ($year) {
            return intval(PointOfSaleTransaction::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                ->sum('profit'));
        })->toArray();

        return view('admins.order-item.history.index', compact(
            'incomesCashier',
            'chartCashierOmzet',
            'chartCashierProfit',
            'chartIncomesCategories'
        ));
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
        if (!Auth::user()->can('Manage Laporan Pos Kasir')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            $order = PointOfSaleTransactionDetail::with('pointOfSaleTransaction', 'item')->where('point_of_sale_transaction_id', $id)->latest();
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
