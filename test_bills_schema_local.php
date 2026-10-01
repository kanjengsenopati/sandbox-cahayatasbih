<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$lConn = \Illuminate\Support\Facades\DB::connection('sqlite');
$schema = $lConn->select("PRAGMA table_info(bills)");
echo json_encode($schema, JSON_PRETTY_PRINT);
