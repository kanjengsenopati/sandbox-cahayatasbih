<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Bill;
use App\Models\BillType;

$student = Student::where('status', 'ACTIVE')->first();
if (!$student) {
    echo "No active student found for testing.\n";
    exit(0);
}

echo "Testing query logic for Student: {$student->name} (ID: {$student->id})\n";

$currentMonth = (int) date('n');
$currentYear = (int) date('Y');

// Test SendNotifWaService query logic
$billType = BillType::first();
if ($billType) {
    echo "Testing query on BillType: {$billType->name}\n";
    $unpaidBills = $billType->bills()
        ->where('student_id', $student->id)
        ->where('status', Bill::STATUS_UNPAID)
        ->where(function ($q) use ($currentMonth, $currentYear) {
            $q->where('year', '<', $currentYear)
                ->orWhere(function ($q2) use ($currentMonth, $currentYear) {
                    $q2->where('year', $currentYear)
                        ->where('month', '<=', $currentMonth);
                });
        })
        ->orderByRaw('CONCAT(year, LPAD(month, 2, "0")) ASC')
        ->get();

    echo "Query executed successfully. Found " . $unpaidBills->count() . " unpaid bills.\n";
}

// Test DashboardController has_unpaid_bills logic
$hasUnpaidBills = \App\Models\Bill::where('student_id', $student->id)
    ->where('status', \App\Models\Bill::STATUS_UNPAID)
    ->where(function ($q) use ($currentMonth, $currentYear) {
        $q->where('year', '<', $currentYear)
            ->orWhere(function ($q2) use ($currentMonth, $currentYear) {
                $q2->where('year', $currentYear)
                    ->where('month', '<=', $currentMonth);
            });
    })->exists();

echo "has_unpaid_bills status: " . ($hasUnpaidBills ? "TRUE" : "FALSE") . "\n";
echo "Verification complete!\n";
