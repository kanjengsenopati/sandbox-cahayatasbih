<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Item;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Models\PointOfSaleTransaction;

class ReportPosController extends Controller
{
    public function index(Request $request)
    {
        // Define the start and end date filters
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Handle AJAX request for top-items
        if (request()->ajax() && request()->query('type') === 'top-items') {
            // Get the start and end dates from the request
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // Build the query for the items, including filtering the transactions by date
            $data = Item::whereIsActive(true)
                ->withCount(['pointOfSaleTransactionDetails' => function ($query) use ($startDate, $endDate) {
                    // Apply date range filter to the related transactions
                    $this->applyTransactionDateFilter(
                        $query,
                        $startDate,
                        $endDate
                    );
                }])
                ->orderByDesc('point_of_sale_transaction_details_count')
                ->take(10); // Limit to top 10 items

            return DataTables::of($data)
                ->addColumn('total_transaction', fn($data) => $data->point_of_sale_transaction_details_count ?? 0)
                ->rawColumns(['total_transaction'])
                ->make(true);
        }

        // Get the year for chart generation
        $year = request('year', now()->year);

        // Generate month names for the chart
        $chartIncomesCategories = collect(range(1, 12))->map(fn($month) => Carbon::create($year, $month, 1)->locale('id')->monthName)->toArray();

        // Calculate the total number of active products
        $totalProduct = Item::whereIsActive(true)->count();

        // Build the transaction query with the date filter
        $transactionQuery = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS);
        $this->applyTransactionDateFilter($transactionQuery, $startDate, $endDate);

        // Calculate total transactions, sales, and income
        $totalTransaction = $transactionQuery->count();
        $totalSales = $transactionQuery->sum('pay_amount');
        $totalIncome = $transactionQuery->sum('profit');

        $incomesCashier = compact('totalProduct', 'totalTransaction', 'totalSales', 'totalIncome');

        // Generate chart data for monthly omzet and profit
        $chartCashierOmzet = $this->generateMonthlyChartData($year, 'pay_amount');
        $chartCashierProfit = $this->generateMonthlyChartData($year, 'profit');

        // Return the view with the necessary data
        return view('admins.report-pos.index', compact(
            'incomesCashier',
            'chartCashierOmzet',
            'chartCashierProfit',
            'chartIncomesCategories'
        ));
    }

    /**
     * Apply date filter to the transaction query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $startDate
     * @param  string|null  $endDate
     * @return void
     */
    private function applyTransactionDateFilter($query, $startDate, $endDate)
    {
        if ($startDate && $endDate) {
            $query->whereHas('pointOfSaleTransaction', function ($transactionQuery) use ($startDate, $endDate) {
                $transactionQuery->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            });
        }
    }

    /**
     * Generate monthly chart data (omzet or profit).
     *
     * @param  int  $year
     * @param  string  $column
     * @return array
     */
    private function generateMonthlyChartData($year, $column)
    {
        return collect(range(1, 12))->map(function ($month) use ($year, $column) {
            return intval(PointOfSaleTransaction::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                ->sum($column));
        })->toArray();
    }
}
