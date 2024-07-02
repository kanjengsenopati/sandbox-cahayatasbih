<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\School;
use GuzzleHttp\Client;
use App\Models\Article;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\HistoryDownload;
use App\Models\PpdbRegistration;
use Yajra\DataTables\DataTables;
use App\Models\ApplicationSetting;
use App\Models\WhiteBlowingSystem;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\SendNotifWaService;
use App\Services\NotificationService;
use App\Models\PointOfSaleTransaction;
use App\Jobs\SendToWhatsappNotificationJob;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $setting = ApplicationSetting::first();
        $targetMonth = $setting->target_month;
        $targetYear = $setting->target_year;
        $total_parents = User::where('is_active', 1)->count();
        $total_students = Student::count();
        $data = [
            'total_parents' => $total_parents,
            'total_students' => $total_students,
        ];
        // hitung total pemasukkan hari ini, bulan ini, tahun ini
        $transactions = Transaction::where('status', Transaction::STATUS_PAID)
            ->whereIn('type', [Transaction::TYPE_BILL, Transaction::TYPE_PPDB])->get();

        $today = $transactions->where('created_at', '>=', now()->startOfDay())
            ->where('created_at', '<=', now()->endOfDay());
        $total_income_today = $today->sum('pay_amount');

        $month = $transactions->where('created_at', '>=', now()->startOfMonth())
            ->where('created_at', '<=', now()->endOfMonth());
        $total_income_month = $month->sum('pay_amount');
        // count total percentage to targetMonth
        $incomePercentageMonth = ($total_income_month / $targetMonth) * 100;

        $year = $transactions->where('created_at', '>=', now()->startOfYear())
            ->where('created_at', '<=', now()->endOfYear());
        $total_income_year = $year->sum('pay_amount');

        // count total percentage to targetYear
        $incomePercentageYear = ($total_income_year / $targetYear) * 100;

        $total = $transactions->sum('pay_amount');

        $incomes = [
            'today' => $total_income_today,
            'month' => $total_income_month,
            'incomePercentageMonth' => $incomePercentageMonth,
            'year' => $total_income_year,
            'incomePercentageYear' => $incomePercentageYear,
            'total' => $total,
            'targetMonth' => $targetMonth,
            'targetYear' => $targetYear,
        ];

        $year = request('year', now()->year);
        $chartIncomesCategories = collect(range(1, 12))->map(function ($month) use ($year) {
            return Carbon::create($year, $month, 1)->locale('id')->monthName;
        })->toArray();

        $chartIncomesSmp = collect(range(1, 12))->map(function ($month) use ($year) {
            return intval(Transaction::whereHas('student.classroom.school', function ($query) {
                $query->where('type', School::TYPE_SMP);
            })
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('status', Transaction::STATUS_PAID)
                ->whereIn('type', [Transaction::TYPE_BILL, Transaction::TYPE_PPDB])
                ->sum('pay_amount'));
        })->toArray();

        $chartIncomesMa = collect(range(1, 12))->map(function ($month) use ($year) {
            return intval(Transaction::whereHas('student.classroom.school', function ($query) {
                $query->where('type', School::TYPE_MA);
            })
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('status', Transaction::STATUS_PAID)
                ->whereIn('type', [Transaction::TYPE_BILL, Transaction::TYPE_PPDB])
                ->sum('pay_amount'));
        })->toArray();

        $chartIncomesPondok = collect(range(1, 12))->map(function ($month) use ($year) {
            return intval(Transaction::whereHas('student.classroom.school', function ($query) {
                $query->where('type', School::TYPE_PONDOK);
            })
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('status', Transaction::STATUS_PAID)
                ->whereIn('type', [Transaction::TYPE_BILL, Transaction::TYPE_PPDB])
                ->sum('pay_amount'));
        })->toArray();

        $cashierToday = $incomeCashier = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
            ->where('created_at', '>=', now()->startOfDay())
            ->where('created_at', '<=', now()->endOfDay());
        $total_income_cashier_today = $cashierToday->sum('pay_amount');

        $cashierMonth = $incomeCashier = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
            ->where('created_at', '>=', now()->startOfMonth())
            ->where('created_at', '<=', now()->endOfMonth());
        $total_income_cashier_month = $cashierMonth->sum('pay_amount');

        $cashierYear = $incomeCashier = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
            ->where('created_at', '>=', now()->startOfYear())
            ->where('created_at', '<=', now()->endOfYear());
        $total_income_cashier_year = $cashierYear->sum('pay_amount');

        $total_cashier = $incomeCashier = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
            ->sum('pay_amount');

        $incomesCashier = [
            'today' => $total_income_cashier_today,
            'month' => $total_income_cashier_month,
            'year' => $total_income_cashier_year,
            'total' => $total_cashier,
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


        return view('admins.dashboard.index', compact(
            'data',
            'incomes',
            'chartIncomesCategories',
            'chartIncomesSmp',
            'chartIncomesMa',
            'chartIncomesPondok',
            'incomesCashier',
            'chartCashierOmzet',
            'chartCashierProfit'
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
