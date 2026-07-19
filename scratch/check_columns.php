<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- TRANSACTIONS TABLE ---\n";
try {
    $columns = DB::select("SHOW COLUMNS FROM transactions");
    foreach ($columns as $column) {
        echo "Field: {$column->Field} | Type: {$column->Type}\n";
    }
} catch (\Exception $e) {
    echo "Error transactions: " . $e->getMessage() . "\n";
}

echo "\n--- TRANSACTION_PROOFS TABLE ---\n";
try {
    $columns = DB::select("SHOW COLUMNS FROM transaction_proofs");
    foreach ($columns as $column) {
        echo "Field: {$column->Field} | Type: {$column->Type}\n";
    }
} catch (\Exception $e) {
    echo "Error transaction_proofs: " . $e->getMessage() . "\n";
}
