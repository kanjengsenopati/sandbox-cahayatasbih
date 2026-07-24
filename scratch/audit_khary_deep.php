<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$student = \App\Models\Student::where('name', 'like', '%KHARY%')
    ->orWhere('name', 'like', '%FAQIH%')
    ->orWhere('nis', 'like', '%332126149%')
    ->first();

if (!$student) {
    echo "Student not found in DB! Searching all students...\n";
    $all = \App\Models\Student::where('name', 'like', '%MUHAMMAD%')->get();
    echo "Found " . $all->count() . " students with MUHAMMAD.\n";
    foreach ($all as $s) {
        if (str_contains(strtoupper($s->name), 'FAQIH') || str_contains(strtoupper($s->name), 'KHARY')) {
            echo "Match: ID {$s->id} | Name: {$s->name} | NIS: {$s->nis}\n";
            $student = $s;
            break;
        }
    }
}

if (!$student) {
    echo "STILL NOT FOUND!\n";
    exit;
}

$out = "=== STUDENT INFO ===\n";
$out .= "ID: {$student->id}\nName: {$student->name}\nNIS: {$student->nis}\nClass ID: {$student->class_id}\nPondok Class ID: " . ($student->pondok_class_id ?? 'null') . "\n";
if ($student->class) {
    $out .= "Class Name: {$student->class->name} | Grade: {$student->class->grade} | School ID: {$student->class->school_id}\n";
}

$out .= "\n=== BILLS FOR KHARY IN DB (tb_bills) ===\n";
$bills = \App\Models\Bill::where('student_id', $student->id)->get();
$out .= "Total bills count in tb_bills: " . $bills->count() . "\n";
foreach ($bills as $b) {
    $out .= "Bill ID: {$b->id} | AY ID: {$b->academic_year_id} | BT ID: {$b->bill_type_id} | Amount: {$b->amount} | Paid: {$b->paid_amount} | Status: {$b->status}\n";
}

$out .= "\n=== ALL BILL TYPES ===\n";
foreach (\App\Models\BillType::with('academicYear')->get() as $bt) {
    $out .= "BT ID: {$bt->id} | Name: {$bt->name} | Type: {$bt->type} | AY: " . ($bt->academicYear->name ?? 'None') . " (ID: {$bt->academic_year_id})\n";
}

file_put_contents(__DIR__ . '/khary_audit_res.txt', $out);
echo "SUCCESS writing khary_audit_res.txt\n";
