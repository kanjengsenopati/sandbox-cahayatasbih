<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$localConn = \Illuminate\Support\Facades\DB::connection('sqlite');

try {
    $masterCleanName = 'BIAYAAPLIKASICT-SMP';
    $localBillType = $localConn->table('bill_types')
        ->whereRaw("REPLACE(name, ' ', '') = ?", [$masterCleanName])
        ->get(['id', 'name']);
    echo "SUCCESS:\n";
    echo json_encode($localBillType, JSON_PRETTY_PRINT);
} catch (\Exception $e) {
    echo "ERROR:\n";
    echo $e->getMessage();
}
