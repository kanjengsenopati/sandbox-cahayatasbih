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
    protected $signature = 'db:cleanup-balances {--dry-run : Only simulate changes without writing to database} {--draft : Generate draft audit simulation table}';

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
        $isDraft = $this->option('draft');

        if ($isDraft) {
            $this->info("=== GENERATING DRAFT AUDIT SIMULATION (NO DATABASE WRITE TO STUDENTS) ===");
            $service = new \App\Services\SaldoSimulationService();
            $result = $service->runSimulation();
            $this->info("Draft simulation completed successfully.");
            $this->info("  - Processed: {$result['total_processed']} students");
            $this->info("  - Mismatches: {$result['total_mismatches']} students");
            $this->info("  - Negatives: {$result['total_negatives']} students");
            $this->info("View details in Admin Panel: /admin/audit/simulation");
            return 0;
        }

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
                        // Relink all standard related tables (excluding bills)
                        $tablesToUpdate = [
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

                        // Relink polymorphic relations (excluding biometric_mappings)
                        if (Schema::hasTable('attendances')) {
                            DB::table('attendances')
                                ->where('presensiable_id', $source->id)
                                ->where('presensiable_type', Student::class)
                                ->update(['presensiable_id' => $target->id]);
                        }

                        // Safely merge bills with unique constraints handled
                        $this->mergeBills($source->id, $target->id, $dryRun);

                        // Safely merge biometric mappings with unique constraints handled
                        $this->mergeBiometricMappings($source->id, $target->id, $dryRun);

                        // Accumulate balances
                        $target->saldo = (int)round($target->saldo) + (int)round($source->saldo);
                        $target->saving = (int)round($target->saving) + (int)round($source->saving);
                        $target->save();

                        // Soft delete the duplicate profile
                        $source->delete();
                        $this->info("  Status: Merged successfully.");
                    } else {
                        // Simulate merging bills and biometrics
                        $this->mergeBills($source->id, $target->id, $dryRun);
                        $this->mergeBiometricMappings($source->id, $target->id, $dryRun);
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

    /**
     * Safely merge bills between source and target students.
     */
    private function mergeBills($sourceId, $targetId, $dryRun)
    {
        if (!Schema::hasTable('bills')) return;

        $sourceBills = DB::table('bills')->where('student_id', $sourceId)->whereNull('deleted_at')->get();
        foreach ($sourceBills as $sb) {
            $targetBill = DB::table('bills')
                ->where('student_id', $targetId)
                ->where('bill_type_id', $sb->bill_type_id)
                ->where('academic_year_id', $sb->academic_year_id)
                ->where('month', $sb->month)
                ->where('year', $sb->year)
                ->whereNull('deleted_at')
                ->first();

            if ($targetBill) {
                $this->info("    - Bill conflict found for type {$sb->bill_type_id}, month {$sb->month}/{$sb->year}:");
                $this->info("      Merging source bill ID {$sb->id} (paid: {$sb->paid_amount}) into target bill ID {$targetBill->id} (paid: {$targetBill->paid_amount})");

                if (!$dryRun) {
                    // Relink transaction details (payments) to the target bill
                    DB::table('transaction_details')->where('bill_id', $sb->id)->update(['bill_id' => $targetBill->id]);

                    // Accumulate paid amount and update status
                    $newPaid = (int)round($targetBill->paid_amount) + (int)round($sb->paid_amount);
                    $newStatus = ($newPaid >= $targetBill->amount) ? 'PAID' : 'UNPAID';
                    DB::table('bills')->where('id', $targetBill->id)->update([
                        'paid_amount' => min($targetBill->amount, $newPaid),
                        'status' => $newStatus
                    ]);

                    // Soft-delete the duplicate bill to resolve the unique active record constraint
                    DB::table('bills')->where('id', $sb->id)->update([
                        'deleted_at' => now()
                    ]);
                }
            } else {
                // No conflict, safe to transfer
                if (!$dryRun) {
                    DB::table('bills')->where('id', $sb->id)->update(['student_id' => $targetId]);
                }
            }
        }

        // Also update any soft-deleted source bills to point to target student ID
        if (!$dryRun) {
            DB::table('bills')->where('student_id', $sourceId)->whereNotNull('deleted_at')->update(['student_id' => $targetId]);
        }
    }

    /**
     * Safely merge biometric mappings between source and target students.
     */
    private function mergeBiometricMappings($sourceId, $targetId, $dryRun)
    {
        if (!Schema::hasTable('biometric_mappings')) return;

        $sourceMappings = DB::table('biometric_mappings')
            ->where('presensiable_id', $sourceId)
            ->where('presensiable_type', Student::class)
            ->get();

        foreach ($sourceMappings as $sm) {
            $targetMapping = DB::table('biometric_mappings')
                ->where('presensiable_id', $targetId)
                ->where('presensiable_type', Student::class)
                ->where('biometric_type', $sm->biometric_type)
                ->where('biometric_index', $sm->biometric_index)
                ->first();

            if ($targetMapping) {
                $this->info("    - Biometric mapping conflict found for type {$sm->biometric_type}, index {$sm->biometric_index}:");
                $this->info("      Deleting source mapping ID {$sm->id} since target already has one.");
                if (!$dryRun) {
                    DB::table('biometric_mappings')->where('id', $sm->id)->delete();
                }
            } else {
                // No conflict, safe to transfer
                if (!$dryRun) {
                    DB::table('biometric_mappings')->where('id', $sm->id)->update([
                        'presensiable_id' => $targetId
                    ]);
                }
            }
        }
    }
}
