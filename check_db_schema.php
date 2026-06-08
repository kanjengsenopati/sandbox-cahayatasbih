<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$columns = DB::select("SHOW COLUMNS FROM students");
foreach ($columns as $column) {
    echo "Field: {$column->Field} | Type: {$column->Type} | Null: {$column->Null} | Key: {$column->Key} | Default: {$column->Default}\n";
}
