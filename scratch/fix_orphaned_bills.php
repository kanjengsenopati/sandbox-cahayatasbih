<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Find bills whose billType is soft-deleted
$orphanedBills = \App\Models\Bill::whereHas('billType', function ($q) {
        $q->onlyTrashed();
    })
    ->whereNull('deleted_at')
    ->get();

echo "Found " . $orphanedBills->count() . " orphaned bills belonging to soft-deleted BillTypes.\n";

if ($orphanedBills->count() > 0) {
    $count = 0;
    foreach ($orphanedBills as $b) {
        $b->delete(); // soft delete orphaned bill
        $count++;
    }
    echo "Successfully soft-deleted {$count} orphaned bill records.\n";
}
