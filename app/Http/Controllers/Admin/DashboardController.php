<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendToPushNotificationJob;
use App\Jobs\SendToWhatsappNotificationJob;
use App\Jobs\SendUnpaidBillNotificationJob;
use App\Models\ApplicationSetting;
use App\Models\Article;
use App\Models\Bill;
use App\Models\Classroom;
use App\Models\HistoryDownload;
use App\Models\PointOfSaleTransaction;
use App\Models\PpdbRegistration;
use App\Models\School;
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
            // Total active parents (wali santri aktif)
            $totalActiveParents = User::where('is_active', 1)->count();

            // Total students
            $totalStudents = Student::count();

            // Fetch prayer times for Demak using Aladhan API
            $response = Http::get('http://api.aladhan.com/v1/timingsByCity', [
                'city' => 'Demak',
                'country' => 'ID',
                'method' => 5, // Method 5 is the default method in Indonesia
            ]);

            $prayerTimes = [];
            if ($response->successful()) {
                $data = $response->json();
                $prayerTimes = $data['data']['timings'];
            }

            // Prepare data to pass to the view
            $data = [
                'total_parents' => $totalActiveParents,
                'total_students' => $totalStudents,
            ];

            return view('admins.dashboard.index', compact(
                'data',
                'prayerTimes'
            ));
        } catch (\Exception $e) {
            // Handle errors gracefully
            return view('admins.dashboard.index', [
                'data' => [
                    'total_parents' => 0,
                    'total_students' => 0,
                ],
                'prayerTimes' => [],
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
