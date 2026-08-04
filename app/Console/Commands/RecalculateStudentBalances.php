<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SaldoRecalculatorService;

class RecalculateStudentBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saldo:recalculate {student_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate running balance and total saldo for a student or all students';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $studentId = $this->argument('student_id');

        if ($studentId) {
            $this->info("Recalculating saldo running balance for student ID: {$studentId}...");
            $success = SaldoRecalculatorService::recalculateForStudent($studentId);
            if ($success) {
                $this->info("Successfully recalculated saldo for student ID: {$studentId}.");
            } else {
                $this->error("Student ID: {$studentId} not found.");
            }
        } else {
            $this->info("Recalculating saldo running balance for ALL students...");
            $count = SaldoRecalculatorService::recalculateAllStudents();
            $this->info("Successfully recalculated saldo for {$count} students.");
        }

        return 0;
    }
}
