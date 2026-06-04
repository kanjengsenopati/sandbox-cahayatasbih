<?php
include __DIR__ . '/../vendor/autoload.php';
$app = include_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PointOfSaleTransaction;

echo "Total transactions: " . PointOfSaleTransaction::count() . "\n";
echo "Max created_at: " . PointOfSaleTransaction::max('created_at') . "\n";
echo "Transactions with NULL outlet_id: " . PointOfSaleTransaction::whereNull('outlet_id')->count() . "\n";

// Search for any transaction where payment_code contains 'POS-20260531'
$matches = PointOfSaleTransaction::where('payment_code', 'LIKE', '%POS-20260531%')->get();
echo "Matches for 'POS-20260531': " . $matches->count() . "\n";
foreach ($matches as $m) {
    echo "ID: {$m->id}, Code: {$m->payment_code}, Outlet ID: " . ($m->outlet_id ?? 'NULL') . ", Created: {$m->created_at}\n";
}
