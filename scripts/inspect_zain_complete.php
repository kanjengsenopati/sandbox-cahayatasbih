<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "====================================================\n";
echo "   INSPEKSI TRANSAKSI TOPUP ZAIN IFTADA & KODE UNIK\n";
echo "====================================================\n";

$student = \App\Models\Student::where('name', 'like', '%ZAIN IFTADA%')->first();
if (!$student) {
    echo "Siswa Zain Iftada tidak ditemukan di database ini.\n";
    exit;
}

echo "Siswa: {$student->name} | NIS: {$student->nis} | Current Saldo: Rp " . number_format($student->saldo, 0, ',', '.') . "\n\n";

echo "--- SEMUA TRANSAKSI TOPUP SALDO SISWA INI ---\n";
$transactions = \App\Models\Transaction::where('student_id', $student->id)
    ->where('type', \App\Models\Transaction::TYPE_SALDO)
    ->orderBy('created_at', 'desc')
    ->get();

foreach ($transactions as $tx) {
    echo "TX ID: {$tx->id} | Code: {$tx->payment_code} | PayAmount: {$tx->pay_amount} | Unique: {$tx->unique_payment} | Status: {$tx->status} | Created: {$tx->created_at} | Updated: {$tx->updated_at}\n";
    
    $details = \App\Models\TransactionDetail::where('transaction_id', $tx->id)->withTrashed()->get();
    echo "  Details count: " . $details->count() . "\n";
    foreach ($details as $d) {
        $sh = \App\Models\SaldoHistory::withTrashed()->find($d->saldo_history_id);
        $deleted = $d->deleted_at ? " [DELETED {$d->deleted_at}]" : "";
        echo "    * TD: {$d->id} | Amount: {$d->amount} | SH_ID: " . ($d->saldo_history_id ?? 'NULL') . "{$deleted}\n";
        if ($sh) {
            $shDeleted = $sh->deleted_at ? " [DELETED {$sh->deleted_at}]" : "";
            echo "      -> SH: Amount: {$sh->amount} | Status: {$sh->status} | Type: {$sh->type} | Desc: {$sh->description} | Before: {$sh->balance_before} | After: {$sh->balance_after}{$shDeleted}\n";
        }
    }
}

echo "\n--- SEMUA SALDO HISTORIES SISWA INI (LATEST 20) ---\n";
$shs = \App\Models\SaldoHistory::where('student_id', $student->id)
    ->withTrashed()
    ->orderBy('created_at', 'desc')
    ->take(20)
    ->get();

foreach ($shs as $sh) {
    $deleted = $sh->deleted_at ? " [DELETED]" : "";
    echo "SH [{$sh->id}]: Amount: {$sh->amount} | Type: {$sh->type} | Status: {$sh->status} | Desc: {$sh->description} | Before: {$sh->balance_before} | After: {$sh->balance_after} | Created: {$sh->created_at}{$deleted}\n";
}

echo "\n--- SIMULASI RUNNING BALANCE JIKA DI-RECALCULATE ---\n";
$successHistories = \App\Models\SaldoHistory::where('student_id', $student->id)
    ->where('status', \App\Models\SaldoHistory::STATUS_SUCCESS)
    ->orderBy('created_at', 'asc')
    ->orderBy('id', 'asc')
    ->get();

$runBal = 0;
$first = $successHistories->first();
if ($first && (float)$first->balance_before > 0) {
    $runBal = (float)$first->balance_before;
    echo "Initial Balance from first record before: Rp " . number_format($runBal, 0, ',', '.') . "\n";
}

foreach ($successHistories as $h) {
    if ($h->type === \App\Models\SaldoHistory::TYPE_IN) {
        $runBal += (float)$h->amount;
    } else {
        $runBal -= (float)$h->amount;
    }
    echo "  + History: {$h->description} | Type: {$h->type} | Amount: {$h->amount} => Running: {$runBal}\n";
}
echo "Expected Saldo: Rp " . number_format($runBal, 0, ',', '.') . " | Current DB Saldo: Rp " . number_format($student->saldo, 0, ',', '.') . "\n";
