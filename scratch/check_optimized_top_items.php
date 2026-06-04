<?php
include __DIR__ . '/../vendor/autoload.php';
$app = include_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PointOfSaleTransaction;
use App\Models\PointOfSaleTransactionDetail;

$startDate = '2026-05-01';
$endDate = '2026-05-31';
$queryOutletId = '38f9e045-879a-424b-8150-d553ed846c61'; // CAHAYA MART

// Test 1: whereHas
echo "Running whereHas Query...\n";
$start = microtime(true);
$whereHasResult = PointOfSaleTransactionDetail::query()
    ->select('item_id', \DB::raw('COUNT(id) as total_transaction'))
    ->whereHas('pointOfSaleTransaction', function ($q) use ($startDate, $endDate, $queryOutletId) {
        $q->where('status', PointOfSaleTransaction::STATUS_SUCCESS);
        if ($startDate && $endDate) {
            $q->whereDate('created_at', '>=', $startDate)
              ->whereDate('created_at', '<=', $endDate);
        }
        if ($queryOutletId) {
            $q->where('outlet_id', $queryOutletId);
        }
    })
    ->groupBy('item_id')
    ->orderByDesc('total_transaction')
    ->take(10)
    ->with('item')
    ->get();
$time = microtime(true) - $start;
echo "whereHas took: " . number_format($time, 4) . " seconds. Got " . count($whereHasResult) . " items.\n";

// Test 2: Join
echo "\nRunning Join Query...\n";
$start = microtime(true);
$joinResult = PointOfSaleTransactionDetail::query()
    ->join('point_of_sale_transactions', 'point_of_sale_transaction_details.point_of_sale_transaction_id', '=', 'point_of_sale_transactions.id')
    ->select('point_of_sale_transaction_details.item_id', \DB::raw('COUNT(point_of_sale_transaction_details.id) as total_transaction'))
    ->where('point_of_sale_transactions.status', PointOfSaleTransaction::STATUS_SUCCESS)
    ->whereNull('point_of_sale_transaction_details.deleted_at')
    ->whereNull('point_of_sale_transactions.deleted_at')
    ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
        $q->whereDate('point_of_sale_transactions.created_at', '>=', $startDate)
          ->whereDate('point_of_sale_transactions.created_at', '<=', $endDate);
    })
    ->when($queryOutletId, function ($q) use ($queryOutletId) {
        $q->where('point_of_sale_transactions.outlet_id', $queryOutletId);
    })
    ->groupBy('point_of_sale_transaction_details.item_id')
    ->orderByDesc('total_transaction')
    ->take(10)
    ->with('item')
    ->get();
$time = microtime(true) - $start;
echo "Join took: " . number_format($time, 4) . " seconds. Got " . count($joinResult) . " items.\n";
