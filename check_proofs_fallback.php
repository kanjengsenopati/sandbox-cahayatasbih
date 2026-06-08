<?php
require __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/app/Helpers/helpers.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Models\PaymentMethod;

$txs = Transaction::with(['student', 'paymentMethod', 'activeProof', 'transactionProofs'])
    ->whereHas('paymentMethod', function ($query) {
        $query->where('type', PaymentMethod::TYPE_TRANSFER);
    })
    ->whereIn('status', [Transaction::STATUS_PAID, Transaction::STATUS_REJECTED])
    ->get();

$recoveredCount = 0;
$stillNullCount = 0;
$activeProofCount = 0;

foreach ($txs as $t) {
    if ($t->activeProof) {
        $activeProofCount++;
    } else {
        if ($t->transactionProofs->isNotEmpty()) {
            $recoveredCount++;
            if ($recoveredCount <= 5) {
                echo "Recovered TX ID: {$t->id}\n";
                echo "  Student: " . ($t->student?->name ?? 'None') . "\n";
                echo "  Status: {$t->status}\n";
                echo "  First Proof ID: " . $t->transactionProofs->first()->id . "\n";
            }
        } else {
            $stillNullCount++;
        }
    }
}

echo "========================================\n";
echo "Total Transfer Transactions (PAID/REJECTED): " . $txs->count() . "\n";
echo "With activeProof: $activeProofCount\n";
echo "Without activeProof but has transactionProofs (Recovered): $recoveredCount\n";
echo "No proofs at all: $stillNullCount\n";
