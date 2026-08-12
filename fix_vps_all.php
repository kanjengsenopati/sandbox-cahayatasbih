<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

config(['database.default' => 'mysql']);
config(['database.connections.mysql.host' => env('DB_MASTER_HOST')]);
config(['database.connections.mysql.port' => env('DB_MASTER_PORT')]);
config(['database.connections.mysql.database' => env('DB_MASTER_DATABASE')]);
config(['database.connections.mysql.username' => env('DB_MASTER_USERNAME')]);
config(['database.connections.mysql.password' => env('DB_MASTER_PASSWORD')]);
\Illuminate\Support\Facades\DB::purge('mysql');
\Illuminate\Support\Facades\DB::setDefaultConnection('mysql');

echo "Running full recalculator on VPS DB...\n";
\App\Services\SaldoRecalculatorService::recalculateAllStudents();
echo "Done!\n";
