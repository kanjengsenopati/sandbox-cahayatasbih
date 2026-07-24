<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$student = \App\Models\Student::where('nis', '332126149')
    ->orWhere('name', 'like', '%KHARY%')
    ->first();

if (!$student) {
    echo "Student not found!\n";
    exit;
}

echo "=== STUDENT ===\n";
echo "ID: " . $student->id . "\n";
echo "Name: " . $student->name . "\n";
echo "NIS: " . $student->nis . "\n";
echo "Gender: " . $student->gender . "\n";
echo "Class ID: " . $student->class_id . " / Pondok Class ID: " . ($student->pondok_class_id ?? 'null') . "\n";

if ($student->class) {
    echo "Class Name: " . $student->class->name . " (Grade: " . $student->class->grade . ", Major: " . $student->class->major . ", School ID: " . $student->class->school_id . ")\n";
}

echo "\n=== BILLS (tb_bills / Bill) ===\n";
$bills = \App\Models\Bill::where('student_id', $student->id)->get();
foreach ($bills as $b) {
    echo "Bill ID: {$b->id} | Year ID: {$b->academic_year_id} | Type ID: {$b->bill_type_id} | Amount: {$b->amount} | Paid: {$b->paid_amount} | Status: {$b->status}\n";
}

echo "\n=== BILL ITEMS / DETAIL ===\n";
if (class_exists('\App\Models\BillItem')) {
    $items = \App\Models\BillItem::where('student_id', $student->id)->get();
    foreach ($items as $bi) {
        echo "BillItem ID: {$bi->id} | Bill ID: {$bi->bill_id} | Amount: {$bi->amount} | Paid: {$bi->paid_amount}\n";
    }
}

echo "\n=== STUDENT RATES / PAYMENT RATES ===\n";
// Check tables for payment rates or rates
$tables = \DB::select("SHOW TABLES");
foreach ($tables as $t) {
    $tname = array_values((array)$t)[0];
    if (str_contains($tname, 'rate') || str_contains($tname, 'tarif') || str_contains($tname, 'tagihan')) {
        echo "Table: $tname\n";
    }
}

echo "\n=== ACADEMIC YEARS ===\n";
$academicYears = \App\Models\AcademicYear::all();
foreach ($academicYears as $ay) {
    echo "AY ID: {$ay->id} | Name: {$ay->name} | Active: {$ay->is_active}\n";
}

echo "\n=== TRANSACTIONS ===\n";
$txs = \App\Models\Transaction::where('student_id', $student->id)->get();
foreach ($txs as $tx) {
    echo "TX ID: {$tx->id} | Amount: {$tx->pay_amount} | Status: {$tx->status} | Code: {$tx->code}\n";
}
