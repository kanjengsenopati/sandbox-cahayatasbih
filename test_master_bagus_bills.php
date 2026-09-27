<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$studentId = '6f4533fe-05e5-4d26-b061-3c7544a82795'; // BAGUS
$masterBillTypeId = 'fac520df-8e7c-443f-a653-fc76b4ac5d50'; // BIAYA APLIKASI CT-SMP

$masterConn = \Illuminate\Support\Facades\DB::connection('mysql_master');

$bills = $masterConn->table('bills')
    ->where('student_id', $studentId)
    ->where('bill_type_id', $masterBillTypeId)
    ->whereIn('month', [7, 8, 9])
    ->get(['month', 'status', 'amount']);

echo json_encode($bills, JSON_PRETTY_PRINT);
