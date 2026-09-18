<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$billTypeId = 'c9e7ff1b-549a-4495-ac11-04c4c72fefd4';
$academicYearId = '56f6f5bf-ca0a-4230-af38-8e2f3fcf35d1';

$billType = \App\Models\BillType::find($billTypeId);

$duplicates = \App\Models\BillType::where('name', $billType->name)
    ->where('academic_year_id', $billType->academic_year_id)
    ->get();

echo "Found " . $duplicates->count() . " BillType(s) with name '" . $billType->name . "' and academic_year_id '$academicYearId'\n\n";

foreach ($duplicates as $dup) {
    echo "ID: " . $dup->id . "\n";
    echo "Name: " . $dup->name . "\n";
    echo "Type: " . $dup->type . "\n";
    echo "Bill Item ID: " . $dup->bill_item_id . "\n";
    echo "Payment Input Type: " . $dup->payment_input_type . "\n";
    
    // Check rates for THIS dup
    $ratesCount = \App\Models\PaymentRate::where('bill_type_id', $dup->id)
        ->where('type', 'REGULAR')
        ->count();
    
    echo "Has Regular Rates: " . $ratesCount . "\n";
    echo "--------------------------\n";
}

// In BillTypeController@show, relatedBillTypeIds are:
$relatedQuery = \App\Models\BillType::query()->where('name', $billType->name);
if (!empty($billType->bill_item_id)) { $relatedQuery->where('bill_item_id', $billType->bill_item_id); }
if (!empty($billType->type)) { $relatedQuery->where('type', $billType->type); }
if (!empty($billType->payment_input_type)) { $relatedQuery->where('payment_input_type', $billType->payment_input_type); }

$relatedBillTypeIds = $relatedQuery->pluck('id')->toArray();
echo "\nIn BillTypeController show(), related IDs are: " . implode(', ', $relatedBillTypeIds) . "\n";
