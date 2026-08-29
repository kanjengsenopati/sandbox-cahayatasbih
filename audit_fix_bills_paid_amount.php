<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Starting Audit & Fix for bills.paid_amount...\n";

// Get all bills that have transaction details
$billsWithTx = DB::table('transaction_details')
    ->select('bill_id', DB::raw('SUM(amount) as total_paid'))
    ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
    ->where('transactions.status', 'PAID')
    ->whereNull('transaction_details.deleted_at')
    ->whereNull('transactions.deleted_at')
    ->whereNotNull('transaction_details.bill_id')
    ->groupBy('bill_id')
    ->get();

$fixedCount = 0;
$corruptedCount = 0;

foreach ($billsWithTx as $tx) {
    $billId = $tx->bill_id;
    $actualPaid = (int)$tx->total_paid;

    $bill = DB::table('bills')->where('id', $billId)->first();
    if (!$bill) continue;

    $currentPaid = (int)$bill->paid_amount;
    $amount = (int)$bill->amount;

    if ($currentPaid !== $actualPaid) {
        $corruptedCount++;
        echo "Bill {$billId} (Student: {$bill->student_id}): Corrupted paid_amount. DB: {$currentPaid} vs Actual: {$actualPaid} (Amount: {$amount})\n";
        
        $newStatus = $bill->status;
        if ($actualPaid == 0) {
            $newStatus = 'UNPAID';
        } elseif ($actualPaid >= $amount) {
            $newStatus = 'PAID';
        } else {
            $newStatus = 'PARTIAL';
        }

        DB::table('bills')->where('id', $billId)->update([
            'paid_amount' => $actualPaid,
            'status' => $newStatus,
            'updated_at' => now(),
        ]);
        $fixedCount++;
    }
}

// Find bills with paid_amount > 0 but NO transactions (Ghost payments)
$ghostBills = DB::table('bills')
    ->where('paid_amount', '>', 0)
    ->whereNotExists(function ($query) {
        $query->select(DB::raw(1))
            ->from('transaction_details')
            ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
            ->where('transactions.status', 'PAID')
            ->whereNull('transaction_details.deleted_at')
            ->whereNull('transactions.deleted_at')
            ->whereColumn('transaction_details.bill_id', 'bills.id');
    })
    ->get();

foreach ($ghostBills as $bill) {
    $corruptedCount++;
    echo "Bill {$bill->id} (Student: {$bill->student_id}): Ghost paid_amount. DB: {$bill->paid_amount} but NO TRANSACTIONS.\n";
    DB::table('bills')->where('id', $bill->id)->update([
        'paid_amount' => 0,
        'status' => 'UNPAID',
        'updated_at' => now(),
    ]);
    $fixedCount++;
}

echo "Audit completed. Found {$corruptedCount} corrupted bills, fixed {$fixedCount}.\n";

