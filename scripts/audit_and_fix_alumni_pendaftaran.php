<?php

// Script: audit_and_fix_alumni_pendaftaran.php
// Jalankan di root Laravel: php scripts/audit_and_fix_alumni_pendaftaran.php

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Force array cache driver for CLI script to avoid PhpRedisConnector error
config(['cache.default' => 'array']);

use App\Models\Student;
use App\Models\Classroom;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Bill;
use App\Models\BillType;
use App\Models\PaymentRate;
use App\Models\PaymentRateItem;
use App\Models\StudentClassroomHistory;
use Illuminate\Support\Facades\DB;

echo "=================================================================\n";
echo "   AUDIT & PERBAIKAN TAGIHAN PENDAFTARAN ALUMNI (SMP -> MA)     \n";
echo "=================================================================\n\n";

// Cari santri Ramiza
$ramizas = Student::where('name', 'like', '%Ramiza%')->get();
if ($ramizas->isEmpty()) {
    echo "[-] Santri dengan nama 'Ramiza' TIDAK DITEMUKAN di database.\n";
    exit(1);
}

// Academic Years
$ay2627 = AcademicYear::where('name', 'like', '%2026%')->first();
$ay2526 = AcademicYear::where('name', 'like', '%2025%')->first();
$ay2425 = AcademicYear::where('name', 'like', '%2024%')->first();

// Cari Master Tarif Pendaftaran MA
$maRates = PaymentRate::whereHas('billType', function($q) {
    $q->where('name', 'like', '%PENDAFTARAN%')->orWhere('name', 'like', '%DAFTAR%');
})->with('paymentRateItems', 'billType')->get();

$alumniRate = null;
$nonAlumniRate = null;

foreach ($maRates as $rate) {
    $itemNominal = $rate->paymentRateItems->sum('amount');
    if ($rate->alumni_status === 'ALUMNI_SMP_MA' || str_contains(strtoupper($rate->name), 'ALUMNI') || $itemNominal == 1000000) {
        $alumniRate = $rate;
    }
    if ($rate->alumni_status === 'NON_ALUMNI' || str_contains(strtoupper($rate->name), 'NON') || $itemNominal == 5000000) {
        $nonAlumniRate = $rate;
    }
}

echo "1. MASTER TARIF PENDAFTARAN MA:\n";
foreach ($maRates as $r) {
    $nom = $r->paymentRateItems->sum('amount');
    echo " - Rate ID: {$r->id} | {$r->name} | Alumni: " . ($r->alumni_status ?? 'ALL') . " | Nominal: Rp " . number_format($nom, 0, ',', '.') . "\n";
}
if ($alumniRate) {
    echo " -> Terdeteksi Tarif Alumni: {$alumniRate->name} (Rate ID: {$alumniRate->id})\n";
}
echo "\n-----------------------------------------------------------------\n";

// Kelas SMP untuk perbaikan histori jika anomali
$smpGrade9 = Classroom::whereHas('school', fn($q) => $q->where('name', 'like', '%SMP%'))
    ->where('name', 'like', '9%')
    ->first();
if (!$smpGrade9) {
    $smpGrade9 = Classroom::whereHas('school', fn($q) => $q->where('name', 'like', '%SMP%'))->first();
}

$smpGrade8 = Classroom::whereHas('school', fn($q) => $q->where('name', 'like', '%SMP%'))
    ->where('name', 'like', '8%')
    ->first() ?? $smpGrade9;

foreach ($ramizas as $ramiza) {
    echo "2. DATA SANTRI: {$ramiza->name} (ID: {$ramiza->id})\n";
    echo "   NIS            : " . ($ramiza->nis ?? '-') . "\n";
    echo "   Sekolah        : " . ($ramiza->classroom?->school?->name ?? '-') . "\n";
    echo "   Kelas Saat Ini : " . ($ramiza->classroom?->name ?? '-') . "\n";
    echo "   Status Alumni  : " . ($ramiza->isAlumniSmpMa() ? 'ALUMNI SMP' : 'BUKAN ALUMNI') . "\n\n";

    echo "   Histori Kelas:\n";
    $histories = StudentClassroomHistory::where('student_id', $ramiza->id)->with('classroom.school', 'academicYear')->get();
    foreach ($histories as $h) {
        $schName = $h->classroom?->school?->name ?? '-';
        $clsName = $h->classroom?->name ?? '-';
        $ayName = $h->academicYear?->name ?? '-';
        echo "   - TA {$ayName}: {$clsName} ({$schName})\n";
    }

    echo "\n   Tagihan Pendaftaran Saat Ini:\n";
    $pendaftaranBills = Bill::where('student_id', $ramiza->id)
        ->whereHas('billType', function($q) {
            $q->where('name', 'like', '%PENDAFTARAN%')->orWhere('name', 'like', '%DAFTAR%');
        })
        ->get();

    foreach ($pendaftaranBills as $b) {
        $btName = $b->billType?->name ?? '-';
        echo "   - Bill ID      : {$b->id}\n";
        echo "     Nama Tagihan : {$btName}\n";
        echo "     Nominal      : Rp " . number_format($b->amount, 0, ',', '.') . "\n";
        echo "     Sudah Dibayar: Rp " . number_format($b->paid_amount, 0, ',', '.') . "\n";
        echo "     Kekurangan   : Rp " . number_format($b->amount - $b->paid_amount, 0, ',', '.') . "\n";
        echo "     Status       : {$b->status}\n";
    }
}

