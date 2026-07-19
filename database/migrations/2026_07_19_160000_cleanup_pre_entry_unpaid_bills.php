<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Soft-deletes existing UNPAID bills for academic years prior to the student's entry year.
     */
    public function up(): void
    {
        $deletedCount = 0;

        // Fetch academic years to resolve their start years
        $academicYears = DB::table('academic_years')->get()->keyBy('id');
        $ayStartYears = [];
        foreach ($academicYears as $id => $ay) {
            $startYear = $ay->start_year;
            if (!$startYear && $ay->name) {
                $parts = explode('/', $ay->name);
                if (count($parts) > 0 && is_numeric($parts[0])) {
                    $startYear = intval($parts[0]);
                }
            }
            $ayStartYears[$id] = $startYear;
        }

        // Fetch all students
        $students = DB::table('students')->select('id', 'nis', 'created_at')->get();

        foreach ($students as $student) {
            // Determine entry year
            $entryYear = null;
            if ($student->nis) {
                $cleanNis = preg_replace('/\D/', '', $student->nis);
                if (strlen($cleanNis) == 9) {
                    $yearPart = substr($cleanNis, 4, 2);
                    if (is_numeric($yearPart)) {
                        $entryYear = 2000 + intval($yearPart);
                    }
                }
            }

            if (!$entryYear) {
                // Try from classroom history
                $firstHistoryYear = DB::table('student_classroom_histories')
                    ->join('academic_years', 'student_classroom_histories.academic_year_id', '=', 'academic_years.id')
                    ->where('student_classroom_histories.student_id', $student->id)
                    ->orderBy('academic_years.start_year', 'asc')
                    ->value('academic_years.start_year');

                if ($firstHistoryYear) {
                    $entryYear = intval($firstHistoryYear);
                }
            }

            if (!$entryYear && $student->created_at) {
                $entryYear = Carbon::parse($student->created_at)->year;
            }

            if ($entryYear) {
                // Find bills for this student that are UNPAID and for an academic year starting before entryYear
                $bills = DB::table('bills')
                    ->where('student_id', $student->id)
                    ->where('status', 'UNPAID')
                    ->whereNull('deleted_at')
                    ->get();

                foreach ($bills as $bill) {
                    $billAyStartYear = $ayStartYears[$bill->academic_year_id] ?? null;
                    if ($billAyStartYear !== null && $billAyStartYear < $entryYear) {
                        DB::table('bills')
                            ->where('id', $bill->id)
                            ->update([
                                'deleted_at' => now(),
                                'updated_at' => now()
                            ]);
                        $deletedCount++;
                    }
                }
            }
        }

        Log::info("Migration cleanup_pre_entry_unpaid_bills: Soft-deleted {$deletedCount} pre-entry unpaid bills.");
        dump("Migration cleanup_pre_entry_unpaid_bills: Soft-deleted {$deletedCount} pre-entry unpaid bills.");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data repair migration - no rollback needed
    }
};
