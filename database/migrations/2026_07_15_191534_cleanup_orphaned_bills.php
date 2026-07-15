<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Soft delete active bills where the student does not exist in the database at all
        $nonExistentStudentCount = DB::table('bills')
            ->leftJoin('students', 'bills.student_id', '=', 'students.id')
            ->whereNull('students.id')
            ->whereNull('bills.deleted_at')
            ->count();

        if ($nonExistentStudentCount > 0) {
            // We use a subquery to find bill IDs to avoid multi-table update syntax issues across different databases
            $nonExistentBillIds = DB::table('bills')
                ->leftJoin('students', 'bills.student_id', '=', 'students.id')
                ->whereNull('students.id')
                ->whereNull('bills.deleted_at')
                ->pluck('bills.id');

            DB::table('bills')
                ->whereIn('id', $nonExistentBillIds)
                ->update(['deleted_at' => now()]);
        }

        // 2. Soft delete active bills where the student is soft-deleted in the database
        $deletedStudentCount = DB::table('bills')
            ->join('students', 'bills.student_id', '=', 'students.id')
            ->whereNotNull('students.deleted_at')
            ->whereNull('bills.deleted_at')
            ->count();

        if ($deletedStudentCount > 0) {
            $deletedStudentBillIds = DB::table('bills')
                ->join('students', 'bills.student_id', '=', 'students.id')
                ->whereNotNull('students.deleted_at')
                ->whereNull('bills.deleted_at')
                ->pluck('bills.id');

            DB::table('bills')
                ->whereIn('id', $deletedStudentBillIds)
                ->update(['deleted_at' => now()]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: soft deletes cannot be easily reversed without risking restoring bills that were deleted before this migration
    }
};

