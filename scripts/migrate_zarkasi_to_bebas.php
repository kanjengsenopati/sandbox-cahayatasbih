<?php

// Script: migrate_zarkasi_to_bebas.php
// Jalankan di root aplikasi: php scripts/migrate_zarkasi_to_bebas.php [--fix]

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bill;
use App\Models\BillType;
use App\Models\PaymentRate;
use App\Models\PaymentRateItem;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

echo "=================================================================\n";
echo "   MIGRASI MODEL PEMBAYARAN ZARKASI: BULANAN -> BEBAS (CICILAN) \n";
echo "=================================================================\n\n";

$isFix = in_array('--fix', $argv);

// 1. Cari semua BillType yang bernama Zarkasi
$zarkasiTypes = BillType::where('name', 'like', '%ZARKASI%')->get();

if ($zarkasiTypes->isEmpty()) {
    echo "[-] Tidak ditemukan Jenis Bayar dengan nama 'ZARKASI'.\n";
    exit(1);
}

echo "1. DAFTAR JENIS BAYAR (BILL TYPE) ZARKASI TERDATA:\n";
foreach ($zarkasiTypes as $bt) {
    echo " - ID: {$bt->id}\n";
    echo "   Nama        : {$bt->name}\n";
    echo "   Tipe Saat Ini: {$bt->type} (" . ($bt->type === 'MONTHLY' ? 'Bulanan' : 'Bebas') . ")\n";
    echo "   Input Type   : {$bt->payment_input_type} (" . ($bt->payment_input_type === 'FREE' ? 'Cicilan Bebas' : 'Fix') . ")\n";
    echo "   Tahun Ajaran: " . ($bt->academicYear?->name ?? '-') . "\n";
    echo "   Target Baru : Tipe = OTHER (Bebas), Input = FREE (Cicilan), Nominal = Rp 550.000\n\n";
}

// 2. Analisis Tarif Pembayaran & Tagihan Santri
$totalStudentsAll = 0;
$totalPaidTransactionsAll = 0;
$totalRedundantBillsAll = 0;

foreach ($zarkasiTypes as $bt) {
    echo "-----------------------------------------------------------------\n";
    echo "ANALISIS DATA UNTUK: {$bt->name} (ID: {$bt->id})\n";

    // Master Tarif
    $rates = PaymentRate::where('bill_type_id', $bt->id)->with('paymentRateItems')->get();
    echo " - Ditemukan " . $rates->count() . " Master Tarif Pembayaran.\n";
    foreach ($rates as $r) {
        $sumAmt = $r->paymentRateItems->sum('amount');
        echo "   * Rate ID: {$r->id} | {$r->name} | Total Item: " . $r->paymentRateItems->count() . " item (Total Rp " . number_format($sumAmt, 0, ',', '.') . ")\n";
    }

    // Tagihan Siswa
    $bills = Bill::where('bill_type_id', $bt->id)->get();
    $groupedByStudent = $bills->groupBy('student_id');
    $studentCount = $groupedByStudent->count();
    $totalBillsCount = $bills->count();

    $studentsWithPayment = 0;
    $totalPaidSum = 0;

    foreach ($groupedByStudent as $stId => $stBills) {
        $stPaid = $stBills->sum('paid_amount');
        if ($stPaid > 0) {
            $studentsWithPayment++;
            $totalPaidSum += $stPaid;
        }
    }

    $redundantBillsCount = max(0, $totalBillsCount - $studentCount);

    echo " - Total Santri Memiliki Tagihan : {$studentCount} santri\n";
    echo " - Total Baris Tagihan (Pecahan) : {$totalBillsCount} baris tagihan\n";
    echo " - Santri yang Sudah Menyicil    : {$studentsWithPayment} santri (Total Terbayar: Rp " . number_format($totalPaidSum, 0, ',', '.') . ")\n";
    echo " - Baris Tagihan Bulanan Berlebih: {$redundantBillsCount} baris (akan diringkas menjadi 1 kartu per santri)\n";

    $totalStudentsAll += $studentCount;
    $totalPaidTransactionsAll += $studentsWithPayment;
    $totalRedundantBillsAll += $redundantBillsCount;
}

echo "=================================================================\n";
echo "RINGKASAN KESELURUHAN:\n";
echo "Total Santri Terdampak          : {$totalStudentsAll} santri\n";
echo "Santri Sudah Ada Pembayaran     : {$totalPaidTransactionsAll} santri (PEMBAYARAN AKAN AMAN 100%)\n";
echo "Tagihan Bulanan yang Diringkas  : {$totalRedundantBillsAll} baris\n";
echo "=================================================================\n\n";

