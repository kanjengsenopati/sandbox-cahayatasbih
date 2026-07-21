<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Bill;

$student = Student::withTrashed()->where('name', 'like', '%AHMAD NADAV%')->first();
if (!$student) {
    echo "Student with name containing 'AHMAD NADAV' not found\n";
    exit;
}

echo "Student Found:\n";
echo "ID: {$student->id}\n";
echo "NIS: {$student->nis}\n";
echo "Name: {$student->name}\n";
echo "Status: {$student->status}\n";
echo "Classroom ID: {$student->classroom_id}\n";
echo "Classroom Name: " . ($student->classroom?->name ?? 'None') . "\n";
echo "Classroom Academic Year ID: " . ($student->classroom?->academic_year_id ?? 'None') . "\n";
echo "Classroom Academic Year Name: " . ($student->classroom?->academicYear?->name ?? 'None') . "\n";
echo "Created At: {$student->created_at}\n";
echo "Updated At: {$student->updated_at}\n";

$bills = Bill::with(['billType.academicYear'])->where('student_id', $student->id)->get();
echo "\nBills Count: " . $bills->count() . "\n";
foreach ($bills as $bill) {
    $ayName = $bill->billType?->academicYear?->name ?? $bill->academicYear?->name ?? 'N/A';
    echo "- Bill ID: {$bill->id} | Type: {$bill->billType?->name} | Academic Year: {$ayName} | Month/Year: {$bill->month}/{$bill->year} | Amount: {$bill->amount} | Paid: {$bill->paid_amount} | Status: {$bill->status}\n";
}
