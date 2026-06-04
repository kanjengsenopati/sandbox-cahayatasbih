<?php
include __DIR__ . '/../vendor/autoload.php';
$app = include_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PointOfSaleTransaction;
use App\Models\Outlet;

echo "Total POS transactions: " . PointOfSaleTransaction::count() . "\n";
echo "Total SUCCESS POS transactions: " . PointOfSaleTransaction::where('status', 'SUCCESS')->count() . "\n";

$transactionsPerOutlet = PointOfSaleTransaction::select('outlet_id', \DB::raw('count(*) as count'))
    ->groupBy('outlet_id')
    ->get();

foreach ($transactionsPerOutlet as $t) {
    $outletName = Outlet::find($t->outlet_id)?->name ?? 'Unknown (' . $t->outlet_id . ')';
    echo "Outlet: $outletName, ID: {$t->outlet_id}, count: {$t->count}\n";
}

$minDate = PointOfSaleTransaction::min('created_at');
$maxDate = PointOfSaleTransaction::max('created_at');
echo "Transaction date range: $minDate to $maxDate\n";
