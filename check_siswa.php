<?php
require __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/app/Helpers/helpers.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$studentNames = [
    'HANIK WULANDARI',
    'DEWI ERNAWATI',
    'HISYAM WIJAYA',
    'ALBY LUTHFY FACHRY',
    'ALFI ZALFA KHOIRIYAH',
    'ATQIYA ALAUDE NINA',
    'TAHTA NOTA ADILUHUNG',
    'KARIN PUTRI ANGGITA',
    'MOHAMMAD SIFA\' TAUFIKURROHMAN'
];

foreach ($studentNames as $name) {
    echo "========================================\n";
    echo "Student Name: $name\n";
    $student = \App\Models\Student::where('name', 'like', "%$name%")->first();
    if (!$student) {
        echo "Student not found!\n";
        continue;
    }
    
    $transactions = \App\Models\Transaction::with(['student', 'paymentMethod', 'activeProof.bank', 'transactionProofs.bank'])
        ->where('student_id', $student->id)
        ->latest('updated_at')
        ->limit(5)
        ->get();
        
    if ($transactions->isEmpty()) {
        echo "No transactions found!\n";
        continue;
    }
    
    foreach ($transactions as $t) {
        echo "Transaction ID: {$t->id}\n";
        echo "  Type: {$t->type}\n";
        echo "  Amount: {$t->pay_amount} (Unique: {$t->unique_payment})\n";
        echo "  Status: {$t->status}\n";
        echo "  Payment Method: " . ($t->paymentMethod?->name ?? 'None') . " (Type: " . ($t->paymentMethod?->type ?? 'None') . ")\n";
        echo "  Active Proof ID: " . ($t->activeProof?->id ?? 'None') . "\n";
        if ($t->activeProof) {
            echo "    Bank: " . ($t->activeProof->bank?->name ?? 'None') . "\n";
            echo "    Proof Image: " . $t->activeProof->proof_image . "\n";
            echo "    Proof Image URL: " . $t->activeProof->proof_image_url . "\n";
        }
        echo "  All Proofs Count: " . $t->transactionProofs->count() . "\n";
        foreach ($t->transactionProofs as $p) {
            echo "    - Proof ID: {$p->id}, is_active: {$p->is_active}, status: {$p->status}, bank: " . ($p->bank?->name ?? 'None') . "\n";
        }
    }
}
