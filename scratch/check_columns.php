<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- BILLS TABLE ---\n";
try {
    $columns = DB::select("SHOW COLUMNS FROM bills");
    foreach ($columns as $column) {
        echo "Field: {$column->Field} | Type: {$column->Type}\n";
    }
} catch (\Exception $e) {
    echo "Error bills: " . $e->getMessage() . "\n";
}
