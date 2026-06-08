<?php
require __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/app/Helpers/helpers.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;

$amounts = [100141, 50062, 50254, 50150, 100220, 200271, 500078, 500222, 500254, 500058];

foreach ($amounts as $amount) {
    echo "========================================\n";
    echo "Searching for Amount: $amount\n";
    $txs = Transaction::with(['student', 'paymentMethod', 'activeProof.bank', 'transactionProofs.bank'])
        ->where('pay_amount', $amount)
        ->get();
        
    if ($txs->isEmpty()) {
        // Try searching by pay_amount + unique_payment
        // Wait, maybe pay_amount in DB is the base amount (e.g. 100000) and unique_payment is 141?
        // Let's check that too
        echo "No direct pay_amount match. Trying base amount + unique...\n";
        // Let's do a wider query
    }
    
    foreach ($txs as $t) {
        echo "Transaction ID: {$t->id}\n";
        echo "  Student: " . ($t->student?->name ?? 'Unknown') . "\n";
        echo "  Type: {$t->type}\n";
        echo "  Amount: {$t->pay_amount} (Unique: {$t->unique_payment})\n";
        echo "  Status: {$t->status}\n";
        echo "  Payment Method: " . ($t->paymentMethod?->name ?? 'None') . " (Type: " . ($t->paymentMethod?->type ?? 'None') . ")\n";
        echo "  Active Proof ID: " . ($t->activeProof?->id ?? 'None') . "\n";
        if ($t->activeProof) {
            echo "    Bank: " . ($t->activeProof->bank?->name ?? 'None') . "\n";
            echo "    Proof Image: " . $t->activeProof->proof_image . "\n";
        }
        echo "  All Proofs Count: " . $t->transactionProofs->count() . "\n";
        foreach ($t->transactionProofs as $p) {
            echo "    - Proof ID: {$p->id}, is_active: {$p->is_active}, status: {$p->status}, bank: " . ($p->bank?->name ?? 'None') . "\n";
        }
    }
}
