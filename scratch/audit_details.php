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
use Illuminate\Support\Facades\DB;

$out = [];
$out[] = "=== AUDIT DETAILS REPORT ===";

// 1. Find 2026/2027 Academic Year
$ay2026 = AcademicYear::where('name', 'like', '%2026/2027%')->first();
$out[] = "Academic Year 2026/2027 ID: " . ($ay2026 ? $ay2026->id : 'NOT FOUND');

// 2. Find Bill Types for 2026/2027
$billTypes = BillType::where('academic_year_id', $ay2026?->id)->get();
$out[] = "\nBill Types in 2026/2027:";
foreach ($billTypes as $bt) {
    $out[] = " - ID: {$bt->id} | Name: {$bt->name} | Type: {$bt->type} | InputType: {$bt->payment_input_type}";
}

// 3. Find Rates for 2026/2027
$rates = PaymentRate::whereIn('bill_type_id', $billTypes->pluck('id'))->with(['billType', 'paymentRateClassrooms.classroom'])->get();
$out[] = "\nPayment Rates in 2026/2027:";
foreach ($rates as $r) {
    $classes = $r->paymentRateClassrooms->map(fn($prc) => $prc->classroom?->name)->implode(', ');
    $out[] = " - Rate ID: {$r->id} | BillType ID: {$r->bill_type_id} ({$r->billType?->name}) | Amount: {$r->amount} | Gender: '{$r->gender}' | Jamaah: '{$r->jamaah_status}' | Type: {$r->type}";
    $out[] = "   Assigned Classes: [{$classes}]";
}

// 4. Check Students mentioned in screenshots:
// - LUTFIANA DWI CAHYANINGTYAS (8F)
// - ALIFFIA MARSYALIMA PUTRI (9E)
// - MUHAMMAD ABYAN PRATAMA (7G)
// - MUHAMMAD CHALEO ARGAPRADITHA (9A)
// - AZZAHIDI SIRADJ SEEHAN (7G)
// - MUHAMMAD NAUFAL DZULKARNAIN (8C)

$names = [
    'LUTFIANA DWI CAHYANINGTYAS',
    'ALIFFIA MARSYALIMA PUTRI',
    'MUHAMMAD ABYAN PRATAMA',
    'MUHAMMAD CHALEO ARGAPRADITHA',
    'AZZAHIDI SIRADJ SEEHAN',
    'MUHAMMAD NAUFAL DZULKARNAIN'
];

$out[] = "\nTarget Students Audit:";
foreach ($names as $name) {
    $st = Student::where('name', 'like', "%{$name}%")->with(['classroom.school', 'user'])->first();
    if (!$st) {
        $out[] = "Student '{$name}' NOT FOUND";
        continue;
    }
    $out[] = "--------------------------------------------------";
    $out[] = "Student: {$st->name} (ID: {$st->id})";
    $out[] = "  Classroom: {$st->classroom?->name} (ID: {$st->classroom_id}) | School: {$st->classroom?->school?->name}";
    $out[] = "  Gender: '{$st->gender}' | Status: {$st->status}";
    $out[] = "  Parent User ID: " . ($st->user_id ?? 'NULL') . " | Jamaah Status: " . ($st->user?->jamaah_status ?? 'NULL');
    
    // Bills for this student
    $bills = Bill::where('student_id', $st->id)->with('billType.academicYear')->get();
    $out[] = "  Bills Count: " . $bills->count();
    foreach ($bills as $b) {
        $out[] = "    -> Bill ID: {$b->id} | BillType: {$b->billType?->name} (ID: {$b->bill_type_id}, AY: {$b->billType?->academicYear?->name}) | RateItemID: " . ($b->payment_rate_item_id ?? 'NULL') . " | Month: {$b->month}/{$b->year} | Amount: Rp {$b->amount} | Status: {$b->status}";
    }
}

// 5. Total active students count per classroom in 2026/2027 and total bills generated
$out[] = "\nSummary of active students and generated bills for 2026/2027 per class:";
$classrooms = DB::table('classrooms')->whereNull('deleted_at')->orderBy('name')->get();
foreach ($classrooms as $c) {
    $stCount = Student::where('classroom_id', $c->id)->where('status', 'ACTIVE')->count();
    $billCount = Bill::whereHas('student', fn($q) => $q->where('classroom_id', $c->id))
        ->whereHas('billType', fn($q) => $q->where('academic_year_id', $ay2026?->id))
        ->count();
    $syahriahCount = Bill::whereHas('student', fn($q) => $q->where('classroom_id', $c->id))
        ->whereHas('billType', fn($q) => $q->where('academic_year_id', $ay2026?->id)->where('name', 'like', '%SYAHR%'))
        ->count();
    $out[] = "  Class {$c->name}: Active Students = {$stCount} | Total 2026/2027 Bills = {$billCount} | Syahriah Bills = {$syahriahCount}";
}

file_put_contents(__DIR__ . '/audit_output.txt', implode("\n", $out));
echo "Audit file saved to audit_output.txt\n";
