<?php
require __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/app/Helpers/helpers.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Total transfer transactions
$total = DB::table('transactions')
    ->join('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id')
    ->where('payment_methods.type', 'TRANSFER')
    ->whereIn('transactions.status', ['PAID', 'REJECTED'])
    ->count();

// Has active proof
$hasActiveProof = DB::table('transactions')
    ->join('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id')
    ->join('transaction_proofs', 'transactions.id', '=', 'transaction_proofs.transaction_id')
    ->where('payment_methods.type', 'TRANSFER')
    ->whereIn('transactions.status', ['PAID', 'REJECTED'])
    ->where('transaction_proofs.is_active', true)
    ->whereNull('transaction_proofs.deleted_at')
    ->count();

// Has any proof but no active proof
$hasAnyProofButNoActive = DB::table('transactions')
    ->join('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id')
    ->where('payment_methods.type', 'TRANSFER')
    ->whereIn('transactions.status', ['PAID', 'REJECTED'])
    ->whereNotExists(function ($query) {
        $query->select(DB::raw(1))
            ->from('transaction_proofs')
            ->whereColumn('transactions.id', 'transaction_proofs.transaction_id')
            ->where('transaction_proofs.is_active', true)
            ->whereNull('transaction_proofs.deleted_at');
    })
    ->whereExists(function ($query) {
        $query->select(DB::raw(1))
            ->from('transaction_proofs')
            ->whereColumn('transactions.id', 'transaction_proofs.transaction_id')
            ->whereNull('transaction_proofs.deleted_at');
    })
    ->count();

// No proofs at all
$noProofs = DB::table('transactions')
    ->join('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id')
    ->where('payment_methods.type', 'TRANSFER')
    ->whereIn('transactions.status', ['PAID', 'REJECTED'])
    ->whereNotExists(function ($query) {
        $query->select(DB::raw(1))
            ->from('transaction_proofs')
            ->whereColumn('transactions.id', 'transaction_proofs.transaction_id')
            ->whereNull('transaction_proofs.deleted_at');
    })
    ->count();

echo "Total: $total\n";
echo "Has active proof: $hasActiveProof\n";
echo "Has any proof but no active: $hasAnyProofButNoActive\n";
echo "No proofs at all: $noProofs\n";

// Show some examples of "Has any proof but no active" if any
if ($hasAnyProofButNoActive > 0) {
    echo "\nExamples of 'Has any proof but no active':\n";
    $examples = DB::table('transactions')
        ->select('transactions.id', 'transactions.pay_amount', 'transactions.status')
        ->join('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id')
        ->where('payment_methods.type', 'TRANSFER')
        ->whereIn('transactions.status', ['PAID', 'REJECTED'])
        ->whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('transaction_proofs')
                ->whereColumn('transactions.id', 'transaction_proofs.transaction_id')
                ->where('transaction_proofs.is_active', true)
                ->whereNull('transaction_proofs.deleted_at');
        })
        ->whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('transaction_proofs')
                ->whereColumn('transactions.id', 'transaction_proofs.transaction_id')
                ->whereNull('transaction_proofs.deleted_at');
        })
        ->limit(5)
        ->get();
    foreach ($examples as $ex) {
        echo "  - TX ID: {$ex->id}, Amount: {$ex->pay_amount}, Status: {$ex->status}\n";
    }
}
