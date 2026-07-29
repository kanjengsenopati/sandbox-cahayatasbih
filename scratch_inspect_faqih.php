<?php
require __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/app/Helpers/helpers.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Student;
use App\Models\Bill;
use App\Models\BillType;
use App\Models\Transaction;

$student = Student::with(['classroom.school', 'user'])->where('nis', '332126149')->orWhere('name', 'like', '%FAQIH%')->first();

if (!$student) {
    echo "Student FAQIH not found in current environment DB!\n";
    exit;
}

echo "========================================================\n";
echo "STUDENT DETAIL:\n";
echo "ID: {$student->id}\n";
echo "NIS: {$student->nis}\n";
echo "Name: {$student->name}\n";
echo "Classroom: " . ($student->classroom?->name ?? 'N/A') . " (ID: {$student->classroom_id})\n";
echo "School Unit: " . ($student->classroom?->school?->name ?? 'N/A') . " (ID: {$student->classroom?->school_id})\n";
echo "Gender: {$student->gender}\n";
echo "Jamaah Status: " . ($student->user?->jamaah_status ?? 'NON_JAMAAH') . "\n";

echo "\n--- ALL BILLS FOR THIS STUDENT ---\n";
$bills = Bill::with(['billType.billItem', 'billType.academicYear', 'academicYear'])
    ->where('student_id', $student->id)
    ->whereNull('deleted_at')
    ->get();

echo "Total active bills count: " . $bills->count() . "\n";
$billsGrouped = $bills->groupBy(fn($b) => $b->billType?->name ?? 'UNKNOWN');

foreach ($billsGrouped as $btName => $bGroup) {
    echo "\nBillType: {$btName} (Count: {$bGroup->count()})\n";
    foreach ($bGroup as $b) {
        echo "  - Bill ID: {$b->id} | AY: {$b->academicYear?->name} | Month: {$b->month} | Year: {$b->year} | Nominal: {$b->amount} | Paid: {$b->paid_amount} | Status: {$b->status} | BillTypeID: {$b->bill_type_id}\n";
    }
}

echo "\n--- ALL TRANSACTIONS FOR THIS STUDENT ---\n";
$txs = Transaction::with(['transactionDetails.bill.billType'])
    ->where('student_id', $student->id)
    ->latest()
    ->get();

echo "Total transactions count: " . $txs->count() . "\n";
foreach ($txs as $tx) {
    echo "Tx ID: {$tx->id} | Type: {$tx->type} | Amount: {$tx->pay_amount} | Status: {$tx->status} | Created: {$tx->created_at}\n";
    foreach ($tx->transactionDetails as $td) {
        $b = $td->bill;
        echo "   -> Detail ID: {$td->id} | BillID: {$td->bill_id} | Amount: {$td->amount} | BillType: " . ($b?->billType?->name ?? 'N/A') . " | Month: " . ($b?->month ?? 'N/A') . "\n";
    }
}
