<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$billTypeId = 'c9e7ff1b-549a-4495-ac11-04c4c72fefd4';
$billType = \App\Models\BillType::find($billTypeId);

$existingRates = \App\Models\PaymentRate::where(function($q) use ($billType) {
        $q->where('bill_type_id', $billType->id)
          ->orWhereHas('billType', function($sub) use ($billType) {
              $sub->where('name', $billType->name)
                  ->where('academic_year_id', $billType->academic_year_id)
                  ->whereNull('deleted_at');
          });
    })
    ->where('type', \App\Models\PaymentRate::TYPE_REGULAR)
    ->with('paymentRateClassrooms')
    ->get();

echo "Existing rates count: " . $existingRates->count() . "\n";
foreach($existingRates as $rate) {
    echo "Rate ID: " . $rate->id . " | BillType ID: " . $rate->bill_type_id . "\n";
    echo "Classrooms: " . $rate->paymentRateClassrooms->count() . "\n";
}
