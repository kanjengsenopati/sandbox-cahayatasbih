<?php
include __DIR__ . '/../vendor/autoload.php';
$app = include_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PointOfSaleTransaction;

echo "=== SEARCHING FOR SPECIFIC POS TRANSACTIONS ===\n";
// Let's search by invoice code POS-20260531638 or similar
$txs = PointOfSaleTransaction::with('outlet', 'admins')
    ->where('payment_code', 'like', '%20260531%')
    ->orWhere('admin_id', '10b3ea92-9d82-40ef-a858-3a34adf0dd9f')
    ->latest()
    ->take(10)
    ->get();

echo "Found " . count($txs) . " transactions:\n";
foreach ($txs as $tx) {
    echo "ID: " . $tx->id . "\n";
    echo "Code: " . $tx->payment_code . "\n";
    echo "Created At: " . $tx->created_at . "\n";
    echo "Paid At: " . $tx->paid_at . "\n";
    echo "Admin: " . ($tx->admins?->name ?? 'N/A') . " (ID: " . $tx->admin_id . ")\n";
    echo "Outlet ID (column): " . var_export($tx->outlet_id, true) . "\n";
    echo "Outlet Relation Name: " . ($tx->outlet?->name ?? 'N/A') . "\n";
    echo "--------------------------------------------------\n";
}
