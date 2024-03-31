<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Client;
use App\Models\Article;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\HistoryDownload;
use Yajra\DataTables\DataTables;
use App\Models\WhiteBlowingSystem;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Jobs\SendToWhatsappNotificationJob;
use App\Models\ApplicationSetting;
use App\Models\School;
use App\Services\NotificationService;
use App\Services\SendNotifWaService;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        try {
            $data = Transaction::where('status', Transaction::STATUS_PAID)->where('type', Transaction::TYPE_BILL)->latest()->first();
            // $message = SendNotifWaService::sendPaymentBillNotification($data);
            // SendToWhatsappNotificationJob::dispatch($data->student->user->phone, $message);
            $title = 'Hallo Kak';
            $message = 'Ini adalah notifikasi';
            $users = User::where('name', 'like', '%dian%')->first();
            NotificationService::sendTo($title, $message, $users, $data);
        } catch (\Exception $e) {
            dd($e->getMessage());
            Log::error('Error sending notification: ' . $e->getMessage());
        }
        $total_students = Student::count();
        $total_classes = Classroom::count();
        $total_schools = School::count();
        $data = [
            'total_students' => $total_students,
            'total_classes' => $total_classes,
            'total_schools' => $total_schools,
        ];
        // hitung total pemasukkan hari ini, bulan ini, tahun ini
        $transactions = Transaction::where('status', Transaction::STATUS_PAID)->get();

        $today = $transactions->where('created_at', '>=', now()->startOfDay())
            ->where('created_at', '<=', now()->endOfDay());
        $total_income_today = $today->sum('pay_amount');

        $month = $transactions->where('created_at', '>=', now()->startOfMonth())
            ->where('created_at', '<=', now()->endOfMonth());
        $total_income_month = $month->sum('pay_amount');

        $year = $transactions->where('created_at', '>=', now()->startOfYear())
            ->where('created_at', '<=', now()->endOfYear());
        $total_income_year = $year->sum('pay_amount');

        $total = $transactions->sum('pay_amount');

        $incomes = [
            'today' => $total_income_today,
            'month' => $total_income_month,
            'year' => $total_income_year,
            'total' => $total,
        ];

        return view('admins.dashboard.index', compact('data', 'incomes'));
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
