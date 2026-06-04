<?php
include __DIR__ . '/../vendor/autoload.php';
$app = include_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Item;
use App\Models\PointOfSaleTransaction;
use Yajra\DataTables\DataTables;

try {
    echo "Running query...\n";
    $startDate = '2026-05-01';
    $endDate = '2026-05-31';
    $queryOutletId = null; // or Koperasi ID if you have it

    $data = Item::whereIsActive(true)
        ->when($queryOutletId, function($q) use ($queryOutletId) {
            if (is_array($queryOutletId)) {
                $q->whereIn('outlet_id', $queryOutletId);
            } else {
                $q->where('outlet_id', $queryOutletId);
            }
        })
        ->withCount(['pointOfSaleTransactionDetails' => function ($query) use ($startDate, $endDate, $queryOutletId) {
            $query->whereHas('pointOfSaleTransaction', function ($transactionQuery) use ($startDate, $endDate, $queryOutletId) {
                $transactionQuery->where('status', PointOfSaleTransaction::STATUS_SUCCESS);
                if ($startDate && $endDate) {
                    $transactionQuery->whereDate('created_at', '>=', $startDate)
                                     ->whereDate('created_at', '<=', $endDate);
                }
                if ($queryOutletId) {
                    if (is_array($queryOutletId)) {
                        $transactionQuery->whereIn('outlet_id', $queryOutletId);
                    } else {
                        $transactionQuery->where('outlet_id', $queryOutletId);
                    }
                }
            });
        }])
        ->orderByDesc('point_of_sale_transaction_details_count')
        ->take(10);

    echo "Query builder created. Trying to get results...\n";
    $results = $data->get();
    echo "Success! Retrieved " . count($results) . " items.\n";

    echo "Trying DataTables wrapper...\n";
    $response = DataTables::of($data)
        ->addColumn('total_transaction', fn($data) => $data->point_of_sale_transaction_details_count ?? 0)
        ->rawColumns(['total_transaction'])
        ->make(true);
    echo "DataTables wrapper success!\n";
} catch (\Exception $e) {
    echo "EXCEPTION THROWN:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
