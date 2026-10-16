<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Admin;
use App\Models\Bill;
use App\Models\Classroom;
use App\Models\PointOfSaleTransaction;
use App\Models\SaldoHistory;
use App\Models\SavingHistory;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\CanonicalBillTypeTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    use CanonicalBillTypeTrait;

    /**
     * Excluded non-academic school IDs.
     */
    private const EXCLUDED_SCHOOL_IDS = [
        '37ca75d4-4a87-4856-be8e-f78e2672134f',
        'ca3d1ef1-a2ec-4a2b-81ce-72a2299e068c',
    ];

    /**
     * Display Business Intelligence Main Dashboard or Outlet Dashboard.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            if ($user && $user->isKasirOnly()) {
                if ($user->isKasirKoperasi()) {
                    $effectiveOutletId = $user->getEffectiveOutletId('kantin');
                    return redirect('/order-item?mode=kantin' . ($effectiveOutletId ? '&outlet_id=' . $effectiveOutletId : ''));
                }
                if ($user->isKasirOutlet() || $user->isKasir()) {
                    $effectiveOutletId = $user->getEffectiveOutletId('outlet');
                    return redirect('/order-item?mode=outlet' . ($effectiveOutletId ? '&outlet_id=' . $effectiveOutletId : ''));
                }
            }

            $isOutletUser = ($user && $user->isKasirOnly())
                || ($user && $user->hasRole('Karyawan Outlet ( Non Kasir )'))
                || $request->input('mode') === 'outlet';

            if ($isOutletUser) {
                $outletIds = $user->getOutletIds();

                $salesToday = PointOfSaleTransaction::whereIn('outlet_id', $outletIds)
                    ->where('status', 'SUCCESS')
                    ->whereDate('created_at', Carbon::today())
                    ->sum('pay_amount');

                $transactionsToday = PointOfSaleTransaction::whereIn('outlet_id', $outletIds)
                    ->where('status', 'SUCCESS')
                    ->whereDate('created_at', Carbon::today())
                    ->count();

                $totalBarang = \App\Models\Item::whereIn('outlet_id', $outletIds)->count();
                $totalKaryawan = \App\Models\Karyawan::whereIn('outlet_id', $outletIds)->count();

                $recentTransactions = PointOfSaleTransaction::with(['student', 'admins'])
                    ->whereIn('outlet_id', $outletIds)
                    ->where('status', 'SUCCESS')
                    ->latest()
                    ->limit(5)
                    ->get();

                $salesChart = [
                    'labels' => [],
                    'data' => [],
                ];
                $startDate = Carbon::today()->subDays(6)->startOfDay();
                $salesData = PointOfSaleTransaction::whereIn('outlet_id', $outletIds)
                    ->where('status', 'SUCCESS')
                    ->where('created_at', '>=', $startDate)
                    ->selectRaw('DATE(created_at) as date_only, SUM(pay_amount) as total_amount')
                    ->groupBy('date_only')
                    ->pluck('total_amount', 'date_only')
                    ->toArray();

                for ($i = 6; $i >= 0; $i--) {
                    $date = Carbon::today()->subDays($i);
                    $dateStr = $date->toDateString();
                    $amount = $salesData[$dateStr] ?? 0;
                    $salesChart['labels'][] = $date->translatedFormat('d M');
                    $salesChart['data'][] = (int) $amount;
                }

                return view('admins.dashboard.index', compact(
                    'isOutletUser',
                    'salesToday',
                    'transactionsToday',
                    'totalBarang',
                    'totalKaryawan',
                    'recentTransactions',
                    'salesChart'
                ));
            }

            $canViewBiDashboard = $user && method_exists($user, 'canAccessBiDashboard') && $user->canAccessBiDashboard();

            if (!$canViewBiDashboard) {
                $activeAcademicYear = AcademicYear::orderByDesc('is_active')
                    ->orderByDesc('start_year')
                    ->orderByDesc('name')
                    ->first(['id', 'name', 'is_active']);

                $activeAcademicYearName = $activeAcademicYear?->name ?? '-';
                $upcomingSchedules = Schedule::with('school:id,name')
                    ->whereDate('start_date', '>=', Carbon::today())
                    ->orderBy('start_date', 'asc')
                    ->limit(6)
                    ->get();

                return view('admins.dashboard.index', [
                    'isOutletUser' => false,
                    'canViewBiDashboard' => false,
                    'activeAcademicYearName' => $activeAcademicYearName,
                    'upcomingSchedules' => $upcomingSchedules,
                ]);
            }

            // =====================================================================
            // BUSINESS INTELLIGENCE (BI) MAIN DASHBOARD
            // =====================================================================

            // 1. Resolve Scoped Schools & Filter Inputs
            $allAllowedSchoolIds = array_values(array_diff(
                $this->resolveScopedSchoolIds(null),
                self::EXCLUDED_SCHOOL_IDS
            ));

            $requestedSchoolId = ($request->filled('school_id') && $request->input('school_id') !== 'all' && $request->input('school_id') !== 'null')
                ? trim((string) $request->input('school_id'))
                : null;

            $scopedSchoolIds = $requestedSchoolId
                ? (in_array($requestedSchoolId, $allAllowedSchoolIds, true) ? [$requestedSchoolId] : [])
                : $allAllowedSchoolIds;

            $selectedSchoolId = $requestedSchoolId && in_array($requestedSchoolId, $allAllowedSchoolIds, true)
                ? $requestedSchoolId
                : null;

            $schools = School::whereIn('id', $allAllowedSchoolIds)
                ->orderBy('name', 'asc')
                ->get(['id', 'name', 'type']);

            $academicYears = AcademicYear::orderByDesc('is_active')
                ->orderByDesc('start_year')
                ->orderByDesc('name')
                ->get(['id', 'name', 'start_year', 'end_year', 'is_active']);

            $activeAcademicYear = $academicYears->firstWhere('is_active', true) ?? $academicYears->first();

            $academicYearInput = $request->input('academic_year_id');
            if ($academicYearInput === 'all') {
                $selectedAcademicYearId = 'all';
                $activeAcademicYearName = 'Semua Tahun Ajaran';
            } elseif (!empty($academicYearInput) && $academicYears->contains('id', $academicYearInput)) {
                $selectedAcademicYearId = $academicYearInput;
                $activeAcademicYearName = $academicYears->firstWhere('id', $academicYearInput)->name ?? '-';
            } else {
                $selectedAcademicYearId = $activeAcademicYear?->id ?? 'all';
                $activeAcademicYearName = $activeAcademicYear?->name ?? 'Semua Tahun Ajaran';
            }

            $validPeriods = ['hari_ini', 'minggu_ini', 'bulan_ini', 'bulan_kemarin', '3_bulan', 'semua'];
            $selectedPeriod = $request->input('period', 'bulan_ini');
            if (!in_array($selectedPeriod, $validPeriods, true)) {
                $selectedPeriod = 'bulan_ini';
            }

            $periodLabels = [
                'hari_ini' => 'Hari Ini',
                'minggu_ini' => 'Minggu Ini',
                'bulan_ini' => 'Bulan Ini',
                'bulan_kemarin' => 'Bulan Kemarin',
                '3_bulan' => '3 Bulan Berjalan',
                'semua' => 'Semua Periode',
            ];
            $periodLabel = $periodLabels[$selectedPeriod] ?? 'Bulan Ini';

            $excludedBillTypeIds = $this->getExcludedBillTypeIds();
            $cacheKey = 'bi_dashboard_v7_' . md5(json_encode([
                'schools' => $scopedSchoolIds,
                'ay' => $selectedAcademicYearId,
                'period' => $selectedPeriod,
                'date' => Carbon::today()->toDateString(),
            ]));

            // 2. Cached BI Analytics (5-minute TTL for heavy aggregations)
            $biCachedData = Cache::remember($cacheKey, 300, function () use ($scopedSchoolIds, $selectedAcademicYearId, $excludedBillTypeIds, $selectedPeriod, $periodLabel) {
                $scopedClassroomIds = !empty($scopedSchoolIds)
                    ? Classroom::whereIn('school_id', $scopedSchoolIds)->pluck('id')->toArray()
                    : [];

                // --- A. Demografi & Ekosistem ---
                $studentBaseQuery = Student::where('status', Student::STATUS_ACTIVE)
                    ->when(!empty($scopedClassroomIds), fn($q) => $q->whereIn('classroom_id', $scopedClassroomIds), fn($q) => $q->whereRaw('1 = 0'));

                $totalSantri = (clone $studentBaseQuery)->count();
                $totalLaki = (clone $studentBaseQuery)->where('gender', 'L')->count();
                $totalPerempuan = (clone $studentBaseQuery)->where('gender', 'P')->count();
                $saldoMengendap = (int) (clone $studentBaseQuery)->sum('saldo');
                $tabunganMengendap = (int) (clone $studentBaseQuery)->sum('saving');

                $totalStaff = Admin::where('is_active', 1)->count();
                $totalWali = User::where('is_active', 1)
                    ->whereHas('student', function ($q) use ($scopedClassroomIds) {
                        $q->where('status', Student::STATUS_ACTIVE)
                            ->when(!empty($scopedClassroomIds), fn($sq) => $sq->whereIn('classroom_id', $scopedClassroomIds));
                    })
                    ->count();
                $totalKelas = count($scopedClassroomIds);

                $genderRatio = [
                    'l' => $totalLaki,
                    'p' => $totalPerempuan,
                ];

                // --- B. Tagihan & Realisasi Pembayaran (Scoped by School, Active Student & Visible Non-Deleted BillType) ---
                $billBaseQuery = DB::table('bills')
                    ->join('bill_types', 'bill_types.id', '=', 'bills.bill_type_id')
                    ->join('students', 'students.id', '=', 'bills.student_id')
                    ->join('classrooms', 'classrooms.id', '=', 'students.classroom_id')
                    ->whereNull('bills.deleted_at')
                    ->whereNull('bill_types.deleted_at')
                    ->whereNull('students.deleted_at')
                    ->where('bill_types.is_visible', 1)
                    ->where('students.status', Student::STATUS_ACTIVE)
                    ->whereNotIn('bills.bill_type_id', $excludedBillTypeIds)
                    ->when(!empty($scopedSchoolIds), fn($q) => $q->whereIn('classrooms.school_id', $scopedSchoolIds), fn($q) => $q->whereRaw('1 = 0'))
                    ->when($selectedAcademicYearId && $selectedAcademicYearId !== 'all', fn($q) => $q->where('bills.academic_year_id', $selectedAcademicYearId));

                $billTotals = (clone $billBaseQuery)->selectRaw("
                    COALESCE(SUM(bills.amount), 0) as target_amount,
                    COALESCE(SUM(bills.paid_amount), 0) as paid_amount,
                    COUNT(bills.id) as total_bills,
                    SUM(CASE WHEN bills.status = 'PAID' THEN 1 ELSE 0 END) as paid_bills,
                    SUM(CASE WHEN bills.status = 'PARTIAL' THEN 1 ELSE 0 END) as partial_bills,
                    SUM(CASE WHEN bills.status = 'UNPAID' THEN 1 ELSE 0 END) as unpaid_bills
                ")->first();

                $targetPembayaran = (int) ($billTotals->target_amount ?? 0);
                $realisasiPembayaran = (int) ($billTotals->paid_amount ?? 0);
                $sisaTunggakan = max(0, $targetPembayaran - $realisasiPembayaran);
                $realisasiPercentage = $targetPembayaran > 0
                    ? round(($realisasiPembayaran / $targetPembayaran) * 100, 1)
                    : 0;

                // Kanal Pembayaran Tagihan (Aplikasi Bukti Bayar Approval vs Entry Data Langsung)
                $channelTotals = (clone $billBaseQuery)
                    ->join('transaction_details', 'transaction_details.bill_id', '=', 'bills.id')
                    ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
                    ->leftJoin('payment_methods', 'payment_methods.id', '=', 'transactions.payment_method_id')
                    ->whereNull('transaction_details.deleted_at')
                    ->whereNull('transactions.deleted_at')
                    ->where('transactions.status', Transaction::STATUS_PAID)
                    ->where('transactions.type', Transaction::TYPE_BILL)
                    ->selectRaw("
                        COALESCE(SUM(CASE WHEN payment_methods.type = 'TRANSFER' THEN (CASE WHEN transaction_details.amount > 0 THEN transaction_details.amount ELSE bills.paid_amount END) ELSE 0 END), 0) as app_amount,
                        COALESCE(SUM(CASE WHEN payment_methods.type != 'TRANSFER' OR payment_methods.type IS NULL THEN (CASE WHEN transaction_details.amount > 0 THEN transaction_details.amount ELSE bills.paid_amount END) ELSE 0 END), 0) as direct_amount,
                        COUNT(DISTINCT CASE WHEN payment_methods.type = 'TRANSFER' THEN transactions.id END) as app_count,
                        COUNT(DISTINCT CASE WHEN payment_methods.type != 'TRANSFER' OR payment_methods.type IS NULL THEN transactions.id END) as direct_count
                    ")
                    ->first();

                $appAmount = (int) ($channelTotals->app_amount ?? 0);
                $directAmount = (int) ($channelTotals->direct_amount ?? 0);
                $appCount = (int) ($channelTotals->app_count ?? 0);
                $directCount = (int) ($channelTotals->direct_count ?? 0);
                $totalChannelAmount = $appAmount + $directAmount;

                $paymentChannelRatio = [
                    'app_amount' => $appAmount,
                    'direct_amount' => $directAmount,
                    'app_count' => $appCount,
                    'direct_count' => $directCount,
                    'app_pct' => $totalChannelAmount > 0 ? round(($appAmount / $totalChannelAmount) * 100, 1) : 0,
                    'direct_pct' => $totalChannelAmount > 0 ? round(($directAmount / $totalChannelAmount) * 100, 1) : 0,
                ];

                // Per-School Breakdown (Populasi + Realisasi Tagihan per Unit)
                $schoolBillsMap = (clone $billBaseQuery)
                    ->selectRaw('classrooms.school_id, COALESCE(SUM(bills.amount), 0) as target_amount, COALESCE(SUM(bills.paid_amount), 0) as paid_amount')
                    ->groupBy('classrooms.school_id')
                    ->get()
                    ->keyBy('school_id');

                $schoolData = School::whereIn('id', $scopedSchoolIds)
                    ->withCount([
                        'classroom as total_classes',
                        'students as total_students' => fn($q) => $q->where('status', Student::STATUS_ACTIVE),
                        'students as count_l' => fn($q) => $q->where('status', Student::STATUS_ACTIVE)->where('gender', 'L'),
                        'students as count_p' => fn($q) => $q->where('status', Student::STATUS_ACTIVE)->where('gender', 'P'),
                    ])
                    ->get()
                    ->map(function ($school) use ($schoolBillsMap) {
                        $sb = $schoolBillsMap->get($school->id);
                        $target = (int) ($sb->target_amount ?? 0);
                        $paid = (int) ($sb->paid_amount ?? 0);
                        $school->target_bill = $target;
                        $school->paid_bill = $paid;
                        $school->unpaid_bill = max(0, $target - $paid);
                        $school->realisasi_pct = $target > 0 ? round(($paid / $target) * 100, 1) : 0;
                        return $school;
                    });

                // --- C. Chart 1: Trend Target vs Realisasi Pembayaran per Bulan (Jul - Jun) ---
                $monthlyBillsRaw = (clone $billBaseQuery)
                    ->selectRaw('bills.month, COALESCE(SUM(bills.amount), 0) as target_amount, COALESCE(SUM(bills.paid_amount), 0) as paid_amount')
                    ->groupBy('bills.month')
                    ->get()
                    ->keyBy('month');

                $academicMonthOrder = [
                    7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
                    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
                ];

                $billTrendChart = [
                    'labels' => [],
                    'target' => [],
                    'realisasi' => [],
                    'tunggakan' => [],
                    'percentage' => [],
                ];

                foreach ($academicMonthOrder as $mNum => $mLabel) {
                    $row = $monthlyBillsRaw->get($mNum);
                    $mTarget = (int) ($row->target_amount ?? 0);
                    $mPaid = (int) ($row->paid_amount ?? 0);
                    $mUnpaid = max(0, $mTarget - $mPaid);
                    $mPct = $mTarget > 0 ? round(($mPaid / $mTarget) * 100, 1) : 0;

                    $billTrendChart['labels'][] = $mLabel;
                    $billTrendChart['target'][] = $mTarget;
                    $billTrendChart['realisasi'][] = $mPaid;
                    $billTrendChart['tunggakan'][] = $mUnpaid;
                    $billTrendChart['percentage'][] = $mPct;
                }

                // --- D. Chart 2: Trend Saldo Masuk & Pembayaran Tagihan (30 Hari & 12 Bulan) ---
                $start12Months = Carbon::now()->subMonths(11)->startOfMonth();

                // Query daily SaldoHistory IN over last 12 months
                $saldoDailyRows = DB::table('saldo_histories')
                    ->join('students', 'students.id', '=', 'saldo_histories.student_id')
                    ->join('classrooms', 'classrooms.id', '=', 'students.classroom_id')
                    ->whereNull('saldo_histories.deleted_at')
                    ->where('saldo_histories.type', SaldoHistory::TYPE_IN)
                    ->where('saldo_histories.status', SaldoHistory::STATUS_SUCCESS)
                    ->where('saldo_histories.created_at', '>=', $start12Months)
                    ->when(!empty($scopedSchoolIds), fn($q) => $q->whereIn('classrooms.school_id', $scopedSchoolIds), fn($q) => $q->whereRaw('1 = 0'))
                    ->selectRaw('DATE(saldo_histories.created_at) as date_only, COALESCE(SUM(saldo_histories.amount), 0) as total_amount')
                    ->groupBy('date_only')
                    ->pluck('total_amount', 'date_only')
                    ->toArray();

                // Query daily Bill Transactions PAID over last 12 months
                $billTxDailyRows = DB::table('transactions')
                    ->join('students', 'students.id', '=', 'transactions.student_id')
                    ->join('classrooms', 'classrooms.id', '=', 'students.classroom_id')
                    ->whereNull('transactions.deleted_at')
                    ->where('transactions.status', Transaction::STATUS_PAID)
                    ->where('transactions.type', Transaction::TYPE_BILL)
                    ->where('transactions.created_at', '>=', $start12Months)
                    ->when(!empty($scopedSchoolIds), fn($q) => $q->whereIn('classrooms.school_id', $scopedSchoolIds), fn($q) => $q->whereRaw('1 = 0'))
                    ->selectRaw('DATE(COALESCE(transactions.paid_at, transactions.created_at)) as date_only, COALESCE(SUM(transactions.pay_amount), 0) as total_amount')
                    ->groupBy('date_only')
                    ->pluck('total_amount', 'date_only')
                    ->toArray();

                // Query daily SavingHistory IN over last 12 months
                $savingDailyRows = DB::table('saving_histories')
                    ->join('students', 'students.id', '=', 'saving_histories.student_id')
                    ->join('classrooms', 'classrooms.id', '=', 'students.classroom_id')
                    ->whereNull('saving_histories.deleted_at')
                    ->where('saving_histories.type', SavingHistory::TYPE_IN)
                    ->where('saving_histories.status', SavingHistory::STATUS_SUCCESS)
                    ->where('saving_histories.created_at', '>=', $start12Months)
                    ->when(!empty($scopedSchoolIds), fn($q) => $q->whereIn('classrooms.school_id', $scopedSchoolIds), fn($q) => $q->whereRaw('1 = 0'))
                    ->selectRaw('DATE(saving_histories.created_at) as date_only, COALESCE(SUM(saving_histories.amount), 0) as total_amount')
                    ->groupBy('date_only')
                    ->pluck('total_amount', 'date_only')
                    ->toArray();

                // Build 30-day series
                $daily30 = [
                    'labels' => [],
                    'saldo_in' => [],
                    'bill_paid' => [],
                    'saving_in' => [],
                ];
                for ($i = 29; $i >= 0; $i--) {
                    $d = Carbon::today()->subDays($i);
                    $dKey = $d->toDateString();
                    $daily30['labels'][] = $d->translatedFormat('d M');
                    $daily30['saldo_in'][] = (int) ($saldoDailyRows[$dKey] ?? 0);
                    $daily30['bill_paid'][] = (int) ($billTxDailyRows[$dKey] ?? 0);
                    $daily30['saving_in'][] = (int) ($savingDailyRows[$dKey] ?? 0);
                }

                // Aggregate 12-month series in PHP from daily maps (100% SQLite & MySQL compatible)
                $saldoMonthlyMap = [];
                foreach ($saldoDailyRows as $dKey => $amt) {
                    if ($dKey) {
                        $ym = substr((string) $dKey, 0, 7);
                        $saldoMonthlyMap[$ym] = ($saldoMonthlyMap[$ym] ?? 0) + (int) $amt;
                    }
                }
                $billTxMonthlyMap = [];
                foreach ($billTxDailyRows as $dKey => $amt) {
                    if ($dKey) {
                        $ym = substr((string) $dKey, 0, 7);
                        $billTxMonthlyMap[$ym] = ($billTxMonthlyMap[$ym] ?? 0) + (int) $amt;
                    }
                }
                $savingMonthlyMap = [];
                foreach ($savingDailyRows as $dKey => $amt) {
                    if ($dKey) {
                        $ym = substr((string) $dKey, 0, 7);
                        $savingMonthlyMap[$ym] = ($savingMonthlyMap[$ym] ?? 0) + (int) $amt;
                    }
                }

                $monthly12 = [
                    'labels' => [],
                    'saldo_in' => [],
                    'bill_paid' => [],
                    'saving_in' => [],
                ];
                for ($i = 11; $i >= 0; $i--) {
                    $m = Carbon::now()->startOfMonth()->subMonths($i);
                    $ymKey = $m->format('Y-m');
                    $monthly12['labels'][] = $m->translatedFormat('M Y');
                    $monthly12['saldo_in'][] = (int) ($saldoMonthlyMap[$ymKey] ?? 0);
                    $monthly12['bill_paid'][] = (int) ($billTxMonthlyMap[$ymKey] ?? 0);
                    $monthly12['saving_in'][] = (int) ($savingMonthlyMap[$ymKey] ?? 0);
                }

                $inflowTrendChart = [
                    'daily_30' => $daily30,
                    'monthly_12' => $monthly12,
                ];

                $todayKey = Carbon::today()->toDateString();
                $thisMonthKey = Carbon::now()->format('Y-m');
                $lastMonthKey = Carbon::now()->subMonth()->format('Y-m');

                // Calculate period-specific cash inflow
                $now = Carbon::now();
                $pembayaranPeriode = 0;
                $saldoMasukPeriode = 0;
                $tabunganMasukPeriode = 0;

                if ($selectedPeriod === 'hari_ini') {
                    $pembayaranPeriode = (int) ($billTxDailyRows[$todayKey] ?? 0);
                    $saldoMasukPeriode = (int) ($saldoDailyRows[$todayKey] ?? 0);
                    $tabunganMasukPeriode = (int) ($savingDailyRows[$todayKey] ?? 0);
                } elseif ($selectedPeriod === 'minggu_ini') {
                    $startOfWeek = $now->copy()->startOfWeek();
                    $endOfWeek = $now->copy()->endOfWeek();
                    for ($cur = $startOfWeek->copy(); $cur->lte($endOfWeek); $cur->addDay()) {
                        $dStr = $cur->toDateString();
                        $pembayaranPeriode += (int) ($billTxDailyRows[$dStr] ?? 0);
                        $saldoMasukPeriode += (int) ($saldoDailyRows[$dStr] ?? 0);
                        $tabunganMasukPeriode += (int) ($savingDailyRows[$dStr] ?? 0);
                    }
                } elseif ($selectedPeriod === 'bulan_kemarin') {
                    $pembayaranPeriode = (int) ($billTxMonthlyMap[$lastMonthKey] ?? 0);
                    $saldoMasukPeriode = (int) ($saldoMonthlyMap[$lastMonthKey] ?? 0);
                    $tabunganMasukPeriode = (int) ($savingMonthlyMap[$lastMonthKey] ?? 0);
                } elseif ($selectedPeriod === '3_bulan') {
                    for ($i = 0; $i < 3; $i++) {
                        $ym = $now->copy()->subMonths($i)->format('Y-m');
                        $pembayaranPeriode += (int) ($billTxMonthlyMap[$ym] ?? 0);
                        $saldoMasukPeriode += (int) ($saldoMonthlyMap[$ym] ?? 0);
                        $tabunganMasukPeriode += (int) ($savingMonthlyMap[$ym] ?? 0);
                    }
                } elseif ($selectedPeriod === 'semua') {
                    $pembayaranPeriode = $realisasiPembayaran;
                    $saldoMasukPeriode = (int) array_sum($saldoMonthlyMap);
                    $tabunganMasukPeriode = (int) array_sum($savingMonthlyMap);
                } else { // bulan_ini (default)
                    $pembayaranPeriode = (int) ($billTxMonthlyMap[$thisMonthKey] ?? 0);
                    $saldoMasukPeriode = (int) ($saldoMonthlyMap[$thisMonthKey] ?? 0);
                    $tabunganMasukPeriode = (int) ($savingMonthlyMap[$thisMonthKey] ?? 0);
                }

                $biSummary = [
                    'target_pembayaran' => $targetPembayaran,
                    'realisasi_pembayaran' => $selectedPeriod === 'semua' ? $realisasiPembayaran : $pembayaranPeriode,
                    'realisasi_pembayaran_all' => $realisasiPembayaran,
                    'sisa_tunggakan' => $sisaTunggakan,
                    'realisasi_percentage' => $targetPembayaran > 0 ? round((($selectedPeriod === 'semua' ? $realisasiPembayaran : $pembayaranPeriode) / $targetPembayaran) * 100, 1) : 0,
                    'realisasi_percentage_all' => $realisasiPercentage,
                    'total_bill_items' => (int) ($billTotals->total_bills ?? 0),
                    'paid_bill_items' => (int) ($billTotals->paid_bills ?? 0),
                    'partial_bill_items' => (int) ($billTotals->partial_bills ?? 0),
                    'unpaid_bill_items' => (int) ($billTotals->unpaid_bills ?? 0),
                    'pembayaran_hari_ini' => (int) ($billTxDailyRows[$todayKey] ?? 0),
                    'pembayaran_bulan_ini' => (int) ($billTxMonthlyMap[$thisMonthKey] ?? 0),
                    'pembayaran_periode' => $pembayaranPeriode,
                    'saldo_masuk_hari_ini' => (int) ($saldoDailyRows[$todayKey] ?? 0),
                    'saldo_masuk_bulan_ini' => (int) ($saldoMonthlyMap[$thisMonthKey] ?? 0),
                    'saldo_masuk_periode' => $saldoMasukPeriode,
                    'saldo_masuk_badge' => $selectedPeriod === 'semua' ? 'Semua Top-Up' : ('Top-Up ' . $periodLabel),
                    'saldo_masuk_12_bulan' => array_sum($monthly12['saldo_in']),
                    'saldo_mengendap' => $saldoMengendap,
                    'tabungan_masuk_bulan_ini' => (int) ($savingMonthlyMap[$thisMonthKey] ?? 0),
                    'tabungan_masuk_periode' => $tabunganMasukPeriode,
                    'tabungan_mengendap' => $tabunganMengendap,
                ];

                $upcomingSchedules = Schedule::with('school')
                    ->whereDate('date', '>=', Carbon::today())
                    ->orderBy('date', 'asc')
                    ->limit(5)
                    ->get();

                // --- E. FINANCIAL DISCREPANCY & ANOMALY MONITORING ---
                // Siswa dengan tunggakan jatuh tempo s.d. bulan berjalan (terutama multi-bulan),
                // namun memiliki mutasi top-up saldo saku/belanja aktif bernominal tinggi.
                $currentYear = (int) Carbon::now()->year;
                $currentMonth = (int) Carbon::now()->month;
                $thirtyDaysAgo = Carbon::now()->subDays(30);

                // 1. Unpaid bills due up to current month for active students in scoped schools
                $dueUnpaidBillsQuery = DB::table('bills')
                    ->join('bill_types', 'bill_types.id', '=', 'bills.bill_type_id')
                    ->join('students', 'students.id', '=', 'bills.student_id')
                    ->join('classrooms', 'classrooms.id', '=', 'students.classroom_id')
                    ->whereNull('bills.deleted_at')
                    ->whereNull('bill_types.deleted_at')
                    ->whereNull('students.deleted_at')
                    ->where('bill_types.is_visible', 1)
                    ->where('students.status', Student::STATUS_ACTIVE)
                    ->whereNotIn('bills.bill_type_id', $excludedBillTypeIds)
                    ->whereIn('bills.status', [Bill::STATUS_UNPAID, Bill::STATUS_PARTIAL])
                    ->when(!empty($scopedSchoolIds), fn($q) => $q->whereIn('classrooms.school_id', $scopedSchoolIds), fn($q) => $q->whereRaw('1 = 0'))
                    ->when($selectedAcademicYearId && $selectedAcademicYearId !== 'all', fn($q) => $q->where('bills.academic_year_id', $selectedAcademicYearId))
                    ->where(function ($query) use ($currentYear, $currentMonth) {
                        $query->where(function ($sub) use ($currentYear, $currentMonth) {
                            $sub->whereNotNull('bills.year')
                                ->whereNotNull('bills.month')
                                ->where(function ($dateSub) use ($currentYear, $currentMonth) {
                                    $dateSub->where('bills.year', '<', $currentYear)
                                            ->orWhere(function ($sameYear) use ($currentYear, $currentMonth) {
                                                $sameYear->where('bills.year', '=', $currentYear)
                                                         ->where('bills.month', '<=', $currentMonth);
                                            });
                                });
                        })
                        ->orWhere(function ($sub) use ($currentYear) {
                            $sub->whereNotNull('bills.year')
                                ->where(function ($mSub) {
                                    $mSub->whereNull('bills.month')->orWhere('bills.month', 0);
                                })
                                ->where('bills.year', '<=', $currentYear);
                        })
                        ->orWhere(function ($sub) {
                            $sub->whereNull('bills.year')
                                ->whereDate('bills.created_at', '<=', Carbon::now()->endOfMonth());
                        });
                    });

                $unpaidPerStudent = (clone $dueUnpaidBillsQuery)
                    ->selectRaw('
                        bills.student_id,
                        COUNT(bills.id) as unpaid_count,
                        COALESCE(SUM(bills.amount - bills.paid_amount), 0) as total_unpaid_amount,
                        COUNT(DISTINCT CASE WHEN bills.month > 0 THEN bills.month END) as distinct_unpaid_months
                    ')
                    ->groupBy('bills.student_id')
                    ->get()
                    ->keyBy('student_id');

                $unpaidBillRows = (clone $dueUnpaidBillsQuery)
                    ->select(
                        'bills.student_id',
                        'bills.month',
                        'bills.year',
                        'bills.amount',
                        'bills.paid_amount',
                        'bills.status',
                        'bill_types.name as bill_type_name'
                    )
                    ->orderBy('bills.year', 'asc')
                    ->orderBy('bills.month', 'asc')
                    ->get()
                    ->groupBy('student_id');

                // 2. Pocket money top-up in the last 30 days
                $topupPerStudent = DB::table('saldo_histories')
                    ->join('students', 'students.id', '=', 'saldo_histories.student_id')
                    ->join('classrooms', 'classrooms.id', '=', 'students.classroom_id')
                    ->whereNull('saldo_histories.deleted_at')
                    ->whereNull('students.deleted_at')
                    ->where('students.status', Student::STATUS_ACTIVE)
                    ->where('saldo_histories.type', SaldoHistory::TYPE_IN)
                    ->where('saldo_histories.status', SaldoHistory::STATUS_SUCCESS)
                    ->where('saldo_histories.created_at', '>=', $thirtyDaysAgo)
                    ->when(!empty($scopedSchoolIds), fn($q) => $q->whereIn('classrooms.school_id', $scopedSchoolIds), fn($q) => $q->whereRaw('1 = 0'))
                    ->selectRaw('
                        saldo_histories.student_id,
                        COUNT(saldo_histories.id) as topup_count,
                        COALESCE(SUM(saldo_histories.amount), 0) as total_topup_amount
                    ')
                    ->groupBy('saldo_histories.student_id')
                    ->get()
                    ->keyBy('student_id');

                // 3. POS spending in the last 30 days
                $posSpendingPerStudent = DB::table('point_of_sale_transactions')
                    ->join('students', 'students.id', '=', 'point_of_sale_transactions.student_id')
                    ->join('classrooms', 'classrooms.id', '=', 'students.classroom_id')
                    ->whereNull('point_of_sale_transactions.deleted_at')
                    ->whereNull('students.deleted_at')
                    ->where('students.status', Student::STATUS_ACTIVE)
                    ->where('point_of_sale_transactions.status', PointOfSaleTransaction::STATUS_SUCCESS)
                    ->where('point_of_sale_transactions.created_at', '>=', $thirtyDaysAgo)
                    ->when(!empty($scopedSchoolIds), fn($q) => $q->whereIn('classrooms.school_id', $scopedSchoolIds), fn($q) => $q->whereRaw('1 = 0'))
                    ->selectRaw('
                        point_of_sale_transactions.student_id,
                        COUNT(point_of_sale_transactions.id) as pos_count,
                        COALESCE(SUM(point_of_sale_transactions.pay_amount), 0) as total_pos_amount
                    ')
                    ->groupBy('point_of_sale_transactions.student_id')
                    ->get()
                    ->keyBy('student_id');

                $academicMonthNames = [
                    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                    5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
                    9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
                ];

                // 4. Candidate students with both unpaid bills and active top-up in 30 days
                $candidateStudentIds = $unpaidPerStudent->keys()->intersect($topupPerStudent->keys())->values();

                $anomalyStudentsList = [];
                $kritisCount = 0;
                $waspadaCount = 0;
                $perhatianCount = 0;
                $totalTunggakanAnomali = 0;
                $totalTopupAnomali = 0;
                $totalBelanjaAnomali = 0;

                if ($candidateStudentIds->isNotEmpty()) {
                    $studentsInfo = Student::whereIn('id', $candidateStudentIds)
                        ->with([
                            'user:id,name,phone,avatar,jamaah_status',
                            'classroom:id,name,school_id',
                            'classroom.school:id,name,type',
                        ])
                        ->get(['id', 'name', 'nis', 'user_id', 'classroom_id', 'school_id', 'saldo', 'avatar'])
                        ->keyBy('id');

                    foreach ($candidateStudentIds as $sId) {
                        $uRow = $unpaidPerStudent->get($sId);
                        $tRow = $topupPerStudent->get($sId);
                        $pRow = $posSpendingPerStudent->get($sId);
                        $student = $studentsInfo->get($sId);

                        if (!$uRow || !$tRow || !$student) {
                            continue;
                        }

                        $unpaidAmount = (int) $uRow->total_unpaid_amount;
                        $unpaidCount = (int) $uRow->unpaid_count;
                        $distinctMonths = (int) $uRow->distinct_unpaid_months;
                        $effectiveMonthCount = max($distinctMonths, min($unpaidCount, 1));
                        $topupAmount = (int) $tRow->total_topup_amount;
                        $topupCount = (int) $tRow->topup_count;
                        $posAmount = (int) ($pRow->total_pos_amount ?? 0);
                        $posCount = (int) ($pRow->pos_count ?? 0);

                        if ($unpaidAmount <= 0 || $topupAmount <= 0) {
                            continue;
                        }

                        // Determine Severity Tier
                        // KRITIS: >= 2 bulan & (topup >= 500k atau topup >= 50% tunggakan)
                        // WASPADA: >= 2 bulan & topup > 0
                        // PERHATIAN: 1 bulan & topup >= 500k
                        $isMultiMonth = $effectiveMonthCount >= 2;
                        $tier = null;
                        $tierLabel = '';
                        $tierBadgeClass = '';
                        $tierDescription = '';
                        $severityRank = 99;

                        if ($isMultiMonth && ($topupAmount >= 500000 || $topupAmount >= ($unpaidAmount * 0.5))) {
                            $tier = 'KRITIS';
                            $tierLabel = 'Kritis';
                            $tierBadgeClass = 'tint-red';
                            $tierDescription = 'Tunggakan multi-bulan dengan alokasi top-up saku sangat tinggi';
                            $severityRank = 1;
                            $kritisCount++;
                        } elseif ($isMultiMonth) {
                            $tier = 'WASPADA';
                            $tierLabel = 'Waspada';
                            $tierBadgeClass = 'tint-warning';
                            $tierDescription = 'Tunggakan multi-bulan disertai mutasi uang saku aktif';
                            $severityRank = 2;
                            $waspadaCount++;
                        } elseif ($topupAmount >= 500000) {
                            $tier = 'PERHATIAN';
                            $tierLabel = 'Perhatian';
                            $tierBadgeClass = 'tint-slate';
                            $tierDescription = 'Tunggakan 1 bulan jatuh tempo dengan top-up belanja tinggi';
                            $severityRank = 3;
                            $perhatianCount++;
                        }

                        if (!$tier) {
                            continue;
                        }

                        $totalTunggakanAnomali += $unpaidAmount;
                        $totalTopupAnomali += $topupAmount;
                        $totalBelanjaAnomali += $posAmount;

                        // Parse detailed unpaid items
                        $rawItems = $unpaidBillRows->get($sId) ?? collect([]);
                        $monthLabels = [];
                        $formattedItems = [];

                        foreach ($rawItems as $item) {
                            $mLabel = $item->month ? ($academicMonthNames[$item->month] ?? 'Bln ' . $item->month) : '';
                            if ($mLabel && !in_array($mLabel, $monthLabels, true)) {
                                $monthLabels[] = $mLabel;
                            }
                            $periodStr = $mLabel ? ($mLabel . ($item->year ? ' ' . $item->year : '')) : ($item->year ? 'TA ' . $item->year : '-');
                            $remaining = max(0, (int) $item->amount - (int) $item->paid_amount);

                            $formattedItems[] = [
                                'name' => $item->bill_type_name,
                                'period' => $periodStr,
                                'remaining' => $remaining,
                                'status' => $item->status,
                            ];
                        }

                        // WhatsApp Message link generator
                        $waliUser = $student->user;
                        $waliName = $waliUser?->name;
                        $waliPhone = $waliUser?->phone;
                        $waliJamaah = $waliUser?->jamaah_status;
                        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $waliPhone);
                        if (str_starts_with($cleanPhone, '0')) {
                            $cleanPhone = '62' . substr($cleanPhone, 1);
                        }

                        $monthSummaryStr = !empty($monthLabels) ? implode(', ', $monthLabels) : ($effectiveMonthCount . ' Bulan');
                        $nominalFormatted = number_format($unpaidAmount, 0, ',', '.');

                        $waText = "Assalamu'alaikum Wr. Wb.\n\n"
                            . "Yth. Bapak/Ibu *" . ($waliName ?: 'Orang Tua / Wali') . "*,\n"
                            . "Semoga senantiasa dalam keadaan sehat wal'afiat.\n\n"
                            . "Kami dari Bagian Keuangan dan Administrasi Pondok Pesantren Cahaya Tasbih bermaksud menginformasikan perihal administrasi ananda *" . $student->name . "* (Kelas: " . ($student->classroom->name ?? '-') . ", " . ($student->classroom->school->name ?? '-') . ").\n\n"
                            . "Tercatat saat ini ananda memiliki kewajiban tagihan yang telah jatuh tempo s.d. bulan ini sebesar *Rp " . $nominalFormatted . "* (" . $monthSummaryStr . ").\n\n"
                            . "Mohon kesediaan Bapak/Ibu untuk dapat segera melakukan pelunasan atau konfirmasi melalui Portal Aplikasi Wali Santri atau rekening resmi pesantren.\n\n"
                            . "Jazakumullah khairan katsiran atas perhatian dan kerja sama Bapak/Ibu.\n"
                            . "Wassalamu'alaikum Wr. Wb.";

                        $waLink = (strlen($cleanPhone) >= 9)
                            ? 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($waText)
                            : null;

                        $anomalyStudentsList[] = [
                            'student_id' => $student->id,
                            'name' => $student->name,
                            'nis' => $student->nis ?? '-',
                            'classroom_name' => $student->classroom->name ?? '-',
                            'school_name' => $student->classroom->school->name ?? '-',
                            'school_type' => $student->classroom->school->type ?? '-',
                            'saldo' => (int) $student->saldo,
                            'avatar' => $student->avatar,
                            'wali_name' => $waliName,
                            'wali_phone' => $waliPhone,
                            'wali_jamaah' => $waliJamaah,
                            'clean_phone' => $cleanPhone,
                            'wa_link' => $waLink,
                            'unpaid_amount' => $unpaidAmount,
                            'unpaid_count' => $unpaidCount,
                            'unpaid_months_count' => $effectiveMonthCount,
                            'unpaid_month_labels' => $monthLabels,
                            'unpaid_items' => $formattedItems,
                            'topup_amount' => $topupAmount,
                            'topup_count' => $topupCount,
                            'pos_amount' => $posAmount,
                            'pos_count' => $posCount,
                            'tier' => $tier,
                            'tier_label' => $tierLabel,
                            'tier_badge_class' => $tierBadgeClass,
                            'tier_description' => $tierDescription,
                            'severity_rank' => $severityRank,
                            'ratio' => round(($topupAmount / $unpaidAmount) * 100),
                        ];
                    }
                }

                // Sort: Severity Rank (1=Kritis, 2=Waspada, 3=Perhatian), then highest unpaid amount, then highest topup
                usort($anomalyStudentsList, function ($a, $b) {
                    if ($a['severity_rank'] !== $b['severity_rank']) {
                        return $a['severity_rank'] <=> $b['severity_rank'];
                    }
                    if ($a['unpaid_amount'] !== $b['unpaid_amount']) {
                        return $b['unpaid_amount'] <=> $a['unpaid_amount'];
                    }
                    return $b['topup_amount'] <=> $a['topup_amount'];
                });

                $allAnomalies = collect($anomalyStudentsList);
                $topAnomalies = $allAnomalies->take(10)->values();

                $anomalySummary = [
                    'total_santri' => $allAnomalies->count(),
                    'kritis_count' => $kritisCount,
                    'waspada_count' => $waspadaCount,
                    'perhatian_count' => $perhatianCount,
                    'total_tunggakan' => $totalTunggakanAnomali,
                    'total_topup_30d' => $totalTopupAnomali,
                    'total_belanja_30d' => $totalBelanjaAnomali,
                    'rasio_overall' => $totalTunggakanAnomali > 0 ? round(($totalTopupAnomali / $totalTunggakanAnomali) * 100) : 0,
                ];

                return compact(
                    'totalSantri',
                    'totalStaff',
                    'totalWali',
                    'totalKelas',
                    'schoolData',
                    'genderRatio',
                    'paymentChannelRatio',
                    'biSummary',
                    'billTrendChart',
                    'inflowTrendChart',
                    'upcomingSchedules',
                    'anomalySummary',
                    'topAnomalies',
                    'allAnomalies'
                );
            });

            extract($biCachedData);

            // 3. Real-Time Query: 10 Aktivitas Pembayaran Terkini (Live)
            $recentPayments = Transaction::where('status', Transaction::STATUS_PAID)
                ->with([
                    'student:id,name,nis,classroom_id,school_id,avatar,user_id,saldo',
                    'student.user:id,name,phone,avatar,jamaah_status',
                    'student.classroom:id,name,school_id',
                    'student.classroom.school:id,name,type',
                    'paymentMethod:id,name',
                    'admin:id,name',
                    'transactionDetails.bill.billType:id,name',
                ])
                ->when(!empty($scopedSchoolIds), function ($query) use ($scopedSchoolIds) {
                    $classroomIds = Classroom::whereIn('school_id', $scopedSchoolIds)->pluck('id')->toArray();
                    $query->whereHas('student', fn($q) => $q->whereIn('classroom_id', $classroomIds));
                }, function ($query) {
                    $query->whereRaw('1 = 0');
                })
                ->orderByRaw('COALESCE(paid_at, updated_at, created_at) DESC')
                ->limit(10)
                ->get();

            // 3b. Real-Time Financial Intelligence per Santri (Unpaid Bills & 30-Day Top-up Capability)
            $recentStudentIds = $recentPayments->pluck('student_id')->filter()->unique()->values();

            $studentFinancialProfiles = [];
            if ($recentStudentIds->isNotEmpty()) {
                // Query unpaid bills scoped by selected academic year and up to current month (jatuh tempo)
                $currentYear = (int) Carbon::now()->year;
                $currentMonth = (int) Carbon::now()->month;

                $unpaidBillsQuery = DB::table('bills')
                    ->join('bill_types', 'bill_types.id', '=', 'bills.bill_type_id')
                    ->whereIn('bills.student_id', $recentStudentIds)
                    ->whereNull('bills.deleted_at')
                    ->whereNull('bill_types.deleted_at')
                    ->where('bill_types.is_visible', 1)
                    ->whereNotIn('bills.bill_type_id', $excludedBillTypeIds ?? [])
                    ->whereIn('bills.status', [Bill::STATUS_UNPAID, Bill::STATUS_PARTIAL])
                    ->when($selectedAcademicYearId && $selectedAcademicYearId !== 'all', fn($q) => $q->where('bills.academic_year_id', $selectedAcademicYearId))
                    ->where(function ($query) use ($currentYear, $currentMonth) {
                        $query->where(function ($sub) use ($currentYear, $currentMonth) {
                            $sub->whereNotNull('bills.year')
                                ->whereNotNull('bills.month')
                                ->where(function ($dateSub) use ($currentYear, $currentMonth) {
                                    $dateSub->where('bills.year', '<', $currentYear)
                                            ->orWhere(function ($sameYear) use ($currentYear, $currentMonth) {
                                                $sameYear->where('bills.year', '=', $currentYear)
                                                         ->where('bills.month', '<=', $currentMonth);
                                            });
                                });
                        })
                        ->orWhere(function ($sub) use ($currentYear) {
                            $sub->whereNotNull('bills.year')
                                ->where(function ($mSub) {
                                    $mSub->whereNull('bills.month')->orWhere('bills.month', 0);
                                })
                                ->where('bills.year', '<=', $currentYear);
                        })
                        ->orWhere(function ($sub) {
                            $sub->whereNull('bills.year')
                                ->whereDate('bills.created_at', '<=', Carbon::now()->endOfMonth());
                        });
                    });

                $unpaidTotals = (clone $unpaidBillsQuery)
                    ->selectRaw('bills.student_id, COUNT(bills.id) as unpaid_count, COALESCE(SUM(bills.amount - bills.paid_amount), 0) as total_unpaid_amount')
                    ->groupBy('bills.student_id')
                    ->get()
                    ->keyBy('student_id');

                $unpaidItemsRaw = (clone $unpaidBillsQuery)
                    ->select('bills.id', 'bills.student_id', 'bills.month', 'bills.year', 'bills.amount', 'bills.paid_amount', 'bills.status', 'bill_types.name as bill_type_name')
                    ->orderBy('bills.year', 'asc')
                    ->orderBy('bills.month', 'asc')
                    ->get()
                    ->groupBy('student_id');

                $thirtyDaysAgo = Carbon::now()->subDays(30);
                $topup30dTotals = DB::table('saldo_histories')
                    ->whereIn('student_id', $recentStudentIds)
                    ->whereNull('deleted_at')
                    ->where('type', SaldoHistory::TYPE_IN)
                    ->where('status', SaldoHistory::STATUS_SUCCESS)
                    ->where('created_at', '>=', $thirtyDaysAgo)
                    ->selectRaw('student_id, COUNT(id) as topup_count, COALESCE(SUM(amount), 0) as total_topup_amount')
                    ->groupBy('student_id')
                    ->get()
                    ->keyBy('student_id');

                $academicMonthNames = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                ];

                foreach ($recentStudentIds as $sId) {
                    $uTotal = $unpaidTotals->get($sId);
                    $unpaidAmount = (int) ($uTotal->total_unpaid_amount ?? 0);
                    $unpaidCount = (int) ($uTotal->unpaid_count ?? 0);

                    $tTotal = $topup30dTotals->get($sId);
                    $topupAmount = (int) ($tTotal->total_topup_amount ?? 0);
                    $topupCount = (int) ($tTotal->topup_count ?? 0);

                    if ($unpaidAmount === 0) {
                        $capabilityStatus = 'BEBAS_TUNGGAKAN';
                        $capabilityLabel = 'Bebas Tunggakan';
                        $capabilityBadgeClass = 'tint-emerald';
                        $capabilityRatio = null;
                    } elseif ($topupAmount >= $unpaidAmount) {
                        $capabilityStatus = 'LIKUID_TINGGI';
                        $capabilityLabel = 'Likuiditas Sangat Baik';
                        $capabilityBadgeClass = 'tint-emerald';
                        $capabilityRatio = round(($topupAmount / $unpaidAmount) * 100);
                    } elseif ($topupAmount > 0) {
                        $capabilityStatus = 'LIKUID_MODERAT';
                        $capabilityLabel = 'Likuiditas Moderat';
                        $capabilityBadgeClass = 'tint-warning';
                        $capabilityRatio = round(($topupAmount / $unpaidAmount) * 100);
                    } else {
                        $capabilityStatus = 'PERLU_ATENSI';
                        $capabilityLabel = 'Perlu Atensi (Tanpa Top-up 30 Hari)';
                        $capabilityBadgeClass = 'tint-error';
                        $capabilityRatio = 0;
                    }

                    $items = ($unpaidItemsRaw->get($sId) ?? collect([]))->map(function ($item) use ($academicMonthNames) {
                        $period = '';
                        if ($item->month) {
                            $period = ($academicMonthNames[$item->month] ?? 'Bln ' . $item->month) . ($item->year ? ' ' . $item->year : '');
                        } elseif ($item->year) {
                            $period = 'TA ' . $item->year;
                        }
                        $remaining = max(0, (int) $item->amount - (int) $item->paid_amount);

                        return [
                            'name' => $item->bill_type_name,
                            'period' => $period,
                            'remaining' => $remaining,
                            'status' => $item->status,
                        ];
                    });

                    $studentFinancialProfiles[$sId] = [
                        'unpaid_amount' => $unpaidAmount,
                        'unpaid_count' => $unpaidCount,
                        'topup_amount' => $topupAmount,
                        'topup_count' => $topupCount,
                        'capability_status' => $capabilityStatus,
                        'capability_label' => $capabilityLabel,
                        'capability_badge_class' => $capabilityBadgeClass,
                        'capability_ratio' => $capabilityRatio,
                        'unpaid_items' => $items,
                    ];
                }
            }

            foreach ($recentPayments as $tx) {
                $tx->financial_profile = $studentFinancialProfiles[$tx->student_id] ?? [
                    'unpaid_amount' => 0,
                    'unpaid_count' => 0,
                    'topup_amount' => 0,
                    'topup_count' => 0,
                    'capability_status' => 'BEBAS_TUNGGAKAN',
                    'capability_label' => 'Bebas Tunggakan',
                    'capability_badge_class' => 'tint-emerald',
                    'capability_ratio' => null,
                    'unpaid_items' => collect([]),
                ];
            }

            // 4. Real-Time Query: Aktivitas Login Wali Santri & User Backoffice
            $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();

            if ($user instanceof Admin) {
                Cache::forget('admin_logged_out_' . $user->id);
                if (!$user->last_login_at || $user->last_login_at->diffInSeconds(now()) >= 120) {
                    $now = now();
                    Admin::where('id', $user->id)->update(['last_login_at' => $now]);
                    $user->last_login_at = $now;
                }
            }

            $today = Carbon::today();
            $onlineThreshold = Carbon::now()->subMinutes(15);
            $sevenDaysAgo = Carbon::today()->subDays(6)->startOfDay();
            $thirtyDaysAgo = Carbon::today()->subDays(29)->startOfDay();

            $isSchoolFiltered = !empty($selectedSchoolId) || count($scopedSchoolIds) < count($allAllowedSchoolIds);
            $waliScopedClassrooms = !empty($scopedSchoolIds)
                ? Classroom::whereIn('school_id', $scopedSchoolIds)->pluck('id')->toArray()
                : [];

            $staffBaseQuery = Admin::where('is_active', 1)
                ->when(!empty($selectedSchoolId), fn($q) => $q->whereIn('school_id', $scopedSchoolIds));
            $scopedTotalStaff = (clone $staffBaseQuery)->count();
            $staffLoginToday = (clone $staffBaseQuery)->whereDate('last_login_at', $today)->count();
            $staffLogin7d = (clone $staffBaseQuery)->where('last_login_at', '>=', $sevenDaysAgo)->count();

            // 1. Wali Santri Aktif Riil (Hanya menghitung wali yang memiliki santri berstatus ACTIVE)
            $waliBaseQuery = User::where('is_active', 1)
                ->whereHas('student', function ($q) use ($waliScopedClassrooms) {
                    $q->where('status', Student::STATUS_ACTIVE)
                        ->when(!empty($waliScopedClassrooms), fn($sq) => $sq->whereIn('classroom_id', $waliScopedClassrooms));
                });

            $scopedTotalWali = (clone $waliBaseQuery)->count();
            $totalWali = $scopedTotalWali;
            $waliLogin7d = (clone $waliBaseQuery)->where('last_login', '>=', $sevenDaysAgo)->count();
            $waliLoginToday = (clone $waliBaseQuery)->whereDate('last_login', $today)->count();
            $waliLogin30d = (clone $waliBaseQuery)->where('last_login', '>=', $thirtyDaysAgo)->count();
            $waliLoginEver = (clone $waliBaseQuery)->whereNotNull('last_login')->count();
            $waliNeverLogin = max(0, $scopedTotalWali - $waliLoginEver);

            // 2. Audit: Wali Santri yang anaknya SUDAH NON-AKTIF (Lulus, DO, Pindah)
            $waliInactiveBaseQuery = User::where('is_active', 1)
                ->whereHas('student', function ($q) use ($waliScopedClassrooms) {
                    $q->when(!empty($waliScopedClassrooms), fn($sq) => $sq->whereIn('classroom_id', $waliScopedClassrooms));
                })
                ->whereDoesntHave('student', function ($q) use ($waliScopedClassrooms) {
                    $q->where('status', Student::STATUS_ACTIVE)
                        ->when(!empty($waliScopedClassrooms), fn($sq) => $sq->whereIn('classroom_id', $waliScopedClassrooms));
                });

            $waliInactiveCount = (clone $waliInactiveBaseQuery)->count();
            $waliAlumniCount = (clone $waliInactiveBaseQuery)->whereHas('student', fn($q) => $q->where('status', Student::STATUS_GRADUATED))->count();
            $waliDroppedOutCount = (clone $waliInactiveBaseQuery)->whereHas('student', fn($q) => $q->where('status', Student::STATUS_DROPPED_OUT))->count();
            $waliTransferredCount = (clone $waliInactiveBaseQuery)->whereHas('student', fn($q) => $q->where('status', Student::STATUS_TRANSFERRED))->count();

            // 3. Audit: Akun terdaftar tanpa santri sama sekali (Orphan / Calon PPDB Batal / Uji Coba)
            $waliOrphanCount = User::where('is_active', 1)->whereDoesntHave('student')->count();
            $totalRegisteredUsers = User::where('is_active', 1)->count();

            $buildWaliPwaMetric = function (int $totalCount, int $loginCount): array {
                $notLoginCount = max(0, $totalCount - $loginCount);
                $loginPct = $totalCount > 0 ? round(($loginCount / $totalCount) * 100, 1) : 0.0;
                $notLoginPct = $totalCount > 0 ? round(100 - $loginPct, 1) : 0.0;

                return [
                    'total' => $totalCount,
                    'total_pct' => $totalCount > 0 ? 100.0 : 0.0,
                    'login' => $loginCount,
                    'login_pct' => $loginPct,
                    'not_login' => $notLoginCount,
                    'not_login_pct' => $notLoginPct,
                ];
            };

            $waliPwaChart = [
                'all' => $buildWaliPwaMetric($scopedTotalWali, $waliLoginEver),
                '30d' => $buildWaliPwaMetric($scopedTotalWali, $waliLogin30d),
                '7d' => $buildWaliPwaMetric($scopedTotalWali, $waliLogin7d),
                'today' => $buildWaliPwaMetric($scopedTotalWali, $waliLoginToday),
            ];

            if ($isSuperAdmin) {
                $scopedStaffIds = !empty($selectedSchoolId)
                    ? (clone $staffBaseQuery)->pluck('id')->toArray()
                    : null;

                $staffActivityBaseQuery = Activity::where('causer_type', Admin::class)
                    ->whereNotNull('causer_id')
                    ->when($scopedStaffIds !== null, fn($q) => $q->whereIn('causer_id', $scopedStaffIds));

                $staffActivityToday = (clone $staffActivityBaseQuery)->whereDate('created_at', $today)->count();
                $staffActivity7d = (clone $staffActivityBaseQuery)->where('created_at', '>=', $sevenDaysAgo)->count();

                // Hanya ambil User Backoffice yang SEDANG ONLINE (aktif dalam 15 menit terakhir & belum logout)
                $recentStaffLogins = Admin::with(['roles:id,name', 'school:id,name'])
                    ->where('is_active', 1)
                    ->whereNotNull('last_login_at')
                    ->where('last_login_at', '>=', $onlineThreshold)
                    ->when(!empty($selectedSchoolId), fn($q) => $q->whereIn('school_id', $scopedSchoolIds))
                    ->orderByDesc('last_login_at')
                    ->get(['id', 'name', 'email', 'phone', 'avatar', 'school_id', 'role_id', 'last_login_at', 'access_scope'])
                    ->reject(fn($staff) => Cache::has('admin_logged_out_' . $staff->id))
                    ->values();

                $staffOnlineCount = $recentStaffLogins->count();

                $recentStaffActivities = (clone $staffActivityBaseQuery)
                    ->with(['causer.roles', 'causer.school'])
                    ->orderByDesc('created_at')
                    ->limit(15)
                    ->get();
            } else {
                $staffActivityToday = 0;
                $staffActivity7d = 0;
                $staffOnlineCount = 0;
                $recentStaffLogins = collect([]);
                $recentStaffActivities = collect([]);
            }

            $loginActivity = [
                'staff_online_count' => $staffOnlineCount,
                'staff_online_percentage' => $scopedTotalStaff > 0 ? round(($staffOnlineCount / $scopedTotalStaff) * 100, 1) : 0,
                'staff_count' => $staffLoginToday,
                'staff_7d' => $staffLogin7d,
                'staff_total' => $scopedTotalStaff,
                'staff_percentage' => $scopedTotalStaff > 0 ? round(($staffLoginToday / $scopedTotalStaff) * 100, 1) : 0,
                'staff_7d_percentage' => $scopedTotalStaff > 0 ? round(($staffLogin7d / $scopedTotalStaff) * 100, 1) : 0,
                'staff_activity_today' => $staffActivityToday,
                'staff_activity_7d' => $staffActivity7d,
                'wali_count' => $waliLoginToday,
                'wali_7d' => $waliLogin7d,
                'wali_30d' => $waliLogin30d,
                'wali_ever' => $waliLoginEver,
                'wali_never' => $waliNeverLogin,
                'wali_total' => $scopedTotalWali,
                'wali_inactive' => $waliInactiveCount,
                'wali_alumni' => $waliAlumniCount,
                'wali_dropped_out' => $waliDroppedOutCount,
                'wali_transferred' => $waliTransferredCount,
                'wali_orphan' => $waliOrphanCount,
                'wali_registered_total' => $totalRegisteredUsers,
                'wali_percentage' => $scopedTotalWali > 0 ? round(($waliLoginToday / $scopedTotalWali) * 100, 1) : 0,
                'wali_7d_percentage' => $scopedTotalWali > 0 ? round(($waliLogin7d / $scopedTotalWali) * 100, 1) : 0,
                'wali_ever_percentage' => $scopedTotalWali > 0 ? round(($waliLoginEver / $scopedTotalWali) * 100, 1) : 0,
                'wali_never_percentage' => $scopedTotalWali > 0 ? round(($waliNeverLogin / $scopedTotalWali) * 100, 1) : 0,
                'wali_pwa_chart' => $waliPwaChart,
            ];

            return view('admins.dashboard.index', compact(
                'isOutletUser',
                'isSuperAdmin',
                'canViewBiDashboard',
                'schools',
                'academicYears',
                'selectedSchoolId',
                'selectedAcademicYearId',
                'activeAcademicYearName',
                'selectedPeriod',
                'periodLabel',
                'totalSantri',
                'totalStaff',
                'totalWali',
                'totalKelas',
                'schoolData',
                'genderRatio',
                'paymentChannelRatio',
                'biSummary',
                'billTrendChart',
                'inflowTrendChart',
                'recentPayments',
                'loginActivity',
                'recentStaffLogins',
                'recentStaffActivities',
                'upcomingSchedules',
                'anomalySummary',
                'topAnomalies',
                'allAnomalies'
            ));
        } catch (\Exception $e) {
            Log::error('Dashboard BI Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return view('admins.dashboard.index', [
                'isOutletUser' => false,
                'isSuperAdmin' => false,
                'schools' => collect([]),
                'academicYears' => collect([]),
                'selectedSchoolId' => null,
                'selectedAcademicYearId' => 'all',
                'activeAcademicYearName' => '-',
                'selectedPeriod' => 'bulan_ini',
                'periodLabel' => 'Bulan Ini',
                'totalSantri' => 0,
                'totalStaff' => 0,
                'totalWali' => 0,
                'totalKelas' => 0,
                'schoolData' => collect([]),
                'genderRatio' => ['l' => 0, 'p' => 0],
                'paymentChannelRatio' => [
                    'app_amount' => 0,
                    'direct_amount' => 0,
                    'app_count' => 0,
                    'direct_count' => 0,
                    'app_pct' => 0,
                    'direct_pct' => 0,
                ],
                'biSummary' => [
                    'target_pembayaran' => 0,
                    'realisasi_pembayaran' => 0,
                    'sisa_tunggakan' => 0,
                    'realisasi_percentage' => 0,
                    'total_bill_items' => 0,
                    'paid_bill_items' => 0,
                    'partial_bill_items' => 0,
                    'unpaid_bill_items' => 0,
                    'pembayaran_hari_ini' => 0,
                    'pembayaran_bulan_ini' => 0,
                    'saldo_masuk_hari_ini' => 0,
                    'saldo_masuk_bulan_ini' => 0,
                    'saldo_masuk_12_bulan' => 0,
                    'saldo_mengendap' => 0,
                    'tabungan_masuk_bulan_ini' => 0,
                    'tabungan_mengendap' => 0,
                ],
                'billTrendChart' => [
                    'labels' => ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                    'target' => array_fill(0, 12, 0),
                    'realisasi' => array_fill(0, 12, 0),
                    'tunggakan' => array_fill(0, 12, 0),
                    'percentage' => array_fill(0, 12, 0),
                ],
                'inflowTrendChart' => [
                    'daily_30' => ['labels' => [], 'saldo_in' => [], 'bill_paid' => [], 'saving_in' => []],
                    'monthly_12' => ['labels' => [], 'saldo_in' => [], 'bill_paid' => [], 'saving_in' => []],
                ],
                'recentPayments' => collect([]),
                'loginActivity' => [
                    'staff_online_count' => 0, 'staff_online_percentage' => 0,
                    'staff_count' => 0, 'staff_7d' => 0, 'staff_total' => 0, 'staff_percentage' => 0, 'staff_7d_percentage' => 0,
                    'staff_activity_today' => 0, 'staff_activity_7d' => 0,
                    'wali_count' => 0, 'wali_7d' => 0, 'wali_total' => 0, 'wali_percentage' => 0, 'wali_7d_percentage' => 0,
                ],
                'recentStaffLogins' => collect([]),
                'recentStaffActivities' => collect([]),
                'upcomingSchedules' => collect([]),
                'anomalySummary' => [
                    'total_santri' => 0,
                    'kritis_count' => 0,
                    'waspada_count' => 0,
                    'perhatian_count' => 0,
                    'total_tunggakan' => 0,
                    'total_topup_30d' => 0,
                    'total_belanja_30d' => 0,
                    'rasio_overall' => 0,
                ],
                'topAnomalies' => collect([]),
                'allAnomalies' => collect([]),
            ])->withErrors(['error' => 'Gagal memuat data dashboard.']);
        }
    }
}
