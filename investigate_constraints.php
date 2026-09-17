<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = ['bill_types', 'payment_rates', 'payment_rate_classrooms', 'bills', 'transactions'];

echo "=== DATABASE SCHEMA & CONSTRAINTS ===\n\n";

foreach ($tables as $table) {
    echo "TABLE: $table\n";
    $schema = \Illuminate\Support\Facades\DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name=?", [$table]);
    if (!empty($schema)) {
        echo $schema[0]->sql . "\n\n";
    }
    
    echo "INDEXES for $table:\n";
    $indexes = \Illuminate\Support\Facades\DB::select("PRAGMA index_list($table)");
    foreach ($indexes as $index) {
        $indexInfo = \Illuminate\Support\Facades\DB::select("PRAGMA index_info({$index->name})");
        $cols = [];
        foreach ($indexInfo as $info) {
            $cols[] = $info->name;
        }
        $unique = $index->unique ? 'UNIQUE' : 'NON-UNIQUE';
        echo "- {$index->name} ($unique) on (" . implode(', ', $cols) . ")\n";
    }
    echo "--------------------------------------------------------\n\n";
}

