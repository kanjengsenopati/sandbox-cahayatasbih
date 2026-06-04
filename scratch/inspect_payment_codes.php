<?php
include __DIR__ . '/../vendor/autoload.php';
$app = include_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PointOfSaleTransaction;

echo "First 10 transaction payment codes:\n";
$txs = PointOfSaleTransaction::orderBy('created_at', 'desc')->take(10)->get();
foreach ($txs as $t) {
    echo "ID: {$t->id}, Code: {$t->payment_code}, Outlet ID: {$t->outlet_id}, Created: {$t->created_at}\n";
}

echo "\nSearching for POS-20260531:\n";
$found = PointOfSaleTransaction::where('payment_code', 'LIKE', '%POS-20260531%')->get();
echo "Found: " . $found->count() . "\n";
foreach ($found as $t) {
    echo "ID: {$t->id}, Code: {$t->payment_code}, Outlet ID: {$t->outlet_id}\n";
}
