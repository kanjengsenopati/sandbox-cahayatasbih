<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bill;
use Illuminate\Support\Facades\DB;

// Find duplicates based on student_id, bill_type_id, month, year, academic_year_id
$duplicates = DB::table('bills')
    ->whereNull('deleted_at')
    ->select('student_id', 'bill_type_id', 'month', 'year', 'academic_year_id', DB::raw('COUNT(*) as count'))
    ->groupBy('student_id', 'bill_type_id', 'month', 'year', 'academic_year_id')
    ->havingRaw('COUNT(*) > 1')
    ->get();

echo "Found " . $duplicates->count() . " duplicate groups.\n";

$totalDeleted = 0;

foreach ($duplicates as $dup) {
    $bills = Bill::where('student_id', $dup->student_id)
        ->where('bill_type_id', $dup->bill_type_id)
        ->where('month', $dup->month)
        ->where('year', $dup->year)
        ->where('academic_year_id', $dup->academic_year_id)
        ->orderBy('created_at', 'desc')
        ->get();

    // Strategy: Keep the PAID one if exists, otherwise keep the latest UNPAID one.
    $keepId = null;
    $paidBill = $bills->where('status', 'PAID')->first();
    
    if ($paidBill) {
        $keepId = $paidBill->id;
    } else {
        $keepId = $bills->first()->id; // Latest one
    }

    foreach ($bills as $bill) {
        if ($bill->id !== $keepId) {
            echo "Deleting duplicate bill: {$bill->id} (Status: {$bill->status})\n";
            // Uncomment to actually delete
            $bill->forceDelete(); 
            $totalDeleted++;
        }
    }
}

echo "Total bills to delete: $totalDeleted\n";
