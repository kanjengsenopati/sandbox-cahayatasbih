<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$mConn = \Illuminate\Support\Facades\DB::connection('mysql_master');
$schema = $mConn->select("DESCRIBE payment_rates");
echo json_encode($schema, JSON_PRETTY_PRINT);
