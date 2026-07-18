<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Bill;
use App\Models\Classroom;
use App\Models\PointOfSaleTransaction;
use App\Models\School;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    // Force deployment trigger comment
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = Auth::user();
            if ($user) {
                if ($user->hasRole('Kasir Koperasi')) {
                    return redirect('/order-item?mode=kantin');
                }
                if ($user->hasRole('Kasir Karyawan Outlet') || $user->hasRole('Kasir')) {
                    return redirect('/order-item?mode=outlet');
                }
            }
            $isOutletUser = $user->hasRole('Kasir') || $user->hasRole('Karyawan Outlet ( Non Kasir )') || request()->input('mode') === 'outlet';

            if ($isOutletUser) {
                $outletIds = $user->getOutletIds();
                
                // 1. Outlet Key Metrics
                // Total Penjualan Hari Ini
                $salesToday = PointOfSaleTransaction::whereIn('outlet_id', $outletIds)
                    ->where('status', 'SUCCESS')
                    ->whereDate('created_at', Carbon::today())
                    ->sum('pay_amount');

                // Total Transaksi Hari Ini
                $transactionsToday = PointOfSaleTransaction::whereIn('outlet_id', $outletIds)
                    ->where('status', 'SUCCESS')
                    ->whereDate('created_at', Carbon::today())
                    ->count();

                // Total Item Barang
                $totalBarang = \App\Models\Item::whereIn('outlet_id', $outletIds)->count();

                // Total Karyawan Outlet
                $totalKaryawan = \App\Models\Karyawan::whereIn('outlet_id', $outletIds)->count();

                // 2. Transaksi Kasir Terkini (Last 5 transactions)
                $recentTransactions = PointOfSaleTransaction::with(['student', 'admins'])
                    ->whereIn('outlet_id', $outletIds)
                    ->where('status', 'SUCCESS')
                    ->latest()
                    ->limit(5)
                    ->get();

                // 3. Grafik Penjualan 7 Hari Terakhir
                $salesChart = [
                    'labels' => [],
                    'data' => []
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

            // --- Default Academic Dashboard ---
            $totalSantri = Student::where('status', Student::STATUS_ACTIVE)->count();
            $totalStaff = Admin::where('is_active', 1)->count();
            $totalWali = User::where('is_active', 1)->count();
            $totalKelas = Classroom::whereNotIn('school_id', ['37ca75d4-4a87-4856-be8e-f78e2672134f', 'ca3d1ef1-a2ec-4a2b-81ce-72a2299e068c'])->count();

            $schoolData = School::whereNotIn('id', ['37ca75d4-4a87-4856-be8e-f78e2672134f', 'ca3d1ef1-a2ec-4a2b-81ce-72a2299e068c'])->withCount([
                'classroom as total_classes',
                'students as total_students' => function ($query) {
                    $query->where('status', Student::STATUS_ACTIVE);
                },
                'students as count_l' => function ($query) {
                    $query->where('status', Student::STATUS_ACTIVE)->where('gender', 'L');
                },
                'students as count_p' => function ($query) {
                    $query->where('status', Student::STATUS_ACTIVE)->where('gender', 'P');
                },
            ])->get();

            $totalLaki = Student::where('status', Student::STATUS_ACTIVE)->where('gender', 'L')->count();
            $totalPerempuan = Student::where('status', Student::STATUS_ACTIVE)->where('gender', 'P')->count();
            $genderRatio = [
                'l' => $totalLaki,
                'p' => $totalPerempuan,
            ];

            $today = Carbon::today();
            $staffLoginToday = Admin::whereDate('last_login_at', $today)->count();
            $waliLoginToday = User::whereDate('last_login', $today)->count();

            $loginActivity = [
                'staff_count' => $staffLoginToday,
                'staff_total' => $totalStaff,
                'staff_percentage' => $totalStaff > 0 ? round(($staffLoginToday / $totalStaff) * 100) : 0,
                'wali_count' => $waliLoginToday,
                'wali_total' => $totalWali,
                'wali_percentage' => $totalWali > 0 ? round(($waliLoginToday / $totalWali) * 100) : 0,
            ];

            $upcomingSchedules = Schedule::with('school')
                ->whereDate('date', '>=', Carbon::today())
                ->orderBy('date', 'asc')
                ->limit(5)
                ->get();

            return view('admins.dashboard.index', compact(
                'isOutletUser',
                'totalSantri',
                'totalStaff',
                'totalWali',
                'totalKelas',
                'schoolData',
                'genderRatio',
                'loginActivity',
                'upcomingSchedules'
            ));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return view('admins.dashboard.index', [
                'isOutletUser' => false,
                'totalSantri' => 0,
                'totalStaff' => 0,
                'totalWali' => 0,
                'totalKelas' => 0,
                'schoolData' => [],
                'genderRatio' => ['l' => 0, 'p' => 0],
                'loginActivity' => [
                    'staff_count' => 0, 'staff_total' => 0, 'staff_percentage' => 0,
                    'wali_count' => 0, 'wali_total' => 0, 'wali_percentage' => 0
                ],
                'upcomingSchedules' => collect([])
            ])->withErrors(['error' => 'Gagal memuat data dashboard.']);
        }
    }
}
