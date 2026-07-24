<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Bill;
use App\Models\BillType;
use App\Models\PaymentRate;
use App\Models\AcademicYear;

$lutfiana = Student::where('name', 'like', '%LUTFIANA%')->with('classroom.school')->get();
echo "Lutfiana matches:\n";
foreach ($lutfiana as $s) {
    echo "ID: {$s->id} | Name: '{$s->name}' | Class: {$s->classroom?->name} (ID: {$s->classroom_id})\n";
    $bills = Bill::where('student_id', $s->id)->with('billType.academicYear')->get();
    echo "  Total Bills: " . $bills->count() . "\n";
    foreach ($bills as $b) {
        echo "    Bill ID: {$b->id} | BillType: '{$b->billType?->name}' (AY: {$b->billType?->academicYear?->name}, BT_ID: {$b->bill_type_id}) | Month: {$b->month}/{$b->year} | Amount: {$b->amount}\n";
    }
}

$aliffia = Student::where('name', 'like', '%ALIFFIA%')->with('classroom.school')->get();
echo "\nAliffia matches:\n";
foreach ($aliffia as $s) {
    echo "ID: {$s->id} | Name: '{$s->name}' | Class: {$s->classroom?->name} (ID: {$s->classroom_id})\n";
    $bills = Bill::where('student_id', $s->id)->with('billType.academicYear')->get();
    echo "  Total Bills: " . $bills->count() . "\n";
    foreach ($bills as $b) {
        echo "    Bill ID: {$b->id} | BillType: '{$b->billType?->name}' (AY: {$b->billType?->academicYear?->name}, BT_ID: {$b->bill_type_id}) | Month: {$b->month}/{$b->year} | Amount: {$b->amount}\n";
    }
}

echo "\nBill Types named SYAHRIAH for 2026/2027:\n";
$ay2026 = AcademicYear::where('name', 'like', '%2026/2027%')->first();
$syahriahBts = BillType::where('academic_year_id', $ay2026?->id)->where('name', 'like', '%SYAHR%')->get();
foreach ($syahriahBts as $bt) {
    $ratesCount = PaymentRate::where('bill_type_id', $bt->id)->count();
    $billsCount = Bill::where('bill_type_id', $bt->id)->count();
    echo "BillType ID: {$bt->id} | Name: '{$bt->name}' | Rates Count: {$ratesCount} | Bills Count: {$billsCount}\n";
}
