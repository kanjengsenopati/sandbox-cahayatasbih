<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bridge = app(\App\Services\MasterIngestionBridgeService::class);
$reflection = new ReflectionClass(get_class($bridge));
$method = $reflection->getMethod('analyzeBillingStatus');
$method->setAccessible(true);

$result = ['status_summary' => ['total_analyzed' => 0, 'new_count' => 0, 'update_count' => 0, 'match_count' => 0, 'conflict_count' => 0], 'items' => []];
$method->invokeArgs($bridge, [
    \Illuminate\Support\Facades\DB::connection('mysql_master'),
    \Illuminate\Support\Facades\DB::connection('sqlite'),
    &$result,
    0, // limit
    null, // school_id
    null, // classroom_id
    '56f6f5bf-ca0a-4230-af38-8e2f3fcf35d1', // academic_year_id (matching the bill_type)
    'fac520df-8e7c-443f-a653-fc76b4ac5d50' // bill_type_id
]);

$bagus = array_filter($result['items'], function($item) {
    return strpos($item['name'], 'BAGUS EKA SAPUTRA') !== false;
});
echo json_encode(array_values($bagus), JSON_PRETTY_PRINT);
