<?php
include __DIR__ . '/../vendor/autoload.php';
$app = include_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PointOfSaleTransaction;
use App\Models\PointOfSaleTransactionDetail;
use App\Models\Item;

$startDate = '2026-05-01';
$endDate = '2026-05-31';
$queryOutletId = '38f9e045-879a-424b-8150-d553ed846c61'; // CAHAYA MART

// Test 1: Original Query logic
echo "Running Original Query...\n";
$start = microtime(true);
$original = Item::whereIsActive(true)
    ->when($queryOutletId, function($q) use ($queryOutletId) {
        $q->where('outlet_id', $queryOutletId);
    })
    ->withCount(['pointOfSaleTransactionDetails' => function ($query) use ($startDate, $endDate, $queryOutletId) {
        $query->whereHas('pointOfSaleTransaction', function ($transactionQuery) use ($startDate, $endDate, $queryOutletId) {
            $transactionQuery->where('status', PointOfSaleTransaction::STATUS_SUCCESS);
            if ($startDate && $endDate) {
                $transactionQuery->whereDate('created_at', '>=', $startDate)
                                 ->whereDate('created_at', '<=', $endDate);
            }
            if ($queryOutletId) {
                $transactionQuery->where('outlet_id', $queryOutletId);
            }
        });
    }])
    ->orderByDesc('point_of_sale_transaction_details_count')
    ->take(10)
    ->get();
$time = microtime(true) - $start;
echo "Original took: " . number_format($time, 4) . " seconds. Got " . count($original) . " items.\n";
foreach ($original as $item) {
    echo " - " . $item->name . ": " . $item->point_of_sale_transaction_details_count . "\n";
}

// Test 2: Optimized Query logic (starting from Detail)
echo "\nRunning Optimized Query...\n";
$start = microtime(true);
$optimized = PointOfSaleTransactionDetail::query()
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
    ->get()
    ->map(function ($detail) {
        return [
            'name' => $detail->item?->name ?? 'Barang Terhapus',
            'total_transaction' => $detail->total_transaction
        ];
    });
$time = microtime(true) - $start;
echo "Optimized took: " . number_format($time, 4) . " seconds. Got " . count($optimized) . " items.\n";
foreach ($optimized as $item) {
    echo " - " . $item['name'] . ": " . $item['total_transaction'] . "\n";
}
