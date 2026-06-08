<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Models\PaymentMethod;

$transactions = Transaction::with('student', 'paymentMethod', 'activeProof.bank', 'transactionProofs', 'admin')
    ->whereHas('student', fn($query) => $query->where('name', 'like', '%TAHTA NOTA ADILUHUNG%'))
    ->latest('updated_at')
    ->get();

foreach ($transactions as $tx) {
    echo "ID: " . $tx->id . "\n";
    echo "Student: " . $tx->student?->name . "\n";
    echo "Status: " . $tx->status . "\n";
    echo "Payment Method: " . $tx->paymentMethod?->name . " / type: " . $tx->paymentMethod?->type . "\n";
    echo "Active Proof ID: " . $tx->activeProof?->id . "\n";
    echo "Active Proof is_active: " . ($tx->activeProof?->is_active ? 'yes' : 'no') . "\n";
    echo "All Proofs Count: " . $tx->transactionProofs->count() . "\n";
    foreach ($tx->transactionProofs as $proof) {
        echo "  - Proof ID: " . $proof->id . ", is_active: " . ($proof->is_active ? '1':'0') . ", status: " . $proof->status . ", bank_id: " . $proof->bank_id . "\n";
    }
    echo "----------------------------------------\n";
}
