<?php
/**
 * Script untuk Audit dan Reparasi Transaksi Top Up Saldo Santri
 * (Memperbaiki kasus nominal pokok tidak masuk ke saldo dan hanya kode unik yang masuk)
 *
 * Penggunaan:
 *   php scripts/repair_topup_and_sync_saldo.php          (Dry run - hanya cek)
 *   php scripts/repair_topup_and_sync_saldo.php --fix    (Terapkan perbaikan & sync saldo)
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\SaldoHistory;
use App\Services\SaldoRecalculatorService;
use Illuminate\Support\Facades\DB;

$isFix = in_array('--fix', $argv);

echo "=================================================================\n";
echo "   REPARASI TOPUP SALDO & SINKRONISASI POKOK + KODE UNIK        \n";
echo "=================================================================\n";
echo "Mode: " . ($isFix ? "LIVE FIX (Menerapkan Perbaikan)" : "DRY RUN (Pemeriksaan Saja)") . "\n\n";

// 1. Ambil seluruh transaksi topup saldo yang berstatus PAID dalam 30 hari terakhir
$paidTopups = Transaction::with(['student', 'transactionDetails.saldoHistory'])
    ->where('type', Transaction::TYPE_SALDO)
    ->whereIn('status', [Transaction::STATUS_PAID, 'paid', 'PAID', 'approved', 'APPROVED', 'SUCCESS', 'success', 'LUNAS', 'lunas'])
    ->where('created_at', '>=', now()->subDays(30))
    ->orderBy('created_at', 'desc')
    ->get();

echo "Total transaksi Top Up Saldo berstatus PAID (30 hari terakhir): " . $paidTopups->count() . " transaksi.\n\n";

$affectedStudents = collect();
$issueCount = 0;

foreach ($paidTopups as $tx) {
    $student = $tx->student;
    if (!$student) continue;

    $payAmount = (int)$tx->pay_amount;
    $uniqueCode = (int)($tx->unique_payment ?? 0);
    $mainAmount = max(0, $payAmount - $uniqueCode);

    // Cari detail pokok dan kode unik
    $details = TransactionDetail::where('transaction_id', $tx->id)->get();
    
    // Cari apakah ada SaldoHistory pokok yang SUCCESS
    $mainHistory = SaldoHistory::where('student_id', $student->id)
        ->where('status', SaldoHistory::STATUS_SUCCESS)
        ->where('type', SaldoHistory::TYPE_IN)
        ->where('amount', $mainAmount)
        ->where('description', 'not like', '%Kode Unik%')
        ->first();

    // Cari apakah ada SaldoHistory pokok yang masih PENDING (korban bug)
    $pendingMainHistory = SaldoHistory::where('student_id', $student->id)
        ->where('status', SaldoHistory::STATUS_PENDING)
        ->where('type', SaldoHistory::TYPE_IN)
        ->where('description', 'not like', '%Kode Unik%')
        ->first();

    // Cari history kode unik jika ada kode unik
    $uniqueHistory = null;
    if ($uniqueCode > 0) {
        $uniqueHistory = SaldoHistory::where('student_id', $student->id)
            ->where('amount', $uniqueCode)
            ->where('description', 'like', '%' . $tx->payment_code . '%')
            ->first();
    }

    $needsFix = false;
    $reason = [];

    if (!$mainHistory) {
        $needsFix = true;
        if ($pendingMainHistory) {
            $reason[] = "Record pokok (Rp " . number_format($mainAmount, 0, ',', '.') . ") masih PENDING (belum di-approve)";
        } else {
            $reason[] = "Record pokok (Rp " . number_format($mainAmount, 0, ',', '.') . ") hilang/belum dibuat di saldo_histories";
        }
    }

    if ($uniqueCode > 0 && (!$uniqueHistory || $uniqueHistory->status !== SaldoHistory::STATUS_SUCCESS)) {
        $needsFix = true;
        $reason[] = "Kode unik (#{$uniqueCode}) belum masuk ke saldo_histories sebagai SUCCESS";
    }

    if ($needsFix) {
        $issueCount++;
        echo "-----------------------------------------------------------------\n";
        echo "[TEMUAN #{$issueCount}] Santri: {$student->name} (NIS: {$student->nis})\n";
        echo "  - TX: {$tx->payment_code} | Total: Rp " . number_format($payAmount, 0, ',', '.') . " | Unik: #{$uniqueCode} | Pokok: Rp " . number_format($mainAmount, 0, ',', '.') . "\n";
        echo "  - Saldo saat ini: Rp " . number_format($student->saldo, 0, ',', '.') . "\n";
        echo "  - Masalah: " . implode(', ', $reason) . "\n";

        if ($isFix) {
            DB::transaction(function () use ($tx, $student, $mainAmount, $uniqueCode, $pendingMainHistory, $details) {
                // A. Pastikan SaldoHistory Pokok SUCCESS
                if ($pendingMainHistory) {
                    $pendingMainHistory->update([
                        'status' => SaldoHistory::STATUS_SUCCESS,
                        'amount' => $mainAmount,
                        'type' => SaldoHistory::TYPE_IN,
                        'usage' => SaldoHistory::USAGE_TOPUP,
                        'description' => 'Top Up Saldo Saku Sebesar Rp.' . number_format($mainAmount, 0, ',', '.'),
                        'updated_at' => now(),
                    ]);
                    $mainHistoryId = $pendingMainHistory->id;
                } else {
                    $newMain = SaldoHistory::create([
                        'student_id' => $student->id,
                        'amount' => $mainAmount,
                        'type' => SaldoHistory::TYPE_IN,
                        'description' => 'Top Up Saldo Saku Sebesar Rp.' . number_format($mainAmount, 0, ',', '.'),
                        'status' => SaldoHistory::STATUS_SUCCESS,
                        'usage' => SaldoHistory::USAGE_TOPUP,
                        'created_at' => $tx->created_at ?? now(),
                        'updated_at' => now(),
                    ]);
                    $mainHistoryId = $newMain->id;
                }

                // Tautkan ke detail transaksi pokok
                $mainDetail = $details->first(function ($d) {
                    return empty($d->saldo_history_id) || ($d->saldoHistory && strpos($d->saldoHistory->description, 'Kode Unik') === false);
                });

                if ($mainDetail) {
                    $mainDetail->update([
                        'saldo_history_id' => $mainHistoryId,
                        'amount' => $mainAmount,
                    ]);
                } else {
                    TransactionDetail::create([
                        'id' => \Illuminate\Support\Str::uuid()->toString(),
                        'transaction_id' => $tx->id,
                        'saldo_history_id' => $mainHistoryId,
                        'amount' => $mainAmount,
                    ]);
                }

                // B. Pastikan SaldoHistory Kode Unik SUCCESS
                if ($uniqueCode > 0) {
                    $existingUnique = SaldoHistory::where('student_id', $student->id)
                        ->where('amount', $uniqueCode)
                        ->where('description', 'like', '%' . $tx->payment_code . '%')
                        ->first();

                    if (!$existingUnique) {
                        $newUnique = SaldoHistory::create([
                            'student_id' => $student->id,
                            'amount' => $uniqueCode,
                            'type' => SaldoHistory::TYPE_IN,
                            'description' => 'Pengembalian Kode Unik Transaksi #' . $tx->payment_code . ' Sebesar Rp.' . number_format($uniqueCode, 0, ',', '.'),
                            'usage' => SaldoHistory::USAGE_TOPUP,
                            'status' => SaldoHistory::STATUS_SUCCESS,
                            'created_at' => $tx->created_at ?? now(),
                            'updated_at' => now(),
                        ]);
                        $uniqueHistoryId = $newUnique->id;
                    } else {
                        $existingUnique->update(['status' => SaldoHistory::STATUS_SUCCESS]);
                        $uniqueHistoryId = $existingUnique->id;
                    }

                    // Tautkan ke detail kode unik jika belum ada
                    $hasUniqueDetail = TransactionDetail::where('transaction_id', $tx->id)
                        ->where('saldo_history_id', $uniqueHistoryId)
                        ->exists();

                    if (!$hasUniqueDetail) {
                        TransactionDetail::create([
                            'id' => \Illuminate\Support\Str::uuid()->toString(),
                            'transaction_id' => $tx->id,
                            'amount' => $uniqueCode,
                            'saldo_history_id' => $uniqueHistoryId,
                        ]);
                    }
                }
            });

            $affectedStudents->push($student->id);
            echo "  -> [PERBAIKAN BERHASIL] Record pokok dan kode unik sudah diset STATUS_SUCCESS.\n";
        }
    }
}

// 2. Jika mode --fix, jalankan recalculateForStudent untuk semua santri yang terkena dampak
if ($isFix && $affectedStudents->isNotEmpty()) {
    echo "\n=================================================================\n";
    echo "   MENJALANKAN RECALCULATE RUNNING BALANCE UNTUK SANTRI TERKENA \n";
    echo "=================================================================\n";

    $uniqueStudentIds = $affectedStudents->unique();
    foreach ($uniqueStudentIds as $sid) {
        $st = Student::find($sid);
        $oldSaldo = $st->saldo;
        SaldoRecalculatorService::recalculateForStudent($sid);
        $st->refresh();
        echo "Santri: {$st->name} (NIS: {$st->nis})\n";
        echo "  - Saldo Lama : Rp " . number_format($oldSaldo, 0, ',', '.') . "\n";
        echo "  - Saldo Baru : Rp " . number_format($st->saldo, 0, ',', '.') . "\n";
        echo "  - Selisih Masuk: +Rp " . number_format($st->saldo - $oldSaldo, 0, ',', '.') . "\n\n";
    }
    echo "Selesai! Seluruh saldo santri berhasil disinkronkan.\n";
} elseif (!$isFix && $issueCount > 0) {
    echo "\n=================================================================\n";
    echo "Ditemukan {$issueCount} transaksi dengan nominal pokok belum masuk ke saldo.\n";
    echo "Jalankan perintah berikut di server untuk menerapkan perbaikan:\n";
    echo "  php scripts/repair_topup_and_sync_saldo.php --fix\n";
    echo "=================================================================\n";
} else {
    echo "\nSemua transaksi topup saldo dalam kondisi sehat dan sinkron.\n";
}
