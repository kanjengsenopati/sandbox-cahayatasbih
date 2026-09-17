<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$billTypeId = 'c9e7ff1b-549a-4495-ac11-04c4c72fefd4';
$academicYearId = '56f6f5bf-ca0a-4230-af38-8e2f3fcf35d1';

$billType = \App\Models\BillType::find($billTypeId);
if (!$billType) {
    echo "BillType not found\n";
    exit;
}

echo "BillType Name: " . $billType->name . "\n";
echo "BillType Type: " . $billType->type . "\n";
echo "BillType Item ID: " . $billType->bill_item_id . "\n";
echo "BillType Input: " . $billType->payment_input_type . "\n";

$relatedQuery = \App\Models\BillType::query()->where('name', $billType->name);
if (!empty($billType->bill_item_id)) {
    $relatedQuery->where('bill_item_id', $billType->bill_item_id);
}
if (!empty($billType->type)) {
    $relatedQuery->where('type', $billType->type);
}
if (!empty($billType->payment_input_type)) {
    $relatedQuery->where('payment_input_type', $billType->payment_input_type);
}

$relatedBillTypeIds = $relatedQuery->pluck('id')->toArray();
echo "Related BillType IDs: " . implode(', ', $relatedBillTypeIds) . "\n";

$regularRates = \App\Models\PaymentRate::whereIn('bill_type_id', $relatedBillTypeIds)
    ->where('type', 'REGULAR')
    ->whereHas('billType', function ($q) use ($academicYearId) {
        $q->where('academic_year_id', $academicYearId);
    })
    ->get();

echo "Regular Rates (BillTypeController style): " . $regularRates->count() . "\n";

$existingClassroomIds = \Illuminate\Support\Facades\DB::table('payment_rate_classrooms')
    ->join('payment_rates', 'payment_rate_classrooms.payment_rate_id', '=', 'payment_rates.id')
    ->join('bill_types', 'payment_rates.bill_type_id', '=', 'bill_types.id')
    ->where(function($q) use ($billType) {
        $q->where('payment_rates.bill_type_id', $billType->id)
          ->orWhere(function($sub) use ($billType) {
              $sub->where('bill_types.name', $billType->name)
                  ->where('bill_types.academic_year_id', $billType->academic_year_id);
          });
    })
    ->where('payment_rates.type', 'REGULAR')
    ->whereNull('payment_rates.deleted_at')
    ->whereNull('payment_rate_classrooms.deleted_at')
    ->select('payment_rate_classrooms.payment_rate_id', 'payment_rate_classrooms.classroom_id', 'bill_types.id as bill_type_id')
    ->get();

echo "Existing Classroom Links: " . $existingClassroomIds->count() . "\n";
if ($existingClassroomIds->count() > 0) {
    echo "Sample data:\n";
    foreach ($existingClassroomIds->take(10) as $row) {
        echo "- RateID: $row->payment_rate_id | ClassID: $row->classroom_id | BillTypeID: $row->bill_type_id\n";
    }
}
