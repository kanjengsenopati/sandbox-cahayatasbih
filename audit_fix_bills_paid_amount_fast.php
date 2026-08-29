<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Starting Fast Audit & Fix for bills.paid_amount...\n";

// Use chunks to avoid memory issues
DB::table('bills')->orderBy('id')->chunk(5000, function($bills) {
    $billIds = $bills->pluck('id')->toArray();
    
    // Get all valid transactions for these bills in one query
    $transactions = DB::table('transaction_details')
        ->select('bill_id', DB::raw('SUM(amount) as total_paid'))
        ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
        ->whereIn('transaction_details.bill_id', $billIds)
        ->where('transactions.status', 'PAID')
        ->whereNull('transaction_details.deleted_at')
        ->whereNull('transactions.deleted_at')
        ->groupBy('bill_id')
        ->get()
        ->keyBy('bill_id');
        
    foreach ($bills as $bill) {
        $actualPaid = isset($transactions[$bill->id]) ? (int)$transactions[$bill->id]->total_paid : 0;
        $currentPaid = (int)$bill->paid_amount;
        $amount = (int)$bill->amount;

        if ($currentPaid !== $actualPaid) {
            echo "Bill {$bill->id} (Student: {$bill->student_id}): DB {$currentPaid} vs Actual {$actualPaid}\n";
            
            $newStatus = $bill->status;
            if ($actualPaid == 0) {
                $newStatus = 'UNPAID';
            } elseif ($actualPaid >= $amount) {
                $newStatus = 'PAID';
            } else {
                $newStatus = 'PARTIAL';
            }

            DB::table('bills')->where('id', $bill->id)->update([
                'paid_amount' => $actualPaid,
                'status' => $newStatus,
                'updated_at' => now(),
            ]);
        }
    }
});

echo "Fast Audit completed.\n";

