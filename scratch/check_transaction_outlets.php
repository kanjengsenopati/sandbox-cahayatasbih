<?php
include __DIR__ . '/../vendor/autoload.php';
$app = include_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PointOfSaleTransaction;

$invoices = ['POS-20260531638', 'POS-20260531637'];
foreach ($invoices as $inv) {
    $t = PointOfSaleTransaction::where('payment_code', $inv)->first();
    if ($t) {
        echo "Invoice: $inv, Outlet ID: " . ($t->outlet_id ?? 'NULL') . "\n";
    } else {
        echo "Invoice: $inv not found!\n";
    }
}

// Also let's check how many transactions have NULL outlet_id
$nullCount = PointOfSaleTransaction::whereNull('outlet_id')->count();
echo "Transactions with NULL outlet_id: $nullCount\n";
