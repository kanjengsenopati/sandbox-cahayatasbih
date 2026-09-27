<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$mConn = \Illuminate\Support\Facades\DB::connection('mysql_master');
$schema = $mConn->select("DESCRIBE bill_items");
echo json_encode($schema, JSON_PRETTY_PRINT);
$items = $mConn->table('bill_items')->whereIn('id', ['96a67821-ebd3-4566-b2f6-dd7be802dbb3', 'a3d6b01f-9c8c-4c9e-976f-e808c8f1e1d6', '67b63299-1bc6-4e03-97a8-759ac604f9aa'])->get();
echo "\nBILL ITEMS:\n";
echo json_encode($items, JSON_PRETTY_PRINT);
