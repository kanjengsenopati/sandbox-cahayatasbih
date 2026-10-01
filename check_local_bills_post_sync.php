<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$studentId = '6f4533fe-05e5-4d26-b061-3c7544a82795'; // BAGUS
$localConn = \Illuminate\Support\Facades\DB::connection('sqlite'); // Local DB

// Check bill types that Bagus has in local DB
$bills = $localConn->table('bills')
    ->select('bill_type_id', 'status', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
    ->where('student_id', $studentId)
    ->groupBy('bill_type_id', 'status')
    ->get();

$result = [];
foreach ($bills as $b) {
    $bt = $localConn->table('bill_types')->where('id', $b->bill_type_id)->first();
    $result[] = [
        'bill_type_id' => $b->bill_type_id,
        'bill_type_name' => $bt ? $bt->name : 'UNKNOWN (GHOST)',
        'status' => $b->status,
        'count' => $b->count
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT);
