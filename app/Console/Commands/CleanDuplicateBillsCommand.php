<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\Bill;
use App\Models\PaymentRate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CleanDuplicateBillsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:clean-duplicates
                            {--dry-run : Perform a dry run without modifying the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean and merge duplicate bills safely, moving transaction records to the primary bill and normalizing academic calendar years.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('=== DRY RUN MODE: No actual database changes will be saved ===');
        }

        $this->info('Starting Safe Duplicate Bills Cleanup & Transaction Healing...');

        // Step 1: Soft-delete known faulty rates
        $faultyRateIds = [
            '9d04027f-eb6c-4f00-a25a-8f0ce25533ef', // APLIKASI CT - MA (wrong years 2026-2027, 0 classrooms)
            '22b13728-2c1e-47e5-bb84-d0a0a24d2f99', // BIAYA APLIKASI CT - SMP (wrong years 2025-2026, KELASDEMO)
        ];

        $deletedRatesCount = 0;
        foreach ($faultyRateIds as $rateId) {
            $rate = PaymentRate::find($rateId);
            if ($rate) {
                $this->warn("Soft-deleting faulty PaymentRate: {$rateId} ({$rate->name})");
                if (!$isDryRun) {
                    $rate->delete();
                }
                $deletedRatesCount++;
            }
        }
        $this->info("Faulty PaymentRates soft-deleted: {$deletedRatesCount}");

        // Step 2: Fix year typo on payment_rate_items
        $wrongItems = DB::select("
            SELECT pri.id, pri.month, pri.year,
                   CASE WHEN pri.month >= 7 THEN ay.start_year ELSE ay.end_year END as expected_year
            FROM payment_rate_items pri
            JOIN payment_rates pr ON pr.id = pri.payment_rate_id
            JOIN bill_types bt ON bt.id = pr.bill_type_id
            JOIN academic_years ay ON ay.id = bt.academic_year_id
            WHERE pri.year != (CASE WHEN pri.month >= 7 THEN ay.start_year ELSE ay.end_year END)
        ");

        $this->info("Found " . count($wrongItems) . " payment_rate_items with incorrect year. Normalizing...");
        if (!$isDryRun && count($wrongItems) > 0) {
            DB::statement("
                UPDATE payment_rate_items pri
                JOIN payment_rates pr ON pr.id = pri.payment_rate_id
                JOIN bill_types bt ON bt.id = pr.bill_type_id
                JOIN academic_years ay ON ay.id = bt.academic_year_id
                SET pri.year = (CASE WHEN pri.month >= 7 THEN ay.start_year ELSE ay.end_year END)
                WHERE pri.year != (CASE WHEN pri.month >= 7 THEN ay.start_year ELSE ay.end_year END)
            ");
            $this->info("Payment rate items successfully normalized.");
        }

        // Step 3: Scan all active duplicate bill groups
        $dupeGroups = DB::select("
            SELECT student_id, bill_type_id, academic_year_id, month, COUNT(*) as cnt
            FROM bills
            WHERE deleted_at IS NULL
            GROUP BY student_id, bill_type_id, academic_year_id, month
            HAVING COUNT(*) > 1
        ");

        $this->info("Found " . count($dupeGroups) . " duplicate groups to process.");

        $totalSoftDeletedBills = 0;
        $totalMovedTxDetails = 0;
        $totalUpdatedYears = 0;
        $totalRecalculatedBills = 0;
        $case1Count = 0;
        $case2Count = 0;
        $case3Count = 0;

        $academicYearMap = AcademicYear::withTrashed()->get()->keyBy('id');

        $actionExecution = function () use (
            $dupeGroups,
            $academicYearMap,
            $isDryRun,
            &$totalSoftDeletedBills,
            &$totalMovedTxDetails,
            &$totalUpdatedYears,
            &$totalRecalculatedBills,
            &$case1Count,
            &$case2Count,
            &$case3Count
        ) {
            $now = now();

            foreach ($dupeGroups as $dg) {
                $bills = DB::table('bills')
                    ->where('student_id', $dg->student_id)
                    ->where('bill_type_id', $dg->bill_type_id)
                    ->where('academic_year_id', $dg->academic_year_id)
                    ->where('month', $dg->month)
                    ->whereNull('deleted_at')
                    ->orderBy('created_at')
                    ->get();

                $ay = $academicYearMap->get($dg->academic_year_id);
                $startYear = $ay ? (int) $ay->start_year : null;
                $endYear = $ay ? (int) $ay->end_year : null;
                $validYear = ($startYear && $endYear)
                    ? (((int) $dg->month >= 7) ? $startYear : $endYear)
                    : null;

                $paidBills = [];
                $unpaidBills = [];

                foreach ($bills as $b) {
                    $hasTx = DB::table('transaction_details')->where('bill_id', $b->id)->exists();
                    if ($b->paid_amount > 0 || $hasTx) {
                        $paidBills[] = $b;
                    } else {
                        $unpaidBills[] = $b;
                    }
                }

                if (count($paidBills) > 1) {
                    // CASE 1: Multiple paid bills (e.g. Fatimah, Sarah, Siti)
                    $case1Count++;

                    // Choose target bill: the one with validYear, or the first one
                    $target = null;
                    if ($validYear) {
                        foreach ($paidBills as $pb) {
                            if ($pb->year == $validYear) {
                                $target = $pb;
                                break;
                            }
                        }
                    }
                    if (!$target) {
                        $target = $paidBills[0];
                    }

                    // 1. Move transaction details and soft-delete duplicate bills FIRST
                    foreach ($bills as $b) {
                        if ($b->id === $target->id) {
                            continue;
                        }

                        $txDetails = DB::table('transaction_details')->where('bill_id', $b->id)->get();
                        if ($txDetails->count() > 0) {
                            if (!$isDryRun) {
                                DB::table('transaction_details')
                                    ->where('bill_id', $b->id)
                                    ->update(['bill_id' => $target->id]);
                            }
                            $totalMovedTxDetails += $txDetails->count();
                        }

                        // Soft-delete the duplicate bill
                        if (!$isDryRun) {
                            DB::table('bills')->where('id', $b->id)->update(['deleted_at' => $now]);
                        }
                        $totalSoftDeletedBills++;
                    }

                    // 2. Recalculate target bill's paid_amount, status, and year
                    if (!$isDryRun) {
                        $totalPaidFromTx = DB::table('transaction_details')
                            ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
                            ->where('transaction_details.bill_id', $target->id)
                            ->where('transactions.status', 'PAID')
                            ->sum('transaction_details.amount');

                        $newStatus = ($totalPaidFromTx >= $target->amount && $target->amount > 0)
                            ? Bill::STATUS_PAID
                            : ($totalPaidFromTx > 0 ? Bill::STATUS_PARTIAL : Bill::STATUS_UNPAID);

                        $updateTarget = [
                            'paid_amount' => $totalPaidFromTx,
                            'status'      => $newStatus,
                            'updated_at'  => $now,
                        ];

                        if ($validYear && $target->year != $validYear) {
                            $updateTarget['year'] = $validYear;
                            $totalUpdatedYears++;
                        }

                        DB::table('bills')->where('id', $target->id)->update($updateTarget);
                    }
                    $totalRecalculatedBills++;

                } elseif (count($paidBills) === 1) {
                    // CASE 2: Exactly one paid bill
                    $case2Count++;
                    $target = $paidBills[0];

                    // 1. Soft-delete all unpaid duplicates FIRST
                    foreach ($unpaidBills as $ub) {
                        if (!$isDryRun) {
                            DB::table('bills')->where('id', $ub->id)->update(['deleted_at' => $now]);
                        }
                        $totalSoftDeletedBills++;
                    }

                    // 2. Now that duplicates are soft-deleted, safely update target year
                    if ($validYear && $target->year != $validYear) {
                        if (!$isDryRun) {
                            DB::table('bills')->where('id', $target->id)->update([
                                'year'       => $validYear,
                                'updated_at' => $now,
                            ]);
                        }
                        $totalUpdatedYears++;
                    }

                } else {
                    // CASE 3: None paid
                    $case3Count++;

                    // Choose target bill: the one with validYear, or the first one
                    $target = null;
                    if ($validYear) {
                        foreach ($unpaidBills as $ub) {
                            if ($ub->year == $validYear) {
                                $target = $ub;
                                break;
                            }
                        }
                    }
                    if (!$target) {
                        $target = $unpaidBills[0];
                    }

                    // 1. Soft-delete the duplicate bill(s) FIRST
                    foreach ($unpaidBills as $ub) {
                        if ($ub->id === $target->id) {
                            continue;
                        }
                        if (!$isDryRun) {
                            DB::table('bills')->where('id', $ub->id)->update(['deleted_at' => $now]);
                        }
                        $totalSoftDeletedBills++;
                    }

                    // 2. Now update target bill year if needed
                    if ($validYear && $target->year != $validYear) {
                        if (!$isDryRun) {
                            DB::table('bills')->where('id', $target->id)->update([
                                'year'       => $validYear,
                                'updated_at' => $now,
                            ]);
                        }
                        $totalUpdatedYears++;
                    }
                }
            }
        };

        if ($isDryRun) {
            $actionExecution();
        } else {
            DB::transaction(function () use ($actionExecution) {
                $actionExecution();
            });
            Cache::forget('preloaded_payment_rates');
        }

        $this->line('----------------------------------------------------');
        $this->info("Summary of Duplicate Cleanup:");
        $this->line("  Case 1 (Both/Multiple Paid)  : {$case1Count} groups");
        $this->line("  Case 2 (Single Paid)         : {$case2Count} groups");
        $this->line("  Case 3 (None Paid)           : {$case3Count} groups");
        $this->line("  Duplicate Bills Soft-Deleted : {$totalSoftDeletedBills}");
        $this->line("  Transaction Details Moved    : {$totalMovedTxDetails}");
        $this->line("  Bill Years Normalized        : {$totalUpdatedYears}");
        $this->line("  Paid Bills Recalculated      : {$totalRecalculatedBills}");

        // Step 4: Verification
        if (!$isDryRun) {
            $remaining = DB::select("
                SELECT student_id, bill_type_id, academic_year_id, month, COUNT(*) as cnt
                FROM bills
                WHERE deleted_at IS NULL
                GROUP BY student_id, bill_type_id, academic_year_id, month
                HAVING COUNT(*) > 1
            ");

            if (count($remaining) === 0) {
                $this->info("SUCCESS: 0 duplicate bills remaining in the entire database!");
            } else {
                $this->error("WARNING: " . count($remaining) . " duplicate groups still exist!");
            }
        }

        return Command::SUCCESS;
    }
}
