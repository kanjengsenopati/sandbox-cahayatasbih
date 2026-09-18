<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\ApplicationSetting;
use Illuminate\Console\Application;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\Admin\ApplicationSettingRequest;

class ApplicationSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Pengaturan Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $applicationSetting = ApplicationSetting::first();
        $schools = \App\Models\School::with(['classroom' => fn($q) => $q->orderBy('name')])->orderBy('name')->get();
        return view('admins.application-setting.index', compact('applicationSetting', 'schools'));
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
    public function store(ApplicationSettingRequest $request)
    {
        if (!Auth::user()->can('Edit Pengaturan Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        $data['payment_auto_check'] = $request->has('payment_auto_check') ? true : false;
        $data['allow_pwa_login_wali'] = $request->has('allow_pwa_login_wali') ? true : false;
        $data['allow_pwa_saldo_payment_wali'] = $request->has('allow_pwa_saldo_payment_wali') ? true : false;

        if ($request->hasFile('student_card_image')) {
            $data['student_card_image'] = $this->storeStudentCardImage($request->file('student_card_image'));
        }

        ApplicationSetting::updateOrCreate([], $data);

        // Process school granular settings
        if ($request->has('schools') && is_array($request->schools)) {
            foreach ($request->schools as $schoolId => $schoolData) {
                \App\Models\School::where('id', $schoolId)->update([
                    'allow_pwa_login' => !empty($schoolData['allow_pwa_login']),
                    'show_pwa_saldo' => !empty($schoolData['show_pwa_saldo']),
                    'allow_pwa_saldo_payment' => !empty($schoolData['allow_pwa_saldo_payment']),
                    'is_saldo_limit_active' => !empty($schoolData['is_saldo_limit_active']),
                    'saldo_limit' => isset($schoolData['saldo_limit']) && $schoolData['saldo_limit'] !== '' ? (int)str_replace('.', '', $schoolData['saldo_limit']) : null,
                ]);
            }
        }

        // Process classroom granular settings
        if ($request->has('classrooms') && is_array($request->classrooms)) {
            foreach ($request->classrooms as $classroomId => $classData) {
                $allowLogin = isset($classData['allow_pwa_login']) && $classData['allow_pwa_login'] !== '' ? (bool)$classData['allow_pwa_login'] : null;
                $showSaldo = isset($classData['show_pwa_saldo']) && $classData['show_pwa_saldo'] !== '' ? (bool)$classData['show_pwa_saldo'] : null;
                $allowPayment = isset($classData['allow_pwa_saldo_payment']) && $classData['allow_pwa_saldo_payment'] !== '' ? (bool)$classData['allow_pwa_saldo_payment'] : null;
                $isSaldoLimitActive = isset($classData['is_saldo_limit_active']) && $classData['is_saldo_limit_active'] !== '' ? (bool)$classData['is_saldo_limit_active'] : false;
                $saldoLimit = isset($classData['saldo_limit']) && $classData['saldo_limit'] !== '' ? (int)str_replace('.', '', $classData['saldo_limit']) : null;

                \App\Models\Classroom::where('id', $classroomId)->update([
                    'allow_pwa_login' => $allowLogin,
                    'show_pwa_saldo' => $showSaldo,
                    'allow_pwa_saldo_payment' => $allowPayment,
                    'is_saldo_limit_active' => $isSaldoLimitActive,
                    'saldo_limit' => $saldoLimit,
                ]);
            }
        }

        return redirect()->route('application-setting.index')->with('success', 'Berhasil menyimpan seluruh pengaturan aplikasi');
    }

    private function storeStudentCardImage($file)
    {
        $imagePath = $file->store('images/student-card', 'public');

        $previousImage = ApplicationSetting::value('student_card_image');
        if ($previousImage) {
            $oldPath = str_replace('storage/', '', $previousImage);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
        }

        return 'storage/' . $imagePath;
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
