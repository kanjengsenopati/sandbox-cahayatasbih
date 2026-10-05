<?php

// Script: audit_and_fix_alumni_pendaftaran.php
// Jalankan di root Laravel: php audit_and_fix_alumni_pendaftaran.php

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

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
echo "   AUDIT & DIAGNOSA TAGIHAN PENDAFTARAN ALUMNI (SMP -> MA)      \n";
echo "=================================================================\n\n";

// 1. Cari Santri Ramiza Suhaimi
$ramizas = Student::where('name', 'like', '%Ramiza%')->get();
if ($ramizas->isEmpty()) {
    echo "[-] Santri dengan nama 'Ramiza' TIDAK DITEMUKAN di database.\n";
    exit(1);
}

foreach ($ramizas as $ramiza) {
    echo "[+] Ditemukan Santri: {$ramiza->name} (ID: {$ramiza->id})\n";
    echo "    NIS        : " . ($ramiza->nis ?? '-') . "\n";
    echo "    NISN       : " . ($ramiza->nisn ?? '-') . "\n";
    echo "    Sekolah    : " . ($ramiza->classroom?->school?->name ?? $ramiza->school?->name ?? '-') . "\n";
    echo "    Kelas Saat : " . ($ramiza->classroom?->name ?? '-') . "\n";
    echo "    isAlumniSmpMa() : " . ($ramiza->isAlumniSmpMa() ? 'TRUE (Alumni SMP)' : 'FALSE (Bukan Alumni)') . "\n";

    echo "    Histori Kelas Terdata:\n";
    $histories = StudentClassroomHistory::where('student_id', $ramiza->id)->with('classroom.school', 'academicYear')->get();
    if ($histories->isEmpty()) {
        echo "      (Tidak ada riwayat kelas di student_classroom_histories)\n";
    } else {
        foreach ($histories as $h) {
            $schName = $h->classroom?->school?->name ?? '-';
            $clsName = $h->classroom?->name ?? '-';
            $ayName = $h->academicYear?->name ?? '-';
            echo "      - TA {$ayName}: {$clsName} ({$schName})\n";
        }
    }

    echo "\n    Daftar Tagihan Pendaftaran/Daftar Ulang:\n";
    $pendaftaranBills = Bill::where('student_id', $ramiza->id)
        ->whereHas('billType', function($q) {
            $q->where('name', 'like', '%PENDAFTARAN%')->orWhere('name', 'like', '%DAFTAR%');
        })
        ->with('billType', 'paymentRateItem.paymentRate')
        ->get();

    if ($pendaftaranBills->isEmpty()) {
        echo "      (Tidak ada tagihan pendaftaran)\n";
    } else {
        foreach ($pendaftaranBills as $b) {
            $prName = $b->paymentRateItem?->paymentRate?->name ?? 'Manual/Tidak terhubung';
            $prAlumni = $b->paymentRateItem?->paymentRate?->alumni_status ?? 'ALL';
            echo "      - Bill ID: {$b->id}\n";
            echo "        Tipe Tagihan  : {$b->billType?->name}\n";
            echo "        Nominal Tagihan: Rp " . number_format($b->amount, 0, ',', '.') . "\n";
            echo "        Sudah Dibayar  : Rp " . number_format($b->paid_amount, 0, ',', '.') . "\n";
            echo "        Kekurangan     : Rp " . number_format($b->amount - $b->paid_amount, 0, ',', '.') . "\n";
            echo "        Status         : {$b->status}\n";
            echo "        Tarif Terpasang: {$prName} (Alumni Status: {$prAlumni})\n";
        }
    }
    echo "-----------------------------------------------------------------\n";
}

echo "\n2. MASTER TARIF PENDAFTARAN / DAFTAR ULANG DI MA:\n";
$maRates = PaymentRate::whereHas('school', function($q) {
    $q->where('name', 'like', '%MA%')->orWhere('name', 'like', '%ALIYAH%');
})->whereHas('billType', function($q) {
    $q->where('name', 'like', '%PENDAFTARAN%')->orWhere('name', 'like', '%DAFTAR%');
})->with('billType', 'paymentRateItems')->get();

if ($maRates->isEmpty()) {
    // Cari tanpa filter sekolah khusus
    $maRates = PaymentRate::whereHas('billType', function($q) {
        $q->where('name', 'like', '%PENDAFTARAN%')->orWhere('name', 'like', '%DAFTAR%');
    })->with('billType', 'paymentRateItems', 'school')->get();
}

$alumniRate = null;
$nonAlumniRate = null;

