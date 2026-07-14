<?php

/**
 * Diagnostic Script for Wali Santri & Perwalian
 * Run via terminal: php diagnose_perwalian.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Student;
use App\Models\PaymentRate;
use App\Models\Classroom;

echo "=========================================================\n";
echo " DIAGNOSTIC REPORT: WALI SANTRI & DATA PERWALIAN\n";
echo "=========================================================\n\n";

// 1. Check parent users by status
$statuses = ['NON_JAMAAH', 'MUKIMIN', 'JAMAAH'];
echo "1. STATUS WALI SANTRI DI DATABASE:\n";
foreach ($statuses as $status) {
    $count = User::where('jamaah_status', $status)->count();
    echo "   - Status '{$status}': {$count} Wali Santri\n";
}
echo "\n";

// 2. Check Wali Santri Non Jamaah & their Students
echo "2. DAFTAR WALI SANTRI 'NON_JAMAAH' DAN SANTRI DI BAWAHNYA:\n";
$nonJamaahParents = User::where('jamaah_status', 'NON_JAMAAH')->get();
if ($nonJamaahParents->isEmpty()) {
    echo "   (Tidak ada Wali Santri dengan status NON_JAMAAH)\n";
} else {
    foreach ($nonJamaahParents as $parent) {
        $students = Student::where('user_id', $parent->id)->get();
        echo "   * Wali: {$parent->name} (Telp: {$parent->phone})\n";
        if ($students->isEmpty()) {
            echo "     -> [PERINGATAN] Tidak memiliki santri di bawah perwaliannya!\n";
        } else {
            foreach ($students as $student) {
                $class = $student->classroom->name ?? '-';
                echo "     -> Santri: {$student->name} (NIS: {$student->nis}, Kelas: {$class}, Status: {$student->status})\n";
            }
        }
    }
}
echo "\n";

// 3. Check Wali Santri Mukimin & their Students
echo "3. DAFTAR WALI SANTRI 'MUKIMIN' DAN SANTRI DI BAWAHNYA:\n";
$mukiminParents = User::where('jamaah_status', 'MUKIMIN')->get();
if ($mukiminParents->isEmpty()) {
    echo "   (Tidak ada Wali Santri dengan status MUKIMIN)\n";
} else {
    foreach ($mukiminParents as $parent) {
        $students = Student::where('user_id', $parent->id)->get();
        echo "   * Wali: {$parent->name} (Telp: {$parent->phone})\n";
        if ($students->isEmpty()) {
            echo "     -> [PERINGATAN] Tidak memiliki santri di bawah perwaliannya!\n";
        } else {
            foreach ($students as $student) {
                $class = $student->classroom->name ?? '-';
                echo "     -> Santri: {$student->name} (NIS: {$student->nis}, Kelas: {$class}, Status: {$student->status})\n";
            }
        }
    }
}
echo "\n";

// 4. Check Students with no parents (orphaned)
echo "4. SANTRI YANG BELUM DIHUBUNGKAN KE WALI SANTRI (user_id is NULL):\n";
$orphans = Student::whereNull('user_id')->get();
if ($orphans->isEmpty()) {
    echo "   (Semua santri sudah terhubung ke Wali Santri)\n";
} else {
    echo "   Total: " . $orphans->count() . " Santri\n";
    foreach ($orphans as $student) {
        $class = $student->classroom->name ?? '-';
        echo "   - {$student->name} (NIS: {$student->nis}, Kelas: {$class})\n";
    }
}
echo "\n";

// 5. Payment Rate Configuration check
echo "5. TARGRET & MATCHING KELAS PADA SETTINGAN TARIF TAGIHAN (PAYMENT RATES):\n";
$rates = PaymentRate::with('paymentRateClassrooms.classroom')->get();
foreach ($rates as $rate) {
    $classrooms = $rate->paymentRateClassrooms->pluck('classroom.name')->filter()->toArray();
    $classroomsList = count($classrooms) > 0 ? implode(', ', $classrooms) : 'Semua';
    echo "   - Rate ID: {$rate->id}\n";
    echo "     * Kelas Target: {$classroomsList}\n";
    echo "     * Filter Status Wali: " . ($rate->jamaah_status ?? 'Semua') . "\n";
    
    // Simulate count
    $query = Student::query()->where('status', 'ACTIVE');
    if ($rate->type === PaymentRate::TYPE_REGULAR) {
        $classroomIds = $rate->paymentRateClassrooms->pluck('classroom_id')->toArray();
        $query->whereIn('classroom_id', $classroomIds);
    } else {
        $studentIds = $rate->paymentRateStudents->pluck('student_id')->toArray();
        $query->whereIn('id', $studentIds);
    }
    if ($rate->jamaah_status) {
        $query->whereHas('user', function ($userQ) use ($rate) {
            $userQ->whereIn('jamaah_status', array_map('trim', explode(',', $rate->jamaah_status)));
        });
    }
    
    echo "     * Jumlah Santri yang Cocok di Database: " . $query->count() . " Santri\n";
}
echo "\n=========================================================\n";
