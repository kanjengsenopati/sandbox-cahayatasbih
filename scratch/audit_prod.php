<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$student = \App\Models\Student::where('nis', '332126149')
    ->orWhere('name', 'LIKE', '%FAQIH%')
    ->first();

if (!$student) {
    echo "Student FAQIH not found\n";
    exit;
}

echo "=== STUDENT ===\n";
echo "ID: {$student->id}\n";
echo "NIS: {$student->nis}\n";
echo "Name: {$student->name}\n";

$bills = \App\Models\Bill::with(['billType.academicYear', 'academicYear'])
    ->where('student_id', $student->id)
    ->get();

echo "\n=== BILLS (" . $bills->count() . ") ===\n";
foreach ($bills as $b) {
    echo "ID: {$b->id} | Type: {$b->billType?->name} (TypeID: {$b->bill_type_id}) | Month: {$b->month} | Year: {$b->year} | Bill_AY_ID: {$b->academic_year_id} ({$b->academicYear?->name}) | BT_AY_ID: {$b->billType?->academic_year_id} ({$b->billType?->academicYear?->name}) | Amount: {$b->amount} | Paid: {$b->paid_amount}\n";
}