foreach ($maRates as $rate) {
    $itemNominal = $rate->paymentRateItems->sum('amount');
    echo " - Rate ID: {$rate->id} | {$rate->name}\n";
    echo "   Sekolah      : " . ($rate->school?->name ?? 'Semua') . "\n";
    echo "   Alumni Status: " . ($rate->alumni_status ?? 'SEMUA (NON-FILTER)') . "\n";
    echo "   Jamaah Status: " . ($rate->jamaah_status ?? 'SEMUA') . "\n";
    echo "   Total Nominal: Rp " . number_format($itemNominal, 0, ',', '.') . "\n";

    if ($rate->alumni_status === 'ALUMNI_SMP_MA' || str_contains(strtoupper($rate->name), 'ALUMNI') || $itemNominal == 1000000) {
        $alumniRate = $rate;
    }
    if ($rate->alumni_status === 'NON_ALUMNI' || str_contains(strtoupper($rate->name), 'NON') || $itemNominal == 5000000) {
        $nonAlumniRate = $rate;
    }
}

echo "\n3. CEK KELAS 10 MA LAINNYA:\n";
$grade10Classrooms = Classroom::whereHas('school', function($q) {
    $q->where('name', 'like', '%MA%')->orWhere('name', 'like', '%ALIYAH%');
})->where('name', 'like', '10%')->pluck('id');

$grade10Students = Student::whereIn('classroom_id', $grade10Classrooms)->get();
echo "Total Siswa di Kelas 10 MA: " . $grade10Students->count() . " siswa\n";

$alumniStudents = [];
$nonAlumniWith5M = [];

foreach ($grade10Students as $st) {
    $isAlumni = $st->isAlumniSmpMa();
    if ($isAlumni) {
        $alumniStudents[] = $st;
    }
}
echo "Jumlah Siswa terdeteksi Alumni SMP via Histori: " . count($alumniStudents) . " siswa\n";

echo "\n=================================================================\n";
echo "   REKOMENDASI / EKSEKUSI PERBAIKAN                             \n";
echo "=================================================================\n";
echo "Untuk menerapkan perbaikan nominal Ramiza Suhaimi (dan alumni lain),\n";
echo "jalankan script ini dengan flag: php audit_and_fix_alumni_pendaftaran.php --fix\n";

if (in_array('--fix', $argv)) {
    echo "\n>>> MEMPROSES PERBAIKAN TAGIHAN... <<<\n";
    DB::beginTransaction();
    try {
        foreach ($ramizas as $ramiza) {
            // 1. Pastikan Ramiza memiliki riwayat SMP jika belum ada
            if (!$ramiza->isAlumniSmpMa()) {
                // Cari kelas SMP terakhir (misal kelas 9)
                $smpClass = Classroom::whereHas('school', function($q) {
                    $q->where('name', 'like', '%SMP%');
                })->first();

                $prevAy = AcademicYear::where('name', 'like', '%2025%')->first() 
                    ?? AcademicYear::orderBy('id', 'desc')->skip(1)->first();

                if ($smpClass && $prevAy) {
                    StudentClassroomHistory::firstOrCreate([
                        'student_id' => $ramiza->id,
                        'academic_year_id' => $prevAy->id,
                    ], [
                        'classroom_id' => $smpClass->id,
                    ]);
                    echo "[FIX] Menambahkan riwayat SMP ke student_classroom_histories untuk {$ramiza->name}.\n";
                }
            }

            // 2. Update tagihan pendaftaran Ramiza menjadi Rp 1.000.000
            $billsToFix = Bill::where('student_id', $ramiza->id)
                ->whereHas('billType', function($q) {
                    $q->where('name', 'like', '%PENDAFTARAN%')->orWhere('name', 'like', '%DAFTAR%');
                })
                ->where('amount', 5000000)
                ->get();

            foreach ($billsToFix as $b) {
                $oldAmount = $b->amount;
                $b->amount = 1000000;
                
                // Jika ada payment rate alumni, hubungkan
                if ($alumniRate && $alumniRate->paymentRateItems->isNotEmpty()) {
                    $b->payment_rate_item_id = $alumniRate->paymentRateItems->first()->id;
                }

                if ($b->paid_amount >= 1000000) {
                    $b->status = Bill::STATUS_PAID;
                } elseif ($b->paid_amount > 0) {
                    $b->status = Bill::STATUS_PARTIAL;
                } else {
                    $b->status = Bill::STATUS_UNPAID;
                }
                $b->save();

                echo "[FIX] Tagihan {$b->billType?->name} (Bill ID: {$b->id}) {$ramiza->name} berhasil diubah dari Rp " . number_format($oldAmount, 0, ',', '.') . " menjadi Rp " . number_format($b->amount, 0, ',', '.') . " (Status: {$b->status}).\n";
            }
        }

        DB::commit();
        echo "\n[BERHASIL] Semua perubahan telah disimpan ke database!\n";
    } catch (\Exception $e) {
        DB::rollBack();
        echo "\n[ERROR] Gagal memperbaiki tagihan: " . $e->getMessage() . "\n";
    }
}
