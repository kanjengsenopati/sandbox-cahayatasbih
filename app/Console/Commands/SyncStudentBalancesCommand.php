<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\SaldoHistory;
use Illuminate\Support\Facades\DB;

class SyncStudentBalancesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saldo:sync-student-balances {--student_id= : Sync specific student ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile and synchronize student.saldo with latest saldo_histories.balance_after';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $studentId = $this->option('student_id');

        $query = Student::query();
        if ($studentId) {
            $query->where('id', $studentId);
        }

        $students = $query->get();
        $syncedCount = 0;
        $fixedCount = 0;

        $this->info("Auditing balance sync for {$students->count()} students...");

        foreach ($students as $student) {
            $latestHistory = SaldoHistory::where('student_id', $student->id)
                ->whereNotIn('status', [SaldoHistory::STATUS_FAILED])
                ->latest()
                ->first();

            if ($latestHistory && $latestHistory->balance_after !== null) {
                $targetSaldo = (int)$latestHistory->balance_after;
                if ((int)$student->saldo !== $targetSaldo) {
                    $oldSaldo = $student->saldo;
                    DB::transaction(function () use ($student, $targetSaldo) {
                        $student->saldo = $targetSaldo;
                        $student->saveQuietly();
                    });
                    $fixedCount++;
                    $this->line("Synced [{$student->name}] (ID: {$student->id}): Old Saldo = {$oldSaldo} -> New Saldo = {$targetSaldo}");
                }
            }
            $syncedCount++;
        }

        $this->info("Synchronization complete! Checked {$syncedCount} students. Corrected {$fixedCount} student balance discrepancies.");
        return 0;
    }
}
