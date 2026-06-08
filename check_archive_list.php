<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Models\PaymentMethod;

$transactions = Transaction::with('student', 'paymentMethod', 'activeProof.bank', 'transactionProofs', 'admin')
    ->whereHas('paymentMethod', function ($query) {
        $query->where('type', PaymentMethod::TYPE_TRANSFER);
    })
    ->where('type', Transaction::TYPE_SALDO)
    ->whereIn('status', [Transaction::STATUS_PAID, Transaction::STATUS_REJECTED])
    ->where('is_deleted_from_archive', false)
    ->latest('updated_at')
    ->limit(20)
    ->get();

echo "TOTAL RETRIEVED: " . $transactions->count() . "\n";
foreach ($transactions as $tx) {
    echo "ID: " . $tx->id . "\n";
    echo "Student: " . $tx->student?->name . "\n";
    echo "Amount: " . $tx->pay_amount . "\n";
    echo "Unique: " . $tx->unique_payment . "\n";
    echo "Method: " . $tx->paymentMethod?->name . " / type: " . $tx->paymentMethod?->type . "\n";
    echo "Status: " . $tx->status . "\n";
    echo "Updated At: " . $tx->updated_at . "\n";
    echo "Active Proof ID: " . $tx->activeProof?->id . "\n";
    echo "Active Proof is_active: " . ($tx->activeProof?->is_active ? '1':'0') . "\n";
    echo "Active Proof Image: " . ($tx->activeProof?->proof_image ?? 'None') . "\n";
    echo "Active Proof Bank: " . ($tx->activeProof?->bank?->name ?? 'None') . "\n";
    echo "All Proofs Count: " . $tx->transactionProofs->count() . "\n";
    foreach ($tx->transactionProofs as $p) {
        echo "  - Proof ID: " . $p->id . ", status: " . $p->status . ", is_active: " . ($p->is_active?'1':'0') . ", bank: " . ($p->bank?->name ?? 'None') . "\n";
    }
    echo "----------------------------------------\n";
}
