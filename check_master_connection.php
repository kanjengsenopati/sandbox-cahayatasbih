<?php
require __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/app/Helpers/helpers.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $masterDb = DB::connection('mysql_master');
    $pdo = $masterDb->getPdo();
    echo "Successfully connected to mysql_master database!\n";
    
    // Check if tables exist
    $tables = ['banks', 'transaction_proofs', 'bill_type_banks', 'topup_banks'];
    foreach ($tables as $table) {
        $exists = $masterDb->select("SHOW TABLES LIKE '{$table}'");
        echo "Table '{$table}' exists in master: " . (empty($exists) ? 'No' : 'Yes') . "\n";
        if (!empty($exists)) {
            $count = $masterDb->table($table)->count();
            echo "  Row count: {$count}\n";
        }
    }
} catch (\Throwable $e) {
    echo "Connection to mysql_master failed: " . $e->getMessage() . "\n";
}
