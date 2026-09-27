<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$mConn = \Illuminate\Support\Facades\DB::connection('mysql_master');
$saldo = $mConn->table('saldo_histories')->latest()->limit(5)->get();
echo json_encode($saldo, JSON_PRETTY_PRINT);
