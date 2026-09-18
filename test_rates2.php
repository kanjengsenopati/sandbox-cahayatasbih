<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$billType = \App\Models\BillType::find('c9e7ff1b-549a-4495-ac11-04c4c72fefd4');

$similarBillTypes = \App\Models\BillType::where('name', $billType->name)
    ->where('academic_year_id', $billType->academic_year_id)
    ->get();

echo "Similar bill types: " . $similarBillTypes->count() . "\n";
foreach($similarBillTypes as $bt) {
    echo "- ID: " . $bt->id . " | Deleted: " . ($bt->trashed() ? "Yes" : "No") . "\n";
    $rates = \App\Models\PaymentRate::where('bill_type_id', $bt->id)->count();
    echo "  Active Rates: " . $rates . "\n";
}
