<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Outlet;
use App\Models\CashFlow;
use App\Models\PointOfSaleTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ProfitLossReportController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->can('Manage Arus Kas') && !Auth::user()->can('Manage Laporan Pos Multi Outlet') && !Auth::user()->can('Manage Laporan Rugi Laba')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        // Tentukan outlet_ids berdasarkan hak akses admin yang login
        $authOutletIds = auth()->user()->getOutletIds();
        $hasOutletRestriction = count($authOutletIds) > 0;
        $outletId = $request->input('outlet_id');

        if ($hasOutletRestriction) {
            $queryOutletId = $outletId && in_array($outletId, $authOutletIds) ? $outletId : $authOutletIds;
        } else {
            $queryOutletId = $outletId;
        }

        // Tentukan rentang tanggal filter (default: awal bulan ini s.d hari ini)
        $startDateInput = $request->input('start_date');
        $endDateInput = $request->input('end_date');

        if ($startDateInput && $endDateInput) {
            $startDate = Carbon::parse($startDateInput)->format('Y-m-d');
            $endDate = Carbon::parse($endDateInput)->format('Y-m-d');
        } else {
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        // 1. Hitung Penjualan POS & Profit POS (Realisasi)
        $posQuery = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->when($queryOutletId, function ($q) use ($queryOutletId) {
                if (is_array($queryOutletId)) {
                    $q->whereIn('outlet_id', $queryOutletId);
                } else {
                    $q->where('outlet_id', $queryOutletId);
                }
            });

        $posSalesTotal = (clone $posQuery)->sum('pay_amount');
        $posProfitTotal = (clone $posQuery)->sum('profit');
        $posCostTotal = max($posSalesTotal - $posProfitTotal, 0);

        // 2. Hitung Pemasukan Kas Operasional (APPROVED, excluding internal handovers)
        $excludeCategoryNames = [
            'Serah Terima Piket ke Bendahara',
            'Serah Terima Bendahara ke Yayasan'
        ];

        $cashIncomesQuery = CashFlow::where('type', CashFlow::TYPE_INCOME)
            ->where('status', CashFlow::STATUS_APPROVED)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereHas('cashflow_category', function($q) use ($excludeCategoryNames) {
                $q->whereNotIn('name', $excludeCategoryNames);
            })
            ->when($queryOutletId, function ($q) use ($queryOutletId) {
                if (is_array($queryOutletId)) {
                    $q->whereIn('outlet_id', $queryOutletId);
                } else {
                    $q->where('outlet_id', $queryOutletId);
                }
            });

        $cashIncomesTotal = $cashIncomesQuery->sum('amount');
        $cashIncomesBreakdown = $cashIncomesQuery->select('cash_flow_category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('cash_flow_category_id')
            ->with('cashflow_category')
            ->get();

        // 3. Hitung Pengeluaran Kas Operasional (APPROVED)
        $cashExpensesQuery = CashFlow::where('type', CashFlow::TYPE_EXPENSE)
            ->where('status', CashFlow::STATUS_APPROVED)
            ->whereBetween('date', [$startDate, $endDate])
            ->when($queryOutletId, function ($q) use ($queryOutletId) {
                if (is_array($queryOutletId)) {
                    $q->whereIn('outlet_id', $queryOutletId);
                } else {
                    $q->where('outlet_id', $queryOutletId);
                }
            });

        $cashExpensesTotal = $cashExpensesQuery->sum('amount');
        $cashExpensesBreakdown = $cashExpensesQuery->select('cash_flow_category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('cash_flow_category_id')
            ->with('cashflow_category')
            ->get();

        // 4. Perhitungan Rugi Laba
        $totalRevenues = $posSalesTotal + $cashIncomesTotal;
        $totalHpp = $posCostTotal;
        $grossProfit = $totalRevenues - $totalHpp;
        $totalExpenses = $cashExpensesTotal;
        $netProfit = $grossProfit - $totalExpenses;

        // Fetch outlets for dropdown filter
        if ($hasOutletRestriction) {
            $outlets = Outlet::whereIn('id', $authOutletIds)->orderBy('name')->get();
        } else {
            $outlets = Outlet::orderBy('name')->get();
        }

        // Current selected outlet details
        $selectedOutlet = null;
        if ($outletId && !$hasOutletRestriction) {
            $selectedOutlet = Outlet::find($outletId);
        } elseif ($outletId && $hasOutletRestriction && in_array($outletId, $authOutletIds)) {
            $selectedOutlet = Outlet::find($outletId);
        }

        return view('admins.report-profit-loss.index', compact(
            'outlets',
            'selectedOutlet',
            'startDate',
            'endDate',
            'posSalesTotal',
            'posProfitTotal',
            'posCostTotal',
            'cashIncomesTotal',
            'cashIncomesBreakdown',
            'cashExpensesTotal',
            'cashExpensesBreakdown',
            'totalRevenues',
            'totalHpp',
            'grossProfit',
            'totalExpenses',
            'netProfit',
            'hasOutletRestriction'
        ));
    }
}
