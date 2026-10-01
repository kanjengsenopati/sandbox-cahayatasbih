<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$studentId = '6f4533fe-05e5-4d26-b061-3c7544a82795'; // BAGUS
$billTypeId = 'bece13e2-358c-4832-8853-807d81458441'; // BIAYA APLIKASI CT - SMP local UUID

$localConn = \Illuminate\Support\Facades\DB::connection('sqlite');

$bills = $localConn->table('bills')
    ->where('student_id', $studentId)
    ->where('bill_type_id', $billTypeId)
    ->get(['id', 'month', 'status', 'paid_amount', 'amount']);

echo json_encode($bills, JSON_PRETTY_PRINT);
