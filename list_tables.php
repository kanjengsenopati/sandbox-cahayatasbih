<?php
require __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/app/Helpers/helpers.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
$dbName = DB::getDatabaseName();
$key = "Tables_in_" . $dbName;

echo "Database Tables in $dbName:\n";
foreach ($tables as $t) {
    $tableName = $t->$key;
    if (strpos($tableName, 'bank') !== false || strpos($tableName, 'proof') !== false || strpos($tableName, 'sync') !== false) {
        echo "  - {$tableName}\n";
    }
}
