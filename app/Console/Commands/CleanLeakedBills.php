<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bill;
use App\Models\PaymentRate;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

class CleanLeakedBills extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:clean-leaked {--dry-run : Only show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up leaked bills that were generated for students who did not belong to the target classrooms in past academic years';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting Leaked Bills Cleanup...");
        $isDryRun = $this->option('dry-run');

        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) {
            $this->error("No active academic year found.");
            return;
        }

        // We only care about bills tied to a payment_rate_item
        $billsQuery = Bill::with(['student', 'paymentRateItems.paymentRate.billType'])
            ->whereNotNull('payment_rate_item_id');

        $this->info("Fetching bills in chunks...");

        $leakedBillIds = [];
        $totalPaidLeaked = 0;

        $billsQuery->chunkById(500, function ($bills) use (&$leakedBillIds, &$totalPaidLeaked, $activeYear) {
            foreach ($bills as $bill) {
                $rateItem = $bill->paymentRateItems;
                if (!$rateItem || !$rateItem->paymentRate) continue;

                $paymentRate = $rateItem->paymentRate;
                $billType = $paymentRate->billType;
                if (!$billType || !$billType->academic_year_id) continue;

                // If the bill belongs to the currently active academic year, it's generally valid
                // based on their current classroom.
                if ($billType->academic_year_id == $activeYear->id) {
                    continue;
                }

                // For past academic years:
                $student = $bill->student;
                if (!$student) continue;

                // If this is a student-specific rate, it's explicitly assigned. Not a leak.
                if ($paymentRate->type === PaymentRate::TYPE_STUDENT) {
                    continue;
                }

                // It's a regular rate (classroom based)
                $allowedClassroomIds = DB::table('payment_rate_classrooms')
                    ->where('payment_rate_id', $paymentRate->id)
                    ->pluck('classroom_id')
                    ->toArray();

                $history = DB::table('student_classroom_histories')
                    ->where('student_id', $student->id)
                    ->where('academic_year_id', $billType->academic_year_id)
                    ->whereNull('deleted_at')
                    ->first();

                $isLeaked = false;
                if (!$history) {
                    // No history in that past year -> student wasn't in any tracked classroom that year -> LEAKED
                    $isLeaked = true;
                } else {
                    if (!in_array($history->classroom_id, $allowedClassroomIds)) {
                        // Student was in a classroom, but NOT one of the allowed classrooms for this rate -> LEAKED
                        $isLeaked = true;
                    }
                }

                if ($isLeaked) {
                    $leakedBillIds[] = $bill->id;
                    if ($bill->paid_amount > 0) {
                        $totalPaidLeaked++;
                    }
                }
            }
        });

        $this->info("Found " . count($leakedBillIds) . " leaked bills.");
        if ($totalPaidLeaked > 0) {
            $this->warn("WARNING: {$totalPaidLeaked} of these leaked bills have ALREADY BEEN PAID (partially or fully).");
        }

        if (count($leakedBillIds) > 0) {
            if ($isDryRun) {
                $this->info("DRY RUN: Would have deleted " . count($leakedBillIds) . " leaked bills.");
            } else {
                $this->info("Deleting " . count($leakedBillIds) . " leaked bills...");
                // Chunk the deletion to avoid SQL statement too long
                $chunks = array_chunk($leakedBillIds, 500);
                foreach ($chunks as $chunk) {
                    Bill::whereIn('id', $chunk)->forceDelete();
                }
                $this->info("Successfully deleted leaked bills.");
            }
        } else {
            $this->info("No leaked bills found. System is clean!");
        }
    }
}
