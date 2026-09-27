<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$conn = \Illuminate\Support\Facades\DB::connection('mysql'); // The local default connection
$indexes = $conn->select("SHOW INDEX FROM bills WHERE Key_name = 'bills_unique_active_record'");
echo json_encode($indexes, JSON_PRETTY_PRINT);
