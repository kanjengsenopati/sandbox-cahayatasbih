<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Item;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PointOfSaleTransaction;
use App\Models\PointOfSaleTransactionDetail;
use Illuminate\Support\Facades\Cache;

class OrderItemHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // Cache duration in minutes
    const CACHE_DURATION = 15;

    public function index(Request $request)
    {
        // Authorization check
        if (!Auth::user()->can('Manage Laporan Pos Kasir')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        // Handle AJAX requests for DataTables
        if ($request->ajax()) {
            return $this->handleAjaxRequest($request);
        }

        // Get filter parameters
        $period = $request->input('period', 'this_month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $year = $request->input('year', now()->year);

        // Calculate date range based on period
        $dateRange = $this->getDateRange($period, $startDate, $endDate);

        // Get dashboard statistics with caching
        $incomesCashier = $this->getDashboardStatistics($dateRange);

        // Get comparison data (previous period)
        $previousPeriod = $this->getPreviousPeriodStats($dateRange);
        $incomesCashier['comparison'] = $this->calculateComparison($incomesCashier, $previousPeriod);

        // Get chart data
        $chartData = $this->getChartData($year);

        // Get additional insights
        $insights = $this->getBusinessInsights($dateRange);

        // Get top categories for pie chart
        $topCategories = $this->getTopCategories($dateRange);

        return view('admins.order-item.history.index', [
            // Statistics
            'totalProduct' => $incomesCashier['totalProduct'],
            'totalTransaction' => $incomesCashier['totalTransaction'],
            'totalSales' => $incomesCashier['totalSales'],
            'totalIncome' => $incomesCashier['totalIncome'],
            'avgTransaction' => $incomesCashier['avgTransaction'],
            'profitMargin' => $incomesCashier['profitMargin'],

            // Comparison data
            'comparison' => $incomesCashier['comparison'],

            // Chart data
            'chartIncomesCategories' => $chartData['chartIncomesCategories'],
            'chartCashierOmzet' => $chartData['chartCashierOmzet'],
            'chartCashierProfit' => $chartData['chartCashierProfit'],

            // Top Categories
            'topCategories' => $topCategories,

            // Insights
            'insights' => $insights['insights'],
        ]);
    }

    private function getTopCategories(array $dateRange)
    {
        $cacheKey = 'top_categories_' . $dateRange['start']->format('Y-m-d') . '_' . $dateRange['end']->format('Y-m-d');

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($dateRange) {
            return DB::table('point_of_sale_transaction_details as details')
                ->join('items', 'details.item_id', '=', 'items.id')
                ->join('category_items', 'items.category_item_id', '=', 'category_items.id')
                ->join('point_of_sale_transactions as transactions', 'details.point_of_sale_transaction_id', '=', 'transactions.id')
                ->select(
                    'category_items.name',
                    DB::raw('SUM(details.quantity) as total_sales'),
                    DB::raw('SUM(details.price) as total_revenue')
                )
                ->where('transactions.status', PointOfSaleTransaction::STATUS_SUCCESS)
                ->whereBetween('transactions.created_at', [$dateRange['start'], $dateRange['end']])
                ->groupBy('category_items.id', 'category_items.name')
                ->orderByDesc('total_revenue')
                ->limit(5)
                ->get();
        });
    }

    /**
     * Handle AJAX requests for DataTables
     */
    private function handleAjaxRequest(Request $request)
    {
        $type = $request->query('type');

        switch ($type) {
            case 'santri':
                return $this->getSantriTransactions($request);
            case 'umum':
                return $this->getUmumTransactions($request);
            case 'top-items':
                return $this->getTopItems($request);
            default:
                return response()->json(['error' => 'Invalid type'], 400);
        }
    }
    private function getSantriTransactions(Request $request)
    {
        $query = PointOfSaleTransaction::query()
            ->with([
                'student:id,name,classroom_id',
                'student.classroom:id,name',
                'admins:id,name'
            ])
            ->where('type', PointOfSaleTransaction::TYPE_SANTRI)
            ->select([
                'id',
                'payment_code',
                'pay_amount',
                'student_id',
                'admin_id',
                'created_at'
            ])
            ->latest();

        // Apply date filter if provided
        $this->applyDateFilter($query, $request);

        return DataTables::of($query)
            ->addColumn('date', function ($data) {
                return Carbon::parse($data->created_at)
                    ->locale('id')
                    ->translatedFormat('d F Y H:i');
            })
            ->editColumn('pay_amount', function ($data) {
                return 'Rp ' . number_format($data->pay_amount, 0, ',', '.');
            })
            ->addColumn('admin', function ($data) {
                return $data->admins?->name ?? '-';
            })
            ->addColumn('action', function ($data) {
                return '<a href="' . route('order-item-history.show', $data->id) . '" class="btn btn-primary btn-sm">Detail</a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Get Umum transactions for DataTable
     */
    private function getUmumTransactions(Request $request)
    {
        $query = PointOfSaleTransaction::query()
            ->with('admins:id,name')
            ->where('type', PointOfSaleTransaction::TYPE_UMUM)
            ->select([
                'id',
                'payment_code',
                'pay_amount',
                'profit',
                'admin_id',
                'created_at'
            ])
            ->latest();

        // Apply date filter if provided
        $this->applyDateFilter($query, $request);

        return DataTables::of($query)
            ->addColumn('date', function ($data) {
                return Carbon::parse($data->created_at)
                    ->locale('id')
                    ->translatedFormat('d F Y H:i');
            })
            ->editColumn('pay_amount', function ($data) {
                return 'Rp ' . number_format($data->pay_amount, 0, ',', '.');
            })
            ->editColumn('profit', function ($data) {
                return 'Rp ' . number_format($data->profit, 0, ',', '.');
            })
            ->addColumn('admin', function ($data) {
                return $data->admins?->name ?? '-';
            })
            ->addColumn('action', function ($data) {
                return '<a href="' . route('order-item-history.show', $data->id) . '" class="btn btn-primary btn-sm">Detail</a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Get top selling items for DataTable
     */
    private function getTopItems(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Build subquery for transaction counts with optimized indexing
        $transactionSubquery = DB::table('point_of_sale_transaction_details as d')
            ->select([
                'd.item_id',
                DB::raw('COUNT(DISTINCT d.point_of_sale_transaction_id) as txn_count'),
                DB::raw('SUM(d.quantity) as total_quantity'),
                DB::raw('SUM(d.quantity * d.price) as total_revenue')
            ])
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('d.created_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay(),
                ]);
            })
            ->groupBy('d.item_id');

        $query = Item::query()
            ->where('is_active', true)
            ->joinSub($transactionSubquery, 't', 't.item_id', '=', 'items.id')
            ->select([
                'items.id',
                'items.name',
                DB::raw('CAST(t.txn_count as UNSIGNED) as total_transaction'),
                DB::raw('CAST(t.total_quantity as UNSIGNED) as total_quantity'),
                DB::raw('CAST(t.total_revenue as UNSIGNED) as revenue')
            ])
            ->orderByDesc('total_transaction')
            ->limit(50); // Limit to top 50 items

        return DataTables::of($query)
            ->editColumn('revenue', function ($row) {
                return 'Rp ' . number_format($row->revenue, 0, ',', '.');
            })
            ->make(true);
    }

    /**
     * Get date range based on period selection
     */
    private function getDateRange($period, $customStart = null, $customEnd = null)
    {
        $now = now();

        switch ($period) {
            case 'today':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay()
                ];
            case 'week':
                return [
                    'start' => $now->copy()->subDays(7)->startOfDay(),
                    'end' => $now->copy()->endOfDay()
                ];
            case 'last_month':
                return [
                    'start' => $now->copy()->subMonth()->startOfMonth(),
                    'end' => $now->copy()->subMonth()->endOfMonth()
                ];
            case 'last_3_months':
                return [
                    'start' => $now->copy()->subMonths(2)->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
            case 'last_6_months':
                return [
                    'start' => $now->copy()->subMonths(5)->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
            case 'this_year':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear()
                ];
            case 'this_month':
            case 'month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
            case 'custom':
                if ($customStart && $customEnd) {
                    return [
                        'start' => Carbon::parse($customStart)->startOfDay(),
                        'end' => Carbon::parse($customEnd)->endOfDay()
                    ];
                }
                // Fallback to current month if custom dates not provided
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
            default:
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
        }
    }

    /**
     * Get dashboard statistics with caching
     */
    private function getDashboardStatistics(array $dateRange)
    {
        $cacheKey = 'dashboard_stats_' . $dateRange['start']->format('Y-m-d') . '_' . $dateRange['end']->format('Y-m-d');

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($dateRange) {
            // Use single query with aggregations for better performance
            $stats = PointOfSaleTransaction::query()
                ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->selectRaw('
                    COUNT(*) as totalTransaction,
                    COALESCE(SUM(pay_amount), 0) as totalSales,
                    COALESCE(SUM(profit), 0) as totalIncome,
                    COALESCE(AVG(pay_amount), 0) as avgTransaction
                ')
                ->first();

            $totalProduct = Cache::remember('total_active_products', 60, function () {
                return Item::where('is_active', true)->count();
            });

            return [
                'totalProduct' => $totalProduct,
                'totalTransaction' => (int) $stats->totalTransaction,
                'totalSales' => (float) $stats->totalSales,
                'totalIncome' => (float) $stats->totalIncome,
                'avgTransaction' => (float) $stats->avgTransaction,
                'profitMargin' => $stats->totalSales > 0
                    ? round(($stats->totalIncome / $stats->totalSales) * 100, 2)
                    : 0
            ];
        });
    }

    /**
     * Get previous period statistics for comparison
     */
    private function getPreviousPeriodStats(array $dateRange)
    {
        $diff = $dateRange['start']->diffInDays($dateRange['end']);

        $previousStart = $dateRange['start']->copy()->subDays($diff + 1);
        $previousEnd = $dateRange['start']->copy()->subDay();

        $cacheKey = 'previous_stats_' . $previousStart->format('Y-m-d') . '_' . $previousEnd->format('Y-m-d');

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($previousStart, $previousEnd) {
            return PointOfSaleTransaction::query()
                ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                ->whereBetween('created_at', [$previousStart, $previousEnd])
                ->selectRaw('
                    COUNT(*) as totalTransaction,
                    COALESCE(SUM(pay_amount), 0) as totalSales,
                    COALESCE(SUM(profit), 0) as totalIncome
                ')
                ->first();
        });
    }

    /**
     * Calculate comparison percentages
     */
    private function calculateComparison($current, $previous)
    {
        return [
            'transaction' => $this->calculatePercentageChange(
                $current['totalTransaction'],
                $previous->totalTransaction
            ),
            'sales' => $this->calculatePercentageChange(
                $current['totalSales'],
                $previous->totalSales
            ),
            'income' => $this->calculatePercentageChange(
                $current['totalIncome'],
                $previous->totalIncome
            ),
        ];
    }

    /**
     * Calculate percentage change between two values
     */
    private function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Get chart data for visualization
     */
    private function getChartData($year)
    {
        $cacheKey = "chart_data_{$year}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($year) {
            // Get monthly data in single query
            $monthlyData = PointOfSaleTransaction::query()
                ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                ->whereYear('created_at', $year)
                ->selectRaw('
                    MONTH(created_at) as month,
                    COALESCE(SUM(pay_amount), 0) as omzet,
                    COALESCE(SUM(profit), 0) as profit
                ')
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->keyBy('month');

            // Fill missing months with zeros
            $chartCashierOmzet = [];
            $chartCashierProfit = [];
            $chartIncomesCategories = [];

            for ($month = 1; $month <= 12; $month++) {
                $chartIncomesCategories[] = Carbon::create($year, $month, 1)
                    ->locale('id')
                    ->monthName;

                $data = $monthlyData->get($month);
                $chartCashierOmzet[] = $data ? (int) $data->omzet : 0;
                $chartCashierProfit[] = $data ? (int) $data->profit : 0;
            }

            return [
                'chartIncomesCategories' => $chartIncomesCategories,
                'chartCashierOmzet' => $chartCashierOmzet,
                'chartCashierProfit' => $chartCashierProfit,
            ];
        });
    }

    /**
     * Get business insights for dashboard
     */
    private function getBusinessInsights(array $dateRange)
    {
        $cacheKey = 'insights_' . $dateRange['start']->format('Y-m-d') . '_' . $dateRange['end']->format('Y-m-d');

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($dateRange) {
            // Best performing cashier
            $bestCashier = PointOfSaleTransaction::query()
                ->select('admin_id', DB::raw('COUNT(*) as total_transactions'))
                ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->whereNotNull('admin_id')
                ->groupBy('admin_id')
                ->orderByDesc('total_transactions')
                ->with('admins:id,name')
                ->first();

            // Peak hour analysis
            $peakHour = PointOfSaleTransaction::query()
                ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
                ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->groupBy('hour')
                ->orderByDesc('count')
                ->first();

            // Top selling product
            $topProduct = DB::table('point_of_sale_transaction_details as d')
                ->join('items', 'items.id', '=', 'd.item_id')
                ->join('point_of_sale_transactions as t', 't.id', '=', 'd.point_of_sale_transaction_id')
                ->whereBetween('d.created_at', [$dateRange['start'], $dateRange['end']])
                ->where('t.status', PointOfSaleTransaction::STATUS_SUCCESS)
                ->select('items.name', DB::raw('SUM(d.quantity) as total_sold'))
                ->groupBy('items.id', 'items.name')
                ->orderByDesc('total_sold')
                ->first();

            // Active customers (santri)
            $activeCustomers = PointOfSaleTransaction::query()
                ->where('type', PointOfSaleTransaction::TYPE_SANTRI)
                ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->distinct('student_id')
                ->count('student_id');

            // Repeat customer rate
            $repeatRate = $this->calculateRepeatCustomerRate($dateRange);

            // Low stock items
            $lowStockItems = Item::where('is_active', true)
                ->where('stock', '<=', 10)
                ->count();

            return [
                'insights' => [
                    'bestCashier' => [
                        'name' => $bestCashier?->admins?->name ?? '-',
                        'transactions' => $bestCashier?->total_transactions ?? 0
                    ],
                    'topProduct' => [
                        'name' => $topProduct?->name ?? '-',
                        'sold' => $topProduct?->total_sold ?? 0
                    ],
                    'peakHour' => $peakHour ? sprintf('%02d:00 - %02d:00', $peakHour->hour, $peakHour->hour + 1) : '-',
                    'activeCustomers' => $activeCustomers,
                    'repeatRate' => $repeatRate,
                    'lowStockItems' => $lowStockItems
                ]
            ];
        });
    }

    /**
     * Calculate repeat customer rate
     */
    private function calculateRepeatCustomerRate(array $dateRange)
    {
        $customerTransactions = PointOfSaleTransaction::query()
            ->where('type', PointOfSaleTransaction::TYPE_SANTRI)
            ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('student_id, COUNT(*) as transaction_count')
            ->groupBy('student_id')
            ->get();

        if ($customerTransactions->isEmpty()) {
            return 0;
        }

        $repeatCustomers = $customerTransactions->where('transaction_count', '>', 1)->count();
        $totalCustomers = $customerTransactions->count();

        return round(($repeatCustomers / $totalCustomers) * 100, 1);
    }

    /**
     * Apply date filter to query
     */
    private function applyDateFilter($query, Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        return $query;
    }

    /**
     * Clear dashboard cache (call this after data changes)
     */
    public function clearCache()
    {
        Cache::forget('total_active_products');
        // You can add pattern-based cache clearing if needed
        // Cache::tags(['dashboard'])->flush();

        return response()->json(['message' => 'Cache cleared successfully']);
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
                    return number_format($data->price, 0, ',', '.');
                })
                ->editColumn('total_price', function ($data) {
                    return number_format($data->total, 0, ',', '.');
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
