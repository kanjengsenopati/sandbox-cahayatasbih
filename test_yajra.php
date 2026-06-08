<?php
require __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/app/Helpers/helpers.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Models\PaymentMethod;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

// Login first admin
$admin = Admin::whereHas('adminSchool')->first();
if ($admin) {
    Auth::login($admin);
    echo "Logged in as Admin: {$admin->name}\n";
} else {
    // If no admin with adminSchool, log in any user
    $anyUser = \App\Models\User::first();
    if ($anyUser) {
        Auth::login($anyUser);
        echo "Logged in as User: {$anyUser->name}\n";
    }
}

// Query exactly like getArchiveTransactionData() in SaldoHistoryController
$transactions = Transaction::with('student', 'paymentMethod', 'activeProof.bank', 'admin')
    ->whereHas('paymentMethod', function ($query) {
        $query->where('type', PaymentMethod::TYPE_TRANSFER);
    })
    ->where('type', Transaction::TYPE_SALDO)
    ->whereIn('status', [Transaction::STATUS_PAID, Transaction::STATUS_REJECTED])
    ->where('is_deleted_from_archive', false)
    ->hasSchool()
    ->latest('updated_at')
    ->limit(5)
    ->get();

echo "=================== SALDO HISTORY ARCHIVE DATA ===================\n";
foreach ($transactions as $t) {
    $bank = $t->activeProof?->bank;
    $proofUrl = $t->activeProof?->proof_image_url ?? $t->activeProof?->proof_image;
    
    echo "Transaction ID: {$t->id}\n";
    echo "  Student: " . ($t->student?->name ?? 'None') . "\n";
    echo "  Active Proof: " . ($t->activeProof ? 'Yes' : 'No') . "\n";
    if ($t->activeProof) {
        echo "    Bank: " . ($bank ? "{$bank->name} (No: {$bank->account_number})" : 'None') . "\n";
        echo "    Proof URL: " . ($proofUrl ?? 'None') . "\n";
    } else {
        // Let's see all proofs count
        $t->load('transactionProofs');
        echo "    No active proof! Total proofs: " . $t->transactionProofs->count() . "\n";
        foreach ($t->transactionProofs as $proof) {
            echo "      - Proof ID: {$proof->id}, is_active: " . ($proof->is_active?'1':'0') . ", status: {$proof->status}\n";
        }
    }
}

// Let's also do for BillController
$billTransactions = Transaction::with('student', 'paymentMethod', 'activeProof.bank', 'admin')
    ->whereHas('paymentMethod', function ($query) {
        $query->where('type', PaymentMethod::TYPE_TRANSFER);
    })
    ->where('type', Transaction::TYPE_BILL)
    ->whereIn('status', [Transaction::STATUS_PAID, Transaction::STATUS_REJECTED])
    ->where('is_deleted_from_archive', false)
    ->hasSchool()
    ->latest('updated_at')
    ->limit(5)
    ->get();

echo "\n=================== BILL ARCHIVE DATA ===================\n";
foreach ($billTransactions as $t) {
    $bank = $t->activeProof?->bank;
    $proofUrl = $t->activeProof?->proof_image_url ?? $t->activeProof?->proof_image;
    
    echo "Transaction ID: {$t->id}\n";
    echo "  Student: " . ($t->student?->name ?? 'None') . "\n";
    echo "  Active Proof: " . ($t->activeProof ? 'Yes' : 'No') . "\n";
    if ($t->activeProof) {
        echo "    Bank: " . ($bank ? "{$bank->name} (No: {$bank->account_number})" : 'None') . "\n";
        echo "    Proof URL: " . ($proofUrl ?? 'None') . "\n";
    } else {
        $t->load('transactionProofs');
        echo "    No active proof! Total proofs: " . $t->transactionProofs->count() . "\n";
        foreach ($t->transactionProofs as $proof) {
            echo "      - Proof ID: {$proof->id}, is_active: " . ($proof->is_active?'1':'0') . ", status: {$proof->status}\n";
        }
    }
}
