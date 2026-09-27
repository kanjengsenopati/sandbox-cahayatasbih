<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bridge = app(\App\Services\MasterIngestionBridgeService::class);

// BAGUS student ID
$studentIds = ['6f4533fe-05e5-4d26-b061-3c7544a82795'];

// Master bill type ID
$masterBillTypeId = 'fac520df-8e7c-443f-a653-fc76b4ac5d50';
$academicYearId = '56f6f5bf-ca0a-4230-af38-8e2f3fcf35d1'; // Master academic year

$result = $bridge->executeVerifiedMerge(
    'billing_status',
    $studentIds,
    $academicYearId,
    $masterBillTypeId
);

echo json_encode($result, JSON_PRETTY_PRINT);
