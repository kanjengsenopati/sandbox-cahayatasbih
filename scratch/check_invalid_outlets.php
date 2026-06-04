<?php
include __DIR__ . '/../vendor/autoload.php';
$app = include_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PointOfSaleTransaction;
use App\Models\Outlet;

$outletIds = Outlet::pluck('id')->toArray();
echo "Valid Outlet IDs: " . implode(', ', $outletIds) . "\n";

// Count transactions with outlet_id NOT in valid outlet IDs
$invalidCount = PointOfSaleTransaction::whereNotIn('outlet_id', $outletIds)->count();
echo "Transactions with invalid outlet_id: $invalidCount\n";

// Get first 10 invalid transactions
if ($invalidCount > 0) {
    $invalidTxs = PointOfSaleTransaction::whereNotIn('outlet_id', $outletIds)->take(10)->get();
    foreach ($invalidTxs as $t) {
        echo "ID: {$t->id}, Code: {$t->payment_code}, Outlet ID: {$t->outlet_id}, Created: {$t->created_at}\n";
    }
}
