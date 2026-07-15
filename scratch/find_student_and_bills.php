<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Bill;

$student = Student::withTrashed()->where('name', 'like', '%ABDUL RAHMAN%')->first();
if (!$student) {
    echo "Student not found\n";
    exit;
}

echo "Student Found:\n";
echo "ID: {$student->id}\n";
echo "NIS: {$student->nis}\n";
echo "Name: {$student->name}\n";
echo "Status: {$student->status} ({$student->translated_status})\n";
echo "Deleted At: " . ($student->deleted_at ?: 'Active (Not Deleted)') . "\n";

$bills = Bill::where('student_id', $student->id)->get();
echo "\nBills Count: " . $bills->count() . "\n";
foreach ($bills as $bill) {
    echo "- Bill ID: {$bill->id} | Month/Year: {$bill->month}/{$bill->year} | Amount: {$bill->amount} | Paid: {$bill->paid_amount} | Status: {$bill->status} | Deleted At: " . ($bill->deleted_at ?: 'No') . "\n";
}
