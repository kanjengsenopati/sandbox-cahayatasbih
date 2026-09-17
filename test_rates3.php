<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$billType = \App\Models\BillType::find('c9e7ff1b-549a-4495-ac11-04c4c72fefd4');

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
    ->where('payment_rates.type', \App\Models\PaymentRate::TYPE_REGULAR)
    ->whereNull('bill_types.deleted_at')
    ->whereNull('payment_rates.deleted_at')
    ->whereNull('payment_rate_classrooms.deleted_at')
    ->pluck('payment_rate_classrooms.classroom_id')
    ->unique()
    ->values()
    ->toArray();

echo "Existing classroom IDs count: " . count($existingClassroomIds) . "\n";
print_r($existingClassroomIds);
