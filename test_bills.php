<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$student = \App\Models\Student::where('name', 'like', '%ABDULLAH FAIZ%')->first();
$bills = \DB::table('bills')->where('student_id', $student->id)->whereNull('deleted_at')->get();
echo "Total bills found: " . count($bills) . "\n";
foreach($bills as $bill) {
    if ($bill->paid_amount > 0) {
        echo "Bill: {$bill->month}/{$bill->year} - Amount: {$bill->amount} - Paid: {$bill->paid_amount} - Type ID: {$bill->bill_type_id}\n";
    }
}
$rate = \App\Models\PaymentRate::latest()->first();
echo "Current Rate BillType ID: " . $rate->bill_type_id . "\n";