if (!$isFix) {
    echo "[MODE SIMULASI / CEK DATA]\n";
    echo "Data di atas aman dan belum diubah.\n";
    echo "Untuk mengeksekusi migrasi menjadi model Bebas (Cicilan) Rp 550.000,\n";
    echo "jalankan:\n";
    echo "php scripts/migrate_zarkasi_to_bebas.php --fix\n\n";
    exit(0);
}

// 3. EKSEKUSI PERBAIKAN
echo ">>> MEMPROSES MIGRASI ZARKASI KE MODEL BEBAS (CICILAN)... <<<\n\n";

DB::beginTransaction();
try {
    foreach ($zarkasiTypes as $bt) {
        echo "[1/3] Mengubah tipe BillType {$bt->name} ke OTHER & FREE...\n";
        $bt->update([
            'type' => BillType::TYPE_OTHER,
            'payment_input_type' => 'FREE',
        ]);

        echo "[2/3] Menyesuaikan master tarif & item nominal Rp 550.000...\n";
        $rates = PaymentRate::where('bill_type_id', $bt->id)->with('paymentRateItems')->get();
        $primaryRateItem = null;

        foreach ($rates as $r) {
            $r->update(['amount' => 550000]);

            $items = $r->paymentRateItems;
            if ($items->isNotEmpty()) {
                // Gunakan item pertama sebagai item utama Rp 550.000
                $primaryItem = $items->first();
                $primaryItem->update([
                    'amount' => 550000,
                    'month' => 0,
                    'year' => $primaryItem->year ?? 2026,
                ]);

                // Hapus item pecahan bulanan lainnya
                if ($items->count() > 1) {
                    $otherItemIds = $items->slice(1)->pluck('id')->toArray();
                    PaymentRateItem::whereIn('id', $otherItemIds)->delete();
                }

                if (!$primaryRateItem) {
                    $primaryRateItem = $primaryItem;
                }
            } else {
                // Buat item baru jika belum ada
                $primaryRateItem = PaymentRateItem::create([
                    'payment_rate_id' => $r->id,
                    'amount' => 550000,
                    'month' => 0,
                    'year' => 2026,
                ]);
            }
        }

        echo "[3/3] Menggabungkan tagihan bulanan santri menjadi 1 kartu tagihan Rp 550.000...\n";
        $bills = Bill::where('bill_type_id', $bt->id)->get();
        $grouped = $bills->groupBy('student_id');

        $processed = 0;
        foreach ($grouped as $studentId => $stBills) {
            $totalPaid = $stBills->sum('paid_amount');
            $allBillIds = $stBills->pluck('id')->toArray();

            // Prioritaskan bill yang memiliki pembayaran atau transaksi sebagai primaryBill
            $primaryBill = $stBills->sortByDesc('paid_amount')->first();
            $otherBillIds = array_diff($allBillIds, [$primaryBill->id]);

            // Pindahkan semua transaction details dari bill lain ke primaryBill
            if (!empty($otherBillIds)) {
                TransactionDetail::whereIn('bill_id', $otherBillIds)->update([
                    'bill_id' => $primaryBill->id
                ]);

                // Hapus baris tagihan pecahan lainnya
                Bill::whereIn('id', $otherBillIds)->forceDelete();
            }

            // Tentukan status akhir
            $finalStatus = Bill::STATUS_UNPAID;
            if ($totalPaid >= 550000) {
                $finalStatus = Bill::STATUS_PAID;
            } elseif ($totalPaid > 0) {
                $finalStatus = Bill::STATUS_PARTIAL;
            }

            // Update primary bill menjadi Rp 550.000
            $primaryBill->amount = 550000;
            $primaryBill->paid_amount = $totalPaid;
            $primaryBill->month = 0;
            $primaryBill->year = 2026;
            $primaryBill->status = $finalStatus;
            if ($primaryRateItem) {
                $primaryBill->payment_rate_item_id = $primaryRateItem->id;
            }
            $primaryBill->save();

            $processed++;
        }

        echo "      -> Selesai memproses {$processed} santri untuk {$bt->name}.\n";
    }

    DB::commit();
    Cache::flush();

    echo "\n=================================================================\n";
    echo "[SUKSES] Migrasi Zarkasi Berhasil 100%!\n";
    echo "- Model Jenis Bayar Zarkasi kini: Bebas (OTHER) dengan Cicilan (FREE)\n";
    echo "- Setiap santri kini memiliki TEPAT 1 KARTU TAGIHAN Zarkasi Rp 550.000\n";
    echo "- Semua histori pembayaran yang sudah masuk telah dialihkan & aman\n";
    echo "- Kasir & Wali Santri kini dapat mengangsur dengan bebas kapan saja\n";
    echo "=================================================================\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n[ERROR] Migrasi gagal: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    exit(1);
}
