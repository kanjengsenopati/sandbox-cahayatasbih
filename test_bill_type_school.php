<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$mConn = \Illuminate\Support\Facades\DB::connection('mysql_master');

$billTypeSchools = $mConn->table('bill_types')
    ->leftJoin('payment_rates', 'payment_rates.bill_type_id', '=', 'bill_types.id')
    ->leftJoin('payment_rate_classrooms', 'payment_rate_classrooms.payment_rate_id', '=', 'payment_rates.id')
    ->leftJoin('classrooms', 'classrooms.id', '=', 'payment_rate_classrooms.classroom_id')
    ->whereNull('bill_types.deleted_at')
    ->select('bill_types.id', 'bill_types.name', 'classrooms.school_id')
    ->groupBy('bill_types.id', 'bill_types.name', 'classrooms.school_id')
    ->get();

echo json_encode($billTypeSchools, JSON_PRETTY_PRINT);
