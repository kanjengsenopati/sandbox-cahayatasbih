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

echo "=== AUDIT DIAGNOSTIC ===\n";

$lutfiana = Student::where('name', 'like', '%LUTFIANA DWI CAHYANINGTYAS%')->with('classroom.school')->first();
$aliffia = Student::where('name', 'like', '%ALIFFIA MARSYALIMA PUTRI%')->with('classroom.school')->first();
$abyan = Student::where('name', 'like', '%MUHAMMAD ABYAN PRATAMA%')->with('classroom.school')->first();
$chaleo = Student::where('name', 'like', '%MUHAMMAD CHALEO ARGAPRADITHA%')->with('classroom.school')->first();

echo "Lutfiana: ID={$lutfiana->id}, Class={$lutfiana->classroom?->name}, School={$lutfiana->classroom?->school?->name}, Gender={$lutfiana->gender}, UserID={$lutfiana->user_id}\n";
echo "Aliffia: ID={$aliffia->id}, Class={$aliffia->classroom?->name}, School={$aliffia->classroom?->school?->name}, Gender={$aliffia->gender}, UserID={$aliffia->user_id}\n";
echo "Abyan: ID={$abyan->id}, Class={$abyan->classroom?->name}, School={$abyan->classroom?->school?->name}, Gender={$abyan->gender}, UserID={$abyan->user_id}\n";
echo "Chaleo: ID={$chaleo->id}, Class={$chaleo->classroom?->name}, School={$chaleo->classroom?->school?->name}, Gender={$chaleo->gender}, UserID={$chaleo->user_id}\n";

echo "\n--- Lutfiana Bills ---\n";
$lutfianaBills = Bill::where('student_id', $lutfiana->id)->with('billType.academicYear')->get();
foreach ($lutfianaBills as $b) {
    echo "Bill ID: {$b->id} | BillType ID: {$b->bill_type_id} | Name: {$b->billType?->name} | AY: {$b->billType?->academicYear?->name} | Month: {$b->month}/{$b->year} | Amount: {$b->amount}\n";
}

echo "\n--- Aliffia Bills ---\n";
$aliffiaBills = Bill::where('student_id', $aliffia->id)->with('billType.academicYear')->get();
foreach ($aliffiaBills as $b) {
    echo "Bill ID: {$b->id} | BillType ID: {$b->bill_type_id} | Name: {$b->billType?->name} | AY: {$b->billType?->academicYear?->name} | Month: {$b->month}/{$b->year} | Amount: {$b->amount}\n";
}

echo "\n--- Payment Rates for 2026/2027 ---\n";
$ay2026 = AcademicYear::where('name', 'like', '%2026/2027%')->first();
echo "Academic Year 2026/2027 ID: {$ay2026?->id}\n";

$rates = PaymentRate::with(['billType', 'paymentRateClassrooms.classroom'])->get();
foreach ($rates as $r) {
    if ($r->billType?->academic_year_id == $ay2026?->id) {
        $classes = $r->paymentRateClassrooms->map(fn($prc) => $prc->classroom?->name)->implode(',');
        echo "Rate ID: {$r->id} | BillType ID: {$r->bill_type_id} | BillType Name: '{$r->billType?->name}' | Amount: {$r->amount} | GenderFilter: '{$r->gender}' | JamaahFilter: '{$r->jamaah_status}' | Type: {$r->type} | Classes: [{$classes}]\n";
    }
}
