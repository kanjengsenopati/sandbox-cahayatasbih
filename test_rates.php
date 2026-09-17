<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$relatedBillTypeIds = ['c9e7ff1b-549a-4495-ac11-04c4c72fefd4'];

$rates = \App\Models\PaymentRate::whereIn('bill_type_id', $relatedBillTypeIds)->get();
echo "Rates for this bill type without whereHas: " . $rates->count() . "\n";

$ratesWithTrashed = \App\Models\PaymentRate::whereIn('bill_type_id', $relatedBillTypeIds)->withTrashed()->get();
echo "Rates with trashed: " . $ratesWithTrashed->count() . "\n";
