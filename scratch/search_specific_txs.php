<?php
include __DIR__ . '/../vendor/autoload.php';
$app = include_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PointOfSaleTransaction;

echo "Search by pattern ending in 1638 or 1637:\n";
$txs = PointOfSaleTransaction::where('payment_code', 'LIKE', '%1638%')
    ->orWhere('payment_code', 'LIKE', '%1637%')
    ->get();

echo "Found count: " . $txs->count() . "\n";
foreach ($txs as $t) {
    echo "ID: {$t->id}, Code: {$t->payment_code}, Outlet ID: {$t->outlet_id}, Created: {$t->created_at}\n";
}
