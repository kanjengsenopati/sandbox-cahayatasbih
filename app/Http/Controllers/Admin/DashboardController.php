<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendToPushNotificationJob;
use App\Jobs\SendToWhatsappNotificationJob;
use App\Jobs\SendUnpaidBillNotificationJob;
use App\Models\Admin;
use App\Models\ApplicationSetting;
use App\Models\Article;
use App\Models\Bill;
use App\Models\Classroom;
use App\Models\HistoryDownload;
use App\Models\PointOfSaleTransaction;
use App\Models\PpdbRegistration;
use App\Models\School;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\StudentBillNotification;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WhiteBlowingSystem;
use App\Services\NotificationService;
use App\Services\SendNotifWaService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        try {
            // 1. Key Metrics
            $totalSantri = Student::where('status', Student::STATUS_ACTIVE)->count();
            // Assuming Admin has is_active column or counting all
            $totalStaff = Admin::where('is_active', 1)->count();
            $totalWali = User::where('is_active', 1)->count();
            $totalKelas = Classroom::whereNotIn('school_id', ['37ca75d4-4a87-4856-be8e-f78e2672134f', 'ca3d1ef1-a2ec-4a2b-81ce-72a2299e068c'])->count();

            // 2. School Data (Deep Dive)
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

            // 3. Gender Ratio (Chart)
            $totalLaki = Student::where('status', Student::STATUS_ACTIVE)->where('gender', 'L')->count();
            $totalPerempuan = Student::where('status', Student::STATUS_ACTIVE)->where('gender', 'P')->count();
            $genderRatio = [
                'l' => $totalLaki,
                'p' => $totalPerempuan,
            ];

            // 4. Activity (Login Today)
            $today = Carbon::today();
            // Using last_login_at for Admin (as per prompt) and last_login for User (as per Model)
            // Note: Ensure columns exist in DB.
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

            // 5. Agenda & Schedule
            $upcomingSchedules = Schedule::with('school')
                ->whereDate('date', '>=', Carbon::today())
                ->orderBy('date', 'asc')
                ->limit(5)
                ->get();

            return view('admins.dashboard.index', compact(
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

    public function generateRandomNumber()
    {
        return substr(str_shuffle(str_repeat('0123456789', 30)), 0, 30);
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