echo "\n-----------------------------------------------------------------\n";

$isFixMode = in_array('--fix', $argv);

if (!$isFixMode) {
    echo "\n[INFO] Jalankan perintah di bawah ini untuk mengeksekusi perbaikan:\n";
    echo "php scripts/audit_and_fix_alumni_pendaftaran.php --fix\n\n";
    exit(0);
}

echo "\n>>> SEDANG MEMPROSES PERBAIKAN... <<<\n";
DB::beginTransaction();
try {
    foreach ($ramizas as $ramiza) {
        // Cek anomali histori: jika 2025/2026 atau 2024/2025 dicatat di kelas MA (misal 10C)
        // Cari kelas SMP asli dari riwayat tagihan lama
        $oldSmpClassId = DB::table('bills')
            ->join('classrooms', 'bills.classroom_id', '=', 'classrooms.id')
            ->join('schools', 'classrooms.school_id', '=', 'schools.id')
            ->where('bills.student_id', $ramiza->id)
            ->where('schools.name', 'like', '%SMP%')
            ->value('classrooms.id');

        $targetSmpClassId = $oldSmpClassId ?? ($smpGrade9?->id ?? null);

        if ($ay2526 && $targetSmpClassId) {
            $h2526 = StudentClassroomHistory::where('student_id', $ramiza->id)
                ->where('academic_year_id', $ay2526->id)
                ->first();

            if ($h2526) {
                $h2526->update(['classroom_id' => $targetSmpClassId]);
                echo "[FIX] Memperbarui histori TA 2025/2026 {$ramiza->name} ke Kelas SMP.\n";
            } else {
                StudentClassroomHistory::create([
                    'student_id' => $ramiza->id,
                    'academic_year_id' => $ay2526->id,
                    'classroom_id' => $targetSmpClassId,
                ]);
                echo "[FIX] Menambahkan histori TA 2025/2026 {$ramiza->name} ke Kelas SMP.\n";
            }
        }

        // Hapus anomali jika 2024/2025 juga tercatat di kelas 10
        if ($ay2425) {
            $h2425 = StudentClassroomHistory::where('student_id', $ramiza->id)
                ->where('academic_year_id', $ay2425->id)
                ->first();
            if ($h2425) {
                $h24Class = Classroom::with('school')->find($h2425->classroom_id);
                if ($h24Class && str_contains(strtoupper($h24Class->school->name ?? ''), 'MA')) {
                    if ($smpGrade8) {
                        $h2425->update(['classroom_id' => $smpGrade8->id]);
                        echo "[FIX] Memperbarui histori TA 2024/2025 ke Kelas SMP.\n";
                    }
                }
            }
        }

        // Perbaiki Tagihan Pendaftaran
        $pendaftaranBills = Bill::where('student_id', $ramiza->id)
            ->whereHas('billType', function($q) {
                $q->where('name', 'like', '%PENDAFTARAN%')->orWhere('name', 'like', '%DAFTAR%');
            })
            ->get();

        $alumniRateItemId = $alumniRate?->paymentRateItems?->first()?->id ?? null;

        foreach ($pendaftaranBills as $b) {
            $oldAmount = $b->amount;
            $b->amount = 1000000;
            if ($alumniRateItemId) {
                $b->payment_rate_item_id = $alumniRateItemId;
            }

            if ($b->paid_amount >= 1000000) {
                $b->status = Bill::STATUS_PAID;
            } elseif ($b->paid_amount > 0) {
                $b->status = Bill::STATUS_PARTIAL;
            } else {
                $b->status = Bill::STATUS_UNPAID;
            }

            $b->save();
            echo "[FIX] Tagihan Pendaftaran (Bill ID: {$b->id}) berhasil diubah dari Rp " . number_format($oldAmount, 0, ',', '.') . " menjadi Rp " . number_format($b->amount, 0, ',', '.') . " (Status: {$b->status}).\n";
        }
    }

    DB::commit();
    echo "\n=================================================================\n";
    echo "[SUKSES] Perbaikan berhasil dieksekusi 100%!\n";
    echo "Status alumni Ramiza Suhaimi kini terdaftar sebagai Alumni SMP,\n";
    echo "dan tagihan pendaftarannya telah menjadi Rp 1.000.000.\n";
    echo "=================================================================\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n[ERROR] Terjadi kegagalan: " . $e->getMessage() . "\n";
}
