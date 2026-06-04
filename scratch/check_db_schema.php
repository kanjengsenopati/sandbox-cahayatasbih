<?php
include __DIR__ . '/../vendor/autoload.php';
$app = include_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== SALDO_HISTORIES TABLE SCHEMA ===\n";
$columns = DB::select("SHOW COLUMNS FROM saldo_histories");
foreach ($columns as $column) {
    echo "Field: " . $column->Field . " | Type: " . $column->Type . " | Null: " . $column->Null . " | Key: " . $column->Key . " | Default: " . var_export($column->Default, true) . " | Extra: " . $column->Extra . "\n";
}
