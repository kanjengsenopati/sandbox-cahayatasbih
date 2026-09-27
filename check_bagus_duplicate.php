<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$lConn = \Illuminate\Support\Facades\DB::connection('sqlite');

// The student_id in the error:
$studentId = 'bf0a054d-68a6-4465-a8b1-20d0aaa5a94d'; 
// The mapped bill_type_id:
$billTypeId = 'bece13e2-358c-4832-8853-807d81458441'; 
$month = '8';

$bills = $lConn->table('bills')
    ->where('student_id', $studentId)
    ->where('bill_type_id', $billTypeId)
    ->where('month', $month)
    ->get();

echo "BILLS WITH SAME COMBO:\n";
echo json_encode($bills, JSON_PRETTY_PRINT);
