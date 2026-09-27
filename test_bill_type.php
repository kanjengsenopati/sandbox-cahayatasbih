<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$mConn = \Illuminate\Support\Facades\DB::connection('mysql_master');
$schema = $mConn->select("DESCRIBE bill_types");
echo json_encode($schema, JSON_PRETTY_PRINT);

$syahriah = $mConn->table('bill_types')->where('name', 'like', '%SYAHRIAH%')->whereNull('deleted_at')->get();
echo "\nSYAHRIAH BILL TYPES:\n";
echo json_encode($syahriah, JSON_PRETTY_PRINT);
