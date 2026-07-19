<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Bill;

$student = Student::where('name', 'LIKE', '%ILLA FAUZILAH%')->first();
if (!$student) {
    echo "Student ILLA FAUZILAH not found.\n";
    exit(1);
}

echo "Student found: ID={$student->id}, Name={$student->name}, Status={$student->status}\n";

$bills = Bill::where('student_id', $student->id)->get();
echo "Total bills count: " . $bills->count() . "\n";

foreach ($bills as $bill) {
    echo "  Bill ID={$bill->id}, Month={$bill->month}, Year={$bill->year}, Amount={$bill->amount}, Status={$bill->status}\n";
}

$currentMonth = (int) date('n');
$currentYear = (int) date('Y');

$hasUnpaidBills = Bill::where('student_id', $student->id)
    ->where('status', Bill::STATUS_UNPAID)
    ->where(function ($q) use ($currentMonth, $currentYear) {
        $q->where('year', '<', $currentYear)
            ->orWhere(function ($q2) use ($currentMonth, $currentYear) {
                $q2->where('year', $currentYear)
                    ->where('month', '<=', $currentMonth);
            });
    })->exists();

echo "hasUnpaidBills evaluated to: " . ($hasUnpaidBills ? "TRUE" : "FALSE") . "\n";
