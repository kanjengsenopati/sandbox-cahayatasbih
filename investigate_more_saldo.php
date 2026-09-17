<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tablesQuery = \Illuminate\Support\Facades\DB::select("SELECT name FROM sqlite_master WHERE type='table'");
foreach($tablesQuery as $t) {
    if (strpos($t->name, 'mutat') !== false || strpos($t->name, 'wallet') !== false || strpos($t->name, 'saldo') !== false || strpos($t->name, 'transaction') !== false) {
        echo "TABLE: " . $t->name . "\n";
        $schema = \Illuminate\Support\Facades\DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name=?", [$t->name]);
        echo $schema[0]->sql . "\n\n";
    }
}
