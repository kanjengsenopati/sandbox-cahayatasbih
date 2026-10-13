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

            $excludedBillTypeIds = $this->getExcludedBillTypeIds();
            $cacheKey = 'bi_dashboard_v5_' . md5(json_encode([
                'schools' => $scopedSchoolIds,
                'ay' => $selectedAcademicYearId,
                'date' => Carbon::today()->toDateString(),
            ]));

            // 2. Cached BI Analytics (5-minute TTL for heavy aggregations)
            $biCachedData = Cache::remember($cacheKey, 300, function () use ($scopedSchoolIds, $selectedAcademicYearId, $excludedBillTypeIds) {
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

                $biSummary = [
                    'target_pembayaran' => $targetPembayaran,
                    'realisasi_pembayaran' => $realisasiPembayaran,
                    'sisa_tunggakan' => $sisaTunggakan,
                    'realisasi_percentage' => $realisasiPercentage,
                    'total_bill_items' => (int) ($billTotals->total_bills ?? 0),
                    'paid_bill_items' => (int) ($billTotals->paid_bills ?? 0),
                    'partial_bill_items' => (int) ($billTotals->partial_bills ?? 0),
                    'unpaid_bill_items' => (int) ($billTotals->unpaid_bills ?? 0),
                    'pembayaran_hari_ini' => (int) ($billTxDailyRows[$todayKey] ?? 0),
                    'pembayaran_bulan_ini' => (int) ($billTxMonthlyMap[$thisMonthKey] ?? 0),
                    'saldo_masuk_hari_ini' => (int) ($saldoDailyRows[$todayKey] ?? 0),
                    'saldo_masuk_bulan_ini' => (int) ($saldoMonthlyMap[$thisMonthKey] ?? 0),
                    'saldo_masuk_12_bulan' => array_sum($monthly12['saldo_in']),
                    'saldo_mengendap' => $saldoMengendap,
                    'tabungan_masuk_bulan_ini' => (int) ($savingMonthlyMap[$thisMonthKey] ?? 0),
                    'tabungan_mengendap' => $tabunganMengendap,
                ];

                $upcomingSchedules = Schedule::with('school')
                    ->whereDate('date', '>=', Carbon::today())
                    ->orderBy('date', 'asc')
                    ->limit(5)
                    ->get();

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
                    'upcomingSchedules'
                );
            });

            extract($biCachedData);

            // 3. Real-Time Query: 10 Aktivitas Pembayaran Terkini (Live)
            $recentPayments = Transaction::where('status', Transaction::STATUS_PAID)
                ->with([
                    'student:id,name,nis,classroom_id,school_id,avatar',
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
                'upcomingSchedules'
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
            ])->withErrors(['error' => 'Gagal memuat data dashboard.']);
        }
    }
}
