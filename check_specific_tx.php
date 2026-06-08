<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;

$txs = Transaction::with('student', 'paymentMethod', 'activeProof.bank', 'transactionProofs')
    ->where('unique_payment', '78')
    ->whereIn('status', [Transaction::STATUS_PAID, Transaction::STATUS_REJECTED])
    ->get();

foreach ($txs as $tx) {
    echo "ID: " . $tx->id . "\n";
    echo "Amount: " . $tx->pay_amount . "\n";
    echo "Unique: " . $tx->unique_payment . "\n";
    echo "Method: " . $tx->paymentMethod?->name . " / type: " . $tx->paymentMethod?->type . "\n";
    echo "Status: " . $tx->status . "\n";
    echo "Updated At: " . $tx->updated_at . "\n";
    echo "Active Proof ID: " . $tx->activeProof?->id . "\n";
    echo "Active Proof is_active: " . ($tx->activeProof?->is_active ? '1':'0') . "\n";
    echo "Active Proof status: " . $tx->activeProof?->status . "\n";
    echo "Active Proof bank: " . ($tx->activeProof?->bank?->name ?? 'None') . "\n";
    echo "All Proofs Count: " . $tx->transactionProofs->count() . "\n";
    foreach ($tx->transactionProofs as $p) {
        echo "  - Proof ID: " . $p->id . ", status: " . $p->status . ", is_active: " . ($p->is_active?'1':'0') . ", bank: " . ($p->bank?->name ?? 'None') . "\n";
    }
    echo "----------------------------------------\n";
}
