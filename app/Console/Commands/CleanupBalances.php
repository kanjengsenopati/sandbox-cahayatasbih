<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Student;

class CleanupBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:cleanup-balances {--dry-run : Only simulate changes without writing to database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate student profiles, cast decimal fields to integer, and recalculate chronological running balances for students';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->info("=== RUNNING IN DRY-RUN MODE (NO DATABASE WRITE) ===");
        }

        DB::beginTransaction();
        try {
            // 1. DEDUPLICATE STUDENTS
            $this->info("1. Checking for duplicate student profiles...");
            $duplicates = DB::table('students')
                ->whereNull('deleted_at')
                ->select('name', DB::raw('COUNT(*) as count'))
                ->groupBy('name')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            $this->info("Found " . $duplicates->count() . " duplicate student name groups.");

            foreach ($duplicates as $dup) {
                $students = Student::where('name', $dup->name)
                    ->whereNull('deleted_at')
                    ->get();

                // Sort students by transaction and activity count descending to select the primary profile (target)
                $students = $students->sortByDesc(function ($s) {
                    $billCount = DB::table('bills')->where('student_id', $s->id)->count();
                    $txCount = DB::table('transactions')->where('student_id', $s->id)->count();
                    $historyCount = DB::table('saldo_histories')->where('student_id', $s->id)->count();
                    return $billCount + $txCount + $historyCount;
                })->values();

                $target = $students[0];
                $sources = $students->slice(1);

                $this->info("Merging student '{$dup->name}':");
                $this->info("  -> Target (Primary): ID {$target->id} | Saldo: {$target->saldo} | Tabungan: {$target->saving}");

                foreach ($sources as $source) {
                    $this->info("  <- Source (Duplicate): ID {$source->id} | Saldo: {$source->saldo} | Tabungan: {$source->saving}");

                    if (!$dryRun) {
                        // Relink all standard related tables
                        $tablesToUpdate = [
                            'bills' => 'student_id',
                            'transactions' => 'student_id',
                            'saldo_histories' => 'student_id',
                            'saving_histories' => 'student_id',
                            'tahfidzs' => 'student_id',
                            'student_card_prints' => 'student_id',
                            'student_classroom_histories' => 'student_id',
                            'student_bill_notifications' => 'student_id',
                            'point_of_sale_transactions' => 'student_id',
                            'student_achievements' => 'student_id',
                            'student_counseling_scores' => 'student_id',
                            'study_grades' => 'student_id'
                        ];

                        foreach ($tablesToUpdate as $table => $column) {
                            if (Schema::hasTable($table)) {
                                DB::table($table)->where($column, $source->id)->update([$column => $target->id]);
                            }
                        }

                        // Relink polymorphic relations
                        if (Schema::hasTable('attendances')) {
                            DB::table('attendances')
                                ->where('presensiable_id', $source->id)
                                ->where('presensiable_type', Student::class)
                                ->update(['presensiable_id' => $target->id]);
                        }

                        if (Schema::hasTable('biometric_mappings')) {
                            DB::table('biometric_mappings')
                                ->where('presensiable_id', $source->id)
                                ->where('presensiable_type', Student::class)
                                ->update(['presensiable_id' => $target->id]);
                        }

                        // Accumulate balances
                        $target->saldo = (int)round($target->saldo) + (int)round($source->saldo);
                        $target->saving = (int)round($target->saving) + (int)round($source->saving);
                        $target->save();

                        // Soft delete the duplicate profile
                        $source->delete();
                        $this->info("  Status: Merged successfully.");
                    } else {
                        $this->info("  Status: Simulating merge.");
                    }
                }
            }

            // 2. RECALCULATE RUNNING BALANCES FOR ALL ACTIVE STUDENTS
            $this->info("\n2. Recalculating running balances for active students...");
            $activeStudents = Student::whereNull('deleted_at')->get();

            foreach ($activeStudents as $student) {
                // Clear any fractional parts from student profile balances
                $studentSaldo = (int)round($student->saldo);
                $studentSaving = (int)round($student->saving);

                if (!$dryRun) {
                    $student->update([
                        'saldo' => $studentSaldo,
                        'saving' => $studentSaving
                    ]);
                }

                // A. Recalculate Saldo Histories
                $histories = DB::table('saldo_histories')
                    ->where('student_id', $student->id)
                    ->where('status', 'SUCCESS')
                    ->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                if ($histories->isNotEmpty()) {
                    $first = $histories->first();
                    $runningSaldo = (int)round($first->balance_before);
                    $this->info("Student '{$student->name}' ({$student->id}):");
                    $this->info("  - Recalculating " . $histories->count() . " saldo history records. Initial base balance: {$runningSaldo}");

                    foreach ($histories as $h) {
                        $before = $runningSaldo;
                        $amount = (int)round($h->amount);

                        if ($h->type === 'IN') {
                            $after = $before + $amount;
                        } else {
                            $after = $before - $amount;
                        }

                        if (!$dryRun) {
                            DB::table('saldo_histories')->where('id', $h->id)->update([
                                'balance_before' => $before,
                                'balance_after' => $after,
                                'amount' => $amount
                            ]);
                        }

                        $runningSaldo = $after;
                    }

                    $this->info("  - Final computed saldo: {$runningSaldo} (Profile was: {$studentSaldo})");
                    if (!$dryRun) {
                        DB::table('students')->where('id', $student->id)->update(['saldo' => $runningSaldo]);
                    }
                }

                // B. Clean up and verify Saving Histories
                $savingHistories = DB::table('saving_histories')
                    ->where('student_id', $student->id)
                    ->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                if ($savingHistories->isNotEmpty()) {
                    $runningSaving = 0;
                    foreach ($savingHistories as $sh) {
                        $amount = (int)round($sh->amount);
                        if ($sh->type === 'IN') {
                            $runningSaving += $amount;
                        } else {
                            $runningSaving -= $amount;
                        }

                        if (!$dryRun) {
                            DB::table('saving_histories')->where('id', $sh->id)->update(['amount' => $amount]);
                        }
                    }

                    $this->info("  - Final computed saving: {$runningSaving} (Profile was: {$studentSaving})");
                    if (!$dryRun) {
                        DB::table('students')->where('id', $student->id)->update(['saving' => $runningSaving]);
                    }
                }
            }

            DB::commit();
            $this->info("\nDatabase balance cleanup completed successfully.");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("\nDatabase balance cleanup failed: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
