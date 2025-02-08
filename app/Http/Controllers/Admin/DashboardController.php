<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Bill;
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
use App\Models\StudentBillNotification;
use App\Jobs\SendToWhatsappNotificationJob;
use App\Jobs\SendUnpaidBillNotificationJob;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $students = Student::whereHas('user', function ($query) {
            $query->where('phone', '08386496199');
        })->get();

        foreach ($students as $student) {
            // Panggil fungsi untuk mendapatkan pesan
            $message = SendNotifWaService::sendAllBillInvoice($student);

            // Periksa apakah $message tidak null
            if ($message !== null) {
                // Kirim notifikasi hanya jika $message ada
                dispatch(new SendToWhatsappNotificationJob($student->user->phone, $message));
                // create log to student bill notification
                StudentBillNotification::updateOrCreate(
                    [
                        'student_id' => $student->id, // Kunci unik untuk mencari entri
                    ],
                    [
                        'message' => $message, // Data yang akan diperbarui atau dibuat
                        'status' => StudentBillNotification::STATUS_SUCCESS,
                        'sent_at' => Carbon::now(),
                    ]
                );
            }
        }
        dd($message);

        $total_parents = User::where('is_active', 1)->count();
        $total_students = Student::count();
        $data = [
            'total_parents' => $total_parents,
            'total_students' => $total_students,
        ];

        return view('admins.dashboard.index', compact(
            'data',
        ));
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
