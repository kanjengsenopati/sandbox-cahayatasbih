<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$id = '091f83b2-fe4b-4773-b2fb-9a6f2a5ffe93';

$rates = \Illuminate\Support\Facades\DB::table('payment_rates')
    ->where('bill_type_id', $id)
    ->whereNull('deleted_at')
    ->get();

echo "Orphaned Rates:\n";
foreach ($rates as $r) {
    echo "- Rate ID: {$r->id}, Amount: {$r->amount}, Type: {$r->type}, Created At: {$r->created_at}\n";
}
