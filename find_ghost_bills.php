<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bill;
use App\Models\BillType;

$billsWithTrashedTypes = Bill::whereHas('billType', function($q) {
    $q->onlyTrashed();
})->count();

echo "Found $billsWithTrashedTypes bills belonging to soft-deleted BillTypes.\n";

// List some examples
$examples = Bill::whereHas('billType', function($q) {
    $q->onlyTrashed();
})->with('billType')->limit(10)->get();

foreach ($examples as $bill) {
    echo "Bill ID: {$bill->id} | Type: {$bill->billType->name} (DELETED) | Student ID: {$bill->student_id} | Period: {$bill->month}/{$bill->year}\n";
}
