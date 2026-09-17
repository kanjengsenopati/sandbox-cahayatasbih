<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tablesQuery = \Illuminate\Support\Facades\DB::select("SELECT name FROM sqlite_master WHERE type='table'");
$allTables = [];
foreach($tablesQuery as $t) { $allTables[] = $t->name; }

// Temukan tabel terkait saldo/mutasi
$targetTables = ['students', 'transactions', 'mutations', 'balance_mutations', 'wallet_mutations', 'student_balances', 'student_mutations'];
$foundTables = array_intersect($allTables, $targetTables);

echo "=== SALDO DATABASE SCHEMA & CONSTRAINTS ===\n\n";

foreach($foundTables as $t) {
    echo "TABLE: $t\n";
    $schema = \Illuminate\Support\Facades\DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name=?", [$t]);
    echo $schema[0]->sql . "\n\n";
    
    echo "INDEXES for $t:\n";
    $indexes = \Illuminate\Support\Facades\DB::select("PRAGMA index_list($t)");
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
