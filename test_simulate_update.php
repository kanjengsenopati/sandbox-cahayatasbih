<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$masterConn = \Illuminate\Support\Facades\DB::connection('mysql_master');
$localConn = \Illuminate\Support\Facades\DB::connection('sqlite');

$studentId = '6f4533fe-05e5-4d26-b061-3c7544a82795'; // BAGUS
$masterBillTypeId = 'fac520df-8e7c-443f-a653-fc76b4ac5d50'; // BIAYA APLIKASI CT-SMP in Master

$mBill = $masterConn->table('bills')
    ->where('student_id', $studentId)
    ->where('bill_type_id', $masterBillTypeId)
    ->where('month', '7')
    ->first();

echo "MASTER BILL:\n";
echo json_encode($mBill, JSON_PRETTY_PRINT);

$row = (array) $mBill;

$targetBillTypeId = $mBill->bill_type_id;
$masterBillType = $masterConn->table('bill_types')->where('id', $mBill->bill_type_id)->first();
if ($masterBillType) {
    $masterCleanName = str_replace(' ', '', $masterBillType->name);
    $localBillType = $localConn->table('bill_types')
        ->whereRaw("REPLACE(name, ' ', '') = ?", [$masterCleanName])
        ->where('academic_year_id', $masterBillType->academic_year_id)
        ->first();
    if ($localBillType) {
        $targetBillTypeId = $localBillType->id;
        echo "\nMAPPED UUID TO: $targetBillTypeId\n";
    }
}
$row['bill_type_id'] = $targetBillTypeId;

$localBill = $localConn->table('bills')
    ->where('student_id', $mBill->student_id)
    ->where('bill_type_id', $targetBillTypeId)
    ->where('month', $mBill->month)
    ->first();

echo "\nLOCAL BILL BEFORE:\n";
echo json_encode($localBill, JSON_PRETTY_PRINT);

if ($localBill) {
    $targetBillId = $localBill->id;
    unset($row['id']);
    try {
        $localConn->table('bills')->where('id', $targetBillId)->update($row);
        echo "\nUPDATE SUCCESSFUL!\n";
    } catch (\Exception $e) {
        echo "\nUPDATE FAILED: " . $e->getMessage() . "\n";
    }
} else {
    echo "\nLOCAL BILL NOT FOUND!\n";
}

$localBillAfter = $localConn->table('bills')->where('id', $localBill->id)->first();
echo "\nLOCAL BILL AFTER:\n";
echo json_encode($localBillAfter, JSON_PRETTY_PRINT);

