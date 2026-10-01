<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$localConn = \Illuminate\Support\Facades\DB::connection('sqlite');

$types = $localConn->table('bill_types')
    ->where('name', 'like', '%BIAYA APLIKASI CT%')
    ->get(['id', 'name', 'academic_year_id']);

echo json_encode($types, JSON_PRETTY_PRINT);
