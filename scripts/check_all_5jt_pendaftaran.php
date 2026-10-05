<?php

// Script: check_all_5jt_pendaftaran.php
// Jalankan di root Laravel: php scripts/check_all_5jt_pendaftaran.php

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Classroom;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Bill;
use App\Models\StudentClassroomHistory;
use Illuminate\Support\Facades\DB;

echo "=================================================================\n";
echo "   AUDIT TAGIHAN PENDAFTARAN 5 JUTA KELAS 10 MA (TA 2026/2027)  \n";
echo "=================================================================\n\n";

// 1. Ambil kelas 10 MA
$maSchool = School::where('name', 'like', '%MA%')->orWhere('name', 'like', '%ALIYAH%')->first();
$grade10Classrooms = Classroom::where('name', 'like', '10%')
    ->when($maSchool, fn($q) => $q->where('school_id', $maSchool->id))
    ->get();

$classIds = $grade10Classrooms->pluck('id')->toArray();

echo "Daftar Rombel Kelas 10 MA:\n";
foreach ($grade10Classrooms as $c) {
    echo " - [{$c->id}] {$c->name} ({$c->school?->name})\n";
}
echo "\n";

// 2. Ambil semua siswa di Kelas 10 MA
$students = Student::whereIn('classroom_id', $classIds)->orderBy('classroom_id')->orderBy('name')->get();
echo "Total Siswa di Kelas 10 MA: " . $students->count() . " siswa\n\n";

// 3. Ambil semua tagihan Pendaftaran untuk siswa-siswa ini
$pendaftaranBills = Bill::whereIn('student_id', $students->pluck('id'))
    ->whereHas('billType', function($q) {
        $q->where('name', 'like', '%PENDAFTARAN%')->orWhere('name', 'like', '%DAFTAR%');
    })
    ->get()
    ->groupBy('student_id');

$alumniWith5M = [];
$nonAlumniWith5M = [];
$studentsWith1M = [];
$studentsWithoutBill = [];

// Cari ID sekolah SMP
$smpSchoolIds = School::where('name', 'like', '%SMP%')->pluck('id')->toArray();

foreach ($students as $st) {
    $stBills = $pendaftaranBills->get($st->id);
    
    // Indikasi Alumni:
    // 1) isAlumniSmpMa() == true
    // 2) Ada student_classroom_histories di sekolah SMP
    // 3) Ada riwayat bills di kelas SMP sebelum 2026-07-01
    // 4) NIS santri terdaftar di SMP tahun ajaran 2025/2026 atau sebelumnya
    $hasSmpHistory = StudentClassroomHistory::where('student_id', $st->id)
        ->whereHas('classroom', fn($q) => $q->whereIn('school_id', $smpSchoolIds))
        ->exists();

    $hasSmpBills = DB::table('bills')
        ->join('classrooms', 'bills.classroom_id', '=', 'classrooms.id')
        ->where('bills.student_id', $st->id)
        ->whereIn('classrooms.school_id', $smpSchoolIds)
        ->exists();

    $isAlumni = $st->isAlumniSmpMa() || $hasSmpHistory || $hasSmpBills;

    if (!$stBills || $stBills->isEmpty()) {
        $studentsWithoutBill[] = [
            'student' => $st,
            'is_alumni' => $isAlumni
        ];
        continue;
    }

    $bill = $stBills->first();
    $amount = (int) $bill->amount;

    if ($amount == 5000000) {
        if ($isAlumni) {
            $alumniWith5M[] = [
                'student' => $st,
                'bill' => $bill,
                'evidence' => $hasSmpHistory ? 'Histori SMP' : ($hasSmpBills ? 'Tagihan SMP Lama' : 'isAlumniSmpMa'),
            ];
        } else {
            $nonAlumniWith5M[] = [
                'student' => $st,
                'bill' => $bill,
            ];
        }
    } elseif ($amount == 1000000) {
        $studentsWith1M[] = [
            'student' => $st,
            'bill' => $bill,
            'is_alumni' => $isAlumni,
        ];
    }
}

echo "=================================================================\n";
echo "HASIL ANALISIS SISWA KELAS 10 MA:\n";
echo "=================================================================\n";
echo "1. Alumni yang SUDAH bertarif 1 Juta : " . count($studentsWith1M) . " siswa\n";
echo "2. Siswa Baru Non-Alumni (Tarif 5 Jt): " . count($nonAlumniWith5M) . " siswa (Wajar)\n";
echo "3. Siswa Belum Ada Tagihan Pendaftaran : " . count($studentsWithoutBill) . " siswa\n\n";

echo "4. TEMUAN KORBAN: ALUMNI SMP TAPI TERKENA TAGIHAN 5 JUTA:\n";
echo "   Total Ditemukan: " . count($alumniWith5M) . " siswa\n";
echo "-----------------------------------------------------------------\n";

if (empty($alumniWith5M)) {
    echo "   [SELAMAT] Tidak ditemukan siswa alumni lain yang terkena 5 juta!\n";
    echo "   Hanya Ramiza Suhaimi yang tadi bermasalah dan sudah berhasil diperbaiki.\n";
} else {
    foreach ($alumniWith5M as $idx => $item) {
        $st = $item['student'];
        $b = $item['bill'];
        $no = $idx + 1;
        echo "   {$no}. [{$st->classroom?->name}] {$st->name} (NIS: {$st->nis})\n";
        echo "      - Bill ID : {$b->id}\n";
        echo "      - Nominal : Rp " . number_format($b->amount, 0, ',', '.') . " | Terbayar: Rp " . number_format($b->paid_amount, 0, ',', '.') . " | Status: {$b->status}\n";
        echo "      - Bukti Alumni: {$item['evidence']}\n\n";
    }
}

echo "=================================================================\n";

if (in_array('--fix', $argv) && !empty($alumniWith5M)) {
    echo "\n>>> MEMPERBAIKI SEMUA ALUMNI YANG SALAH TARIF MENJADI 1 JUTA... <<<\n";
    DB::beginTransaction();
    try {
        foreach ($alumniWith5M as $item) {
            $st = $item['student'];
            $b = $item['bill'];
            
            $b->amount = 1000000;
            if ($b->paid_amount >= 1000000) {
                $b->status = Bill::STATUS_PAID;
            } elseif ($b->paid_amount > 0) {
                $b->status = Bill::STATUS_PARTIAL;
            } else {
                $b->status = Bill::STATUS_UNPAID;
            }
            $b->save();
            echo "[FIXED] {$st->name} diubah menjadi Rp 1.000.000 (Status: {$b->status})\n";
        }
        DB::commit();
        echo "\n[SELESAI] Semua alumni di atas telah disesuaikan menjadi Rp 1.000.000!\n";
    } catch (\Exception $e) {
        DB::rollBack();
        echo "[ERROR] Gagal: " . $e->getMessage() . "\n";
    }
}
