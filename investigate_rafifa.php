<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$student = \App\Models\Student::where('name', 'like', '%RAFIFA AQILA%')->first();
if (!$student) {
    echo "Student RAFIFA AQILA not found.\n";
    exit;
}

echo "Student: {$student->name} (ID: {$student->id}, NIS: {$student->nis})\n\n";

// Find the bills for LKS SMT 1 - SMP
$bills = \Illuminate\Support\Facades\DB::table('bills')
    ->join('bill_types', 'bills.bill_type_id', '=', 'bill_types.id')
    ->leftJoin('payment_rate_items', 'bills.payment_rate_item_id', '=', 'payment_rate_items.id')
    ->where('bills.student_id', $student->id)
    ->where('bill_types.name', 'like', '%LKS%')
    ->whereNull('bills.deleted_at')
    ->select('bills.id as bill_id', 'bills.bill_type_id', 'bill_types.name as bill_type_name', 'bills.amount', 'bills.paid_amount', 'bills.status', 'bills.payment_rate_item_id')
    ->get();

echo "=== Bills for LKS ===\n";
foreach ($bills as $bill) {
    echo "- Bill ID: {$bill->bill_id}\n";
    echo "  Bill Type: {$bill->bill_type_name} (ID: {$bill->bill_type_id})\n";
    echo "  Rate Item ID: {$bill->payment_rate_item_id}\n";
    echo "  Amount: {$bill->amount} | Paid: {$bill->paid_amount} | Status: {$bill->status}\n\n";
}

// Find PaymentRates for the BillType
$billTypeIds = $bills->pluck('bill_type_id')->unique();
foreach ($billTypeIds as $btId) {
    $rates = \Illuminate\Support\Facades\DB::table('payment_rates')
        ->where('bill_type_id', $btId)
        ->whereNull('deleted_at')
        ->get();
    
    echo "=== Payment Rates for Bill Type ID $btId ===\n";
    foreach ($rates as $r) {
        $classrooms = \Illuminate\Support\Facades\DB::table('payment_rate_classrooms')
            ->join('classrooms', 'payment_rate_classrooms.classroom_id', '=', 'classrooms.id')
            ->where('payment_rate_classrooms.payment_rate_id', $r->id)
            ->whereNull('payment_rate_classrooms.deleted_at')
            ->pluck('classrooms.name')->toArray();
            
        echo "- Rate ID: {$r->id} | Type: {$r->type} | Classes: " . implode(', ', $classrooms) . "\n";
    }
}
