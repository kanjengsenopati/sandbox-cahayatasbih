<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$id = '091f83b2-fe4b-4773-b2fb-9a6f2a5ffe93';

$billType = \Illuminate\Support\Facades\DB::table('bill_types')->where('id', $id)->first();
if ($billType) {
    echo "Found BillType in DB!\n";
    echo "ID: " . $billType->id . "\n";
    echo "Name: " . $billType->name . "\n";
    echo "Academic Year ID: " . $billType->academic_year_id . "\n";
    echo "Deleted At: " . $billType->deleted_at . "\n";
} else {
    echo "BillType completely missing in DB.\n";
}

$ratesCount = \Illuminate\Support\Facades\DB::table('payment_rates')->where('bill_type_id', $id)->whereNull('deleted_at')->count();
echo "Active Payment Rates attached to this BillType: " . $ratesCount . "\n";
