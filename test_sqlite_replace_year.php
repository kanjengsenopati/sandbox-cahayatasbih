<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$localConn = \Illuminate\Support\Facades\DB::connection('sqlite');

$masterCleanName = 'BIAYAAPLIKASICT-SMP';
$academic_year_id = '56f6f5bf-ca0a-4230-af38-8e2f3fcf35d1'; // Assuming this is the year being used

$localBillType = $localConn->table('bill_types')
    ->whereRaw("REPLACE(name, ' ', '') = ?", [$masterCleanName])
    ->where('academic_year_id', $academic_year_id)
    ->get(['id', 'name', 'academic_year_id', 'created_at']);
echo json_encode($localBillType, JSON_PRETTY_PRINT);
