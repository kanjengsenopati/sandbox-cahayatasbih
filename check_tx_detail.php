<?php
require __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/app/Helpers/helpers.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;

$cases = [
    ['name' => 'HANIK WULANDARI', 'amount' => 100141, 'unique' => 141],
    ['name' => 'DEWI ERNAWATI', 'amount' => 50062, 'unique' => 62],
    ['name' => 'TAHTA NOTA ADILUHUNG', 'amount' => 500078, 'unique' => 78],
    ['name' => 'KARIN PUTRI ANGGITA', 'amount' => 500222, 'unique' => 222]
];

foreach ($cases as $case) {
    echo "========================================\n";
    echo "Searching for {$case['name']} (Amount: {$case['amount']}, Unique: {$case['unique']})\n";
    
    $txs = Transaction::with(['student', 'paymentMethod', 'activeProof.bank', 'transactionProofs.bank'])
        ->whereHas('student', function($query) use ($case) {
            $query->where('name', 'like', "%{$case['name']}%");
        })
        ->where('pay_amount', $case['amount'])
        ->get();
        
    if ($txs->isEmpty()) {
        echo "No transaction found by exact pay_amount!\n";
        continue;
    }
    
    foreach ($txs as $t) {
        echo "Transaction ID: {$t->id}\n";
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
