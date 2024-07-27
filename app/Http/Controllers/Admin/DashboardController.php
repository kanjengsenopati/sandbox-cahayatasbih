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
        // $students = Student::all();

        // foreach ($students as $student) {
        //     $newBarcode = $this->generateRandomNumber();
        //     $student->barcode = $newBarcode;
        //     $student->save();
        // }

        // $setting = ApplicationSetting::first();
        // $targetMonth = $setting->target_month;
        // $targetYear = $setting->target_year;
        $total_parents = User::where('is_active', 1)->count();
        $total_students = Student::count();
        $data = [
            'total_parents' => $total_parents,
            'total_students' => $total_students,
        ];
        // hitung total pemasukkan hari ini, bulan ini, tahun ini



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
