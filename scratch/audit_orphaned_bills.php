<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bill;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

echo "=== AUDIT OF ORPHANED BILLS ===\n\n";

// 1. Bills where student does not exist at all in the database
$noStudentCount = DB::table('bills')
    ->leftJoin('students', 'bills.student_id', '=', 'students.id')
    ->whereNull('students.id')
    ->whereNull('bills.deleted_at')
    ->count();

echo "1. Bills with non-existent student IDs: {$noStudentCount}\n";

// 2. Bills where student is soft-deleted (deleted_at is NOT NULL on students table)
$deletedStudentCount = DB::table('bills')
    ->join('students', 'bills.student_id', '=', 'students.id')
    ->whereNotNull('students.deleted_at')
    ->whereNull('bills.deleted_at')
    ->count();

echo "2. Bills belonging to soft-deleted students: {$deletedStudentCount}\n";

// 3. Bills where student is marked as "Keluar" (DROPPED_OUT)
$droppedOutStudentBillsQuery = DB::table('bills')
    ->join('students', 'bills.student_id', '=', 'students.id')
    ->where('students.status', Student::STATUS_DROPPED_OUT)
    ->whereNull('students.deleted_at')
    ->whereNull('bills.deleted_at');

$droppedOutStudentBillsTotal = $droppedOutStudentBillsQuery->count();
$droppedOutStudentBillsUnpaid = (clone $droppedOutStudentBillsQuery)->where('bills.status', Bill::STATUS_UNPAID)->count();
$droppedOutStudentBillsPaid = (clone $droppedOutStudentBillsQuery)->where('bills.status', Bill::STATUS_PAID)->count();

echo "3. Bills belonging to 'Keluar' (DROPPED_OUT) students:\n";
echo "   - Total Active Bills: {$droppedOutStudentBillsTotal}\n";
echo "   - Unpaid Bills: {$droppedOutStudentBillsUnpaid}\n";
echo "   - Paid Bills: {$droppedOutStudentBillsPaid}\n";

echo "\n=================================\n";
