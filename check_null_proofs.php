<?php
require __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/app/Helpers/helpers.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Models\PaymentMethod;

$txs = Transaction::with(['student', 'paymentMethod', 'activeProof'])
    ->whereHas('paymentMethod', function ($query) {
        $query->where('type', PaymentMethod::TYPE_TRANSFER);
    })
    ->whereIn('status', [Transaction::STATUS_PAID, Transaction::STATUS_REJECTED])
    ->get();

$nullProofCount = 0;
$hasProofCount = 0;

foreach ($txs as $t) {
    if (!$t->activeProof) {
        $nullProofCount++;
        if ($nullProofCount <= 10) {
            echo "Null activeProof TX ID: {$t->id}\n";
            echo "  Student: " . ($t->student?->name ?? 'None') . "\n";
            echo "  Type: {$t->type}\n";
            echo "  Amount: {$t->pay_amount}\n";
            echo "  Status: {$t->status}\n";
            echo "  Created: {$t->created_at}\n";
        }
    } else {
        $hasProofCount++;
    }
}

echo "========================================\n";
echo "Total Transfer Transactions (PAID/REJECTED): " . $txs->count() . "\n";
echo "With activeProof: $hasProofCount\n";
echo "Without activeProof (null): $nullProofCount\n";
