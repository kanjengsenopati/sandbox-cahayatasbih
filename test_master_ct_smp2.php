<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$mConn = \Illuminate\Support\Facades\DB::connection('mysql_master');
$b = $mConn->table('bill_types')->where('name', 'BIAYA APLIKASI CT-SMP')->get();
echo json_encode($b, JSON_PRETTY_PRINT);
