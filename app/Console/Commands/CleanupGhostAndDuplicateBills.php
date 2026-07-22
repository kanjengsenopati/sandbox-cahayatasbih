<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\Bill;
use App\Models\BillType;
use App\Models\Classroom;
use App\Models\PaymentRate;
use App\Models\PaymentRateItem;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentClassroomHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CleanupGhostAndDuplicateBills extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:cleanup-ghost-duplicates
                            {--dry-run : Perform a dry run without making any actual database changes}
                            {--nis= : Target a specific student by NIS}
                            {--school= : Target a specific school ID or code (e.g. MA)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit and clean up ghost bills, duplicate bills, normalize Syahriah 12 months, fix Zarkasi, and relink null payment_rate_item_id.';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $targetNis = $this->option('nis');
        $targetSchool = $this->option('school');

        if ($isDryRun) {
            $this->warn('=== DRY RUN MODE - No database changes will be executed ===');
        }

        $this->info('Starting comprehensive audit, cleanup, Syahriah 12-month expansion, Zarkasi fix, and payment rate item relinking...');

        // 0. Ensure PaymentRates & PaymentRateItems have 12 monthly items for SYAHRIAH BillTypes
        $this->ensureSyahriahPaymentRateItems12Months($isDryRun);

        // 0b. Relink bills with NULL payment_rate_item_id to their respective payment_rate_items
        $this->relinkNullPaymentRateItems($isDryRun);

        // 0c. Clean up orphaned bills (deleted/missing student) and post-departure bills for non-active students
        $this->cleanupOrphanedAndInactiveBills($isDryRun);

        // 1. Identify MA schools
        $maSchools = School::where('name', 'LIKE', '%MA%')
            ->orWhere('name', 'LIKE', '%ALIYAH%')
            ->pluck('id')
            ->toArray();

        // 2. Fetch Students to Audit
        $studentsQuery = Student::query();

        if ($targetNis) {
            $studentsQuery->where('nis', $targetNis);
        } elseif ($targetSchool) {
            $studentsQuery->where('school_id', $targetSchool);
        } else {
            if (!empty($maSchools)) {
                $studentsQuery->where(function($q) use ($maSchools) {
                    $q->whereIn('school_id', $maSchools)
                      ->orWhereHas('classroom.school', function($sq) use ($maSchools) {
                          $sq->whereIn('id', $maSchools);
                      })
                      ->orWhereHas('classroom', function($cq) {
                          $cq->where('name', 'LIKE', '10%')
                             ->orWhere('name', 'LIKE', '11%')
                             ->orWhere('name', 'LIKE', '12%')
                             ->orWhere('name', 'LIKE', 'X%')
                             ->orWhere('name', 'LIKE', 'XI%')
                             ->orWhere('name', 'LIKE', 'XII%');
                      });
                });
            }
        }

        $totalGhostDeleted = 0;
        $totalDuplicateDeleted = 0;

        $studentsQuery->chunk(50, function ($students) use (&$totalGhostDeleted, &$totalDuplicateDeleted, $isDryRun) {
            foreach ($students as $student) {
                $this->line("--------------------------------------------------");
                $this->info("Auditing Student: {$student->name} (NIS: {$student->nis}, ID: {$student->id})");

                // FIRST: Normalize Syahriah into 12 monthly bills (July - June)
                $this->normalizeSyahriahBillsForStudent($student, $isDryRun);

                // SECOND: Fix Zarkasi bills into 1 single 550k annual bill per year & delete 450k ghost bills
                $this->normalizeZarkasiBillsForStudent($student, $isDryRun);

                // Fetch classroom history for student
                $histories = StudentClassroomHistory::with(['classroom.school', 'academicYear'])
                    ->where('student_id', $student->id)
                    ->whereNull('deleted_at')
                    ->get();

                $historyByAcademicYear = [];
                foreach ($histories as $hist) {
                    if ($hist->academic_year_id && $hist->classroom) {
                        $historyByAcademicYear[$hist->academic_year_id] = [
                            'classroom_id' => $hist->classroom_id,
                            'classroom_name' => $hist->classroom->name,
                            'school_id' => $hist->classroom->school_id,
                            'school_name' => $hist->classroom->school?->name ?? '',
                        ];
                    }
                }

                // Fetch all active bills for student
                $bills = Bill::with(['billType.billItem', 'billType.academicYear', 'academicYear', 'classroom.school'])
                    ->where('student_id', $student->id)
                    ->whereNull('deleted_at')
                    ->get();

                $this->line("  Active Bills Count: " . $bills->count());

                // A. GHOST BILL AUDIT
                foreach ($bills as $bill) {
                    if ($bill->status !== Bill::STATUS_UNPAID || (int)$bill->paid_amount > 0) {
                        continue; // Never touch paid bills
                    }

                    $billAcadYearId = $bill->academic_year_id;
                    $billType = $bill->billType;
                    $billTypeName = strtoupper($billType?->name ?? '');
                    $billItemName = strtoupper($billType?->billItem?->name ?? '');
                    $billCategory = $this->normalizeFeeCategory($billTypeName . ' ' . $billItemName);

                    // Check 1: Historical enrollment school unit mismatch
                    if (isset($historyByAcademicYear[$billAcadYearId])) {
                        $enrolledSchoolName = strtoupper($historyByAcademicYear[$billAcadYearId]['school_name']);

                        $isBillForMA = (str_contains($billTypeName, 'MA') || str_contains($billItemName, 'MA') || (!str_contains($billTypeName, 'SMP') && !str_contains($billTypeName, 'SD') && !str_contains($billTypeName, 'PONDOK')));
                        $isStudentInSMP = (str_contains($enrolledSchoolName, 'SMP') || str_contains($enrolledSchoolName, 'SD'));

                        if ($isBillForMA && $isStudentInSMP) {
                            $this->warn("  [GHOST DETECTED] Bill #{$bill->id} ({$billType?->name} - {$bill->academicYear?->name}) is for MA/General, but student was enrolled in {$enrolledSchoolName} in that academic year.");
                            if (!$isDryRun) {
                                $bill->delete();
                            }
                            $totalGhostDeleted++;
                            continue;
                        }
                    }

                    // Check 1b: UPT Pondok Cross-School Leakage Detection
                    $studentSchoolName = strtoupper($student->classroom?->school?->name ?? '');
                    $isBillForPondok = (str_contains($billTypeName, 'PONDOK') || str_contains($billItemName, 'PONDOK'));
                    $isStudentInNonPondok = (!str_contains($studentSchoolName, 'PONDOK') && !str_contains($studentSchoolName, 'PPTQ'));

                    if ($isBillForPondok && $isStudentInNonPondok) {
                        $this->warn("  [CROSS-UPT LEAKAGE DETECTED] Bill #{$bill->id} ({$billType?->name} - {$bill->academicYear?->name}) is for UPT PONDOK, but student is enrolled in non-pondok school '{$studentSchoolName}'.");
                        if (!$isDryRun) {
                            $bill->delete();
                        }
                        $totalGhostDeleted++;
                        continue;
                    }

                    // Check 2: Parallel category payment matching in same academic year
                    $hasPaidParallelCategory = $bills->first(function($otherBill) use ($bill, $billCategory) {
                        if ($otherBill->id === $bill->id) return false;
                        if ($otherBill->academic_year_id !== $bill->academic_year_id) return false;
                        if ((int)$otherBill->paid_amount == 0 && $otherBill->status === Bill::STATUS_UNPAID) return false;

                        $otherTypeName = strtoupper($otherBill->billType?->name ?? '');
                        $otherItemName = strtoupper($otherBill->billType?->billItem?->name ?? '');
                        $otherCategory = $this->normalizeFeeCategory($otherTypeName . ' ' . $otherItemName);

                        return ($billCategory === $otherCategory);
                    });

                    if ($hasPaidParallelCategory) {
                        $this->warn("  [GHOST DETECTED] Bill #{$bill->id} ({$billType?->name} {$bill->month}/{$bill->year}) is UNPAID ghost bill. Parallel paid bill #{$hasPaidParallelCategory->id} ({$hasPaidParallelCategory->billType?->name}) exists for category '{$billCategory}'!");
                        if (!$isDryRun) {
                            $bill->delete();
                        }
                        $totalGhostDeleted++;
                    }
                }

                // Reload remaining active bills after ghost cleanup
                $remainingBills = Bill::with(['billType.billItem', 'academicYear'])
                    ->where('student_id', $student->id)
                    ->whereNull('deleted_at')
                    ->get();

                // B. DUPLICATE BILL AUDIT
                $grouped = [];
                foreach ($remainingBills as $b) {
                    $typeName = strtoupper($b->billType?->name ?? 'UNKNOWN');
                    $itemName = strtoupper($b->billType?->billItem?->name ?? '');
                    $category = $this->normalizeFeeCategory($typeName . ' ' . $itemName);
                    $key = $b->academic_year_id . '_' . $b->month . '_' . $b->year . '_' . $category;

                    $grouped[$key][] = $b;
                }

                foreach ($grouped as $key => $groupBills) {
                    if (count($groupBills) > 1) {
                        $this->info("  [DUPLICATE GROUP] Key: {$key} Count: " . count($groupBills));

                        usort($groupBills, function($a, $b) {
                            $aPaid = ($a->status === Bill::STATUS_PAID || (int)$a->paid_amount > 0);
                            $bPaid = ($b->status === Bill::STATUS_PAID || (int)$b->paid_amount > 0);
                            if ($aPaid !== $bPaid) {
                                return $bPaid <=> $aPaid;
                            }
                            if ($a->paid_amount != $b->paid_amount) {
                                return $b->paid_amount <=> $a->paid_amount;
                            }
                            return $b->updated_at <=> $a->updated_at;
                        });

                        $keepBill = $groupBills[0];
                        $this->info("    Keeping Bill #{$keepBill->id} ({$keepBill->billType?->name}, Month {$keepBill->month}/{$keepBill->year}, Paid: Rp {$keepBill->paid_amount}, Status: {$keepBill->status})");

                        for ($i = 1; $i < count($groupBills); $i++) {
                            $dupBill = $groupBills[$i];
                            if ($dupBill->status === Bill::STATUS_UNPAID && (int)$dupBill->paid_amount == 0) {
                                $this->warn("    [DELETING DUPLICATE] Bill #{$dupBill->id} ({$dupBill->billType?->name}, Month {$dupBill->month}/{$dupBill->year}, Paid: Rp 0, Status: UNPAID)");
                                if (!$isDryRun) {
                                    $dupBill->delete();
                                }
                                $totalDuplicateDeleted++;
                            } else {
                                $this->warn("    [WARNING] Duplicate Bill #{$dupBill->id} has payments (Paid: Rp {$dupBill->paid_amount}). Retaining for manual review.");
                            }
                        }
                    }
                }
            }
        });

        $this->line("==================================================");
        $this->info("COMPREHENSIVE AUDIT & CLEANUP COMPLETED.");
        $this->info("Ghost Bills Soft-Deleted     : {$totalGhostDeleted}");
        $this->info("Duplicate Bills Soft-Deleted : {$totalDuplicateDeleted}");

        if ($isDryRun) {
            $this->warn("=== DRY RUN MODE - No actual changes were saved ===");
        }

        return Command::SUCCESS;
    }

    /**
     * Relink bills with NULL payment_rate_item_id to payment_rate_items
     */
    private function relinkNullPaymentRateItems(bool $isDryRun)
    {
        $unlinkedBills = Bill::whereNull('payment_rate_item_id')
            ->whereNull('deleted_at')
            ->get();

        if ($unlinkedBills->isEmpty()) {
            return;
        }

        $this->info("  [RELINKING] Found {$unlinkedBills->count()} bills with NULL payment_rate_item_id. Relinking...");

        $relinkedCount = 0;
        foreach ($unlinkedBills as $bill) {
            $rateItem = PaymentRateItem::whereHas('paymentRate', function($q) use ($bill) {
                $q->where('bill_type_id', $bill->bill_type_id);
            })
            ->where('month', $bill->month)
            ->where('year', $bill->year)
            ->first();

            if (!$rateItem) {
                // Find or create PaymentRate for this bill_type_id
                $paymentRate = PaymentRate::where('bill_type_id', $bill->bill_type_id)->first();
                if (!$paymentRate) {
                    $paymentRate = PaymentRate::create([
                        'id' => (string)Str::uuid(),
                        'bill_type_id' => $bill->bill_type_id,
                        'amount' => $bill->amount,
                        'type' => 'REGULAR',
                    ]);
                }

                $rateItem = PaymentRateItem::create([
                    'id' => (string)Str::uuid(),
                    'payment_rate_id' => $paymentRate->id,
                    'month' => $bill->month,
                    'year' => $bill->year,
                    'amount' => $bill->amount,
                ]);
            }

            if ($rateItem && !$isDryRun) {
                $bill->update(['payment_rate_item_id' => $rateItem->id]);
                $relinkedCount++;
            }
        }

        $this->info("  [RELINKING COMPLETE] Successfully relinked {$relinkedCount} bills to payment_rate_items.");
    }

    /**
     * Normalize Syahriah into 12 monthly bills (July - June)
     */
    private function normalizeSyahriahBillsForStudent(Student $student, bool $isDryRun)
    {
        $activeBills = Bill::with(['billType.academicYear'])
            ->where('student_id', $student->id)
            ->whereNull('deleted_at')
            ->get();

        $groupedByBillType = $activeBills->groupBy('bill_type_id');

        foreach ($groupedByBillType as $billTypeId => $typeBills) {
            $first = $typeBills->first();
            $billType = $first->billType;
            if (!$billType) continue;

            $isSyahriah = str_contains(strtoupper($billType->name), 'SYAHRIAH');
            if (!$isSyahriah) continue; // STRICTLY FOR SYAHRIAH ONLY

            $acadYear = $billType->academicYear;
            if (!$acadYear) continue;

            $startYear = $acadYear->getStartYearSafe() ?? 2026;
            $endYear = $startYear + 1;

            $singleLargeBill = $typeBills->first(function($b) {
                return (int)$b->amount >= 5000000;
            });

            if ($singleLargeBill || $typeBills->count() < 12) {
                $this->info("  [SYAHRIAH EXPANSION] Splitting bill for {$billType->name} ({$acadYear->name}) into 12 monthly entries (July {$startYear} - June {$endYear})...");

                $totalPaid = $typeBills->sum('paid_amount');
                $totalYearlyAmount = $singleLargeBill ? (int)$singleLargeBill->amount : 6000000;
                $monthlyAmount = intval($totalYearlyAmount / 12);
                if ($monthlyAmount <= 0) $monthlyAmount = 500000;

                $monthsSequence = [
                    ['month' => 7, 'year' => $startYear],
                    ['month' => 8, 'year' => $startYear],
                    ['month' => 9, 'year' => $startYear],
                    ['month' => 10, 'year' => $startYear],
                    ['month' => 11, 'year' => $startYear],
                    ['month' => 12, 'year' => $startYear],
                    ['month' => 1, 'year' => $endYear],
                    ['month' => 2, 'year' => $endYear],
                    ['month' => 3, 'year' => $endYear],
                    ['month' => 4, 'year' => $endYear],
                    ['month' => 5, 'year' => $endYear],
                    ['month' => 6, 'year' => $endYear],
                ];

                if (!$isDryRun) {
                    foreach ($typeBills as $tb) {
                        $tb->delete();
                    }
                }

                $remainingPaidPool = $totalPaid;
                $now = now();

                // Find or create PaymentRate for this bill_type_id
                $paymentRate = PaymentRate::where('bill_type_id', $billType->id)->first();
                if (!$paymentRate && !$isDryRun) {
                    $paymentRate = PaymentRate::create([
                        'id' => (string)Str::uuid(),
                        'bill_type_id' => $billType->id,
                        'amount' => $totalYearlyAmount,
                        'type' => 'REGULAR',
                    ]);
                }

                foreach ($monthsSequence as $mInfo) {
                    $m = $mInfo['month'];
                    $y = $mInfo['year'];

                    $allocatedPaid = 0;
                    if ($remainingPaidPool >= $monthlyAmount) {
                        $allocatedPaid = $monthlyAmount;
                        $remainingPaidPool -= $monthlyAmount;
                    } elseif ($remainingPaidPool > 0) {
                        $allocatedPaid = $remainingPaidPool;
                        $remainingPaidPool = 0;
                    }

                    $status = ($allocatedPaid >= $monthlyAmount) ? Bill::STATUS_PAID : Bill::STATUS_UNPAID;

                    // Ensure payment_rate_item exists for this month/year
                    $rateItem = null;
                    if ($paymentRate) {
                        $rateItem = PaymentRateItem::where('payment_rate_id', $paymentRate->id)
                            ->where('month', $m)
                            ->where('year', $y)
                            ->first();

                        if (!$rateItem && !$isDryRun) {
                            $rateItem = PaymentRateItem::create([
                                'id' => (string)Str::uuid(),
                                'payment_rate_id' => $paymentRate->id,
                                'month' => $m,
                                'year' => $y,
                                'amount' => $monthlyAmount,
                            ]);
                        }
                    }

                    if (!$isDryRun) {
                        DB::table('bills')->insert([
                            'id' => (string)Str::uuid(),
                            'bill_type_id' => $billType->id,
                            'classroom_id' => $student->classroom_id,
                            'student_id' => $student->id,
                            'academic_year_id' => $billType->academic_year_id,
                            'month' => $m,
                            'year' => $y,
                            'amount' => $monthlyAmount,
                            'paid_amount' => $allocatedPaid,
                            'status' => $status,
                            'payment_rate_item_id' => $rateItem?->id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                    $this->line("    + Created Syahriah Bill: Month {$m}/{$y} | Amount: Rp " . number_format($monthlyAmount) . " | Paid: Rp " . number_format($allocatedPaid) . " | Status: {$status}");
                }
            }
        }
    }

    /**
     * Fix Zarkasi bills into 1 single 550,000 annual bill per year & delete 450,000 ghost bills
     */
    private function normalizeZarkasiBillsForStudent(Student $student, bool $isDryRun)
    {
        $zarkasiBills = Bill::with(['billType.academicYear'])
            ->where('student_id', $student->id)
            ->whereHas('billType', function($q) {
                $q->where('name', 'LIKE', '%ZARKASI%');
            })
            ->whereNull('deleted_at')
            ->get();

        $groupedByYear = $zarkasiBills->groupBy('academic_year_id');

        foreach ($groupedByYear as $acadYearId => $billsInYear) {
            $first = $billsInYear->first();
            $acadYear = $first->billType?->academicYear;
            $startYear = $acadYear?->getStartYearSafe() ?? 2024;

            $totalPaid = $billsInYear->sum('paid_amount');
            $this->info("  [ZARKASI FIX] Processing Zarkasi for Academic Year ID: {$acadYearId} (Total Paid: Rp " . number_format($totalPaid) . ")...");

            if (!$isDryRun) {
                foreach ($billsInYear as $zb) {
                    $zb->delete();
                }
            }

            $zarkasiAmount = 550000;
            $allocatedPaid = min($totalPaid, $zarkasiAmount);
            $status = ($allocatedPaid >= $zarkasiAmount) ? Bill::STATUS_PAID : Bill::STATUS_UNPAID;

            $paymentRate = PaymentRate::where('bill_type_id', $first->bill_type_id)->first();
            $rateItem = null;
            if ($paymentRate) {
                $rateItem = PaymentRateItem::where('payment_rate_id', $paymentRate->id)
                    ->where('month', 7)
                    ->where('year', $startYear)
                    ->first();
                if (!$rateItem && !$isDryRun) {
                    $rateItem = PaymentRateItem::create([
                        'id' => (string)Str::uuid(),
                        'payment_rate_id' => $paymentRate->id,
                        'month' => 7,
                        'year' => $startYear,
                        'amount' => $zarkasiAmount,
                    ]);
                }
            }

            if (!$isDryRun) {
                DB::table('bills')->insert([
                    'id' => (string)Str::uuid(),
                    'bill_type_id' => $first->bill_type_id,
                    'classroom_id' => $student->classroom_id,
                    'student_id' => $student->id,
                    'academic_year_id' => $acadYearId,
                    'month' => 7,
                    'year' => $startYear,
                    'amount' => $zarkasiAmount,
                    'paid_amount' => $allocatedPaid,
                    'status' => $status,
                    'payment_rate_item_id' => $rateItem?->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->info("    + Restored Zarkasi Single Annual Bill: Month 7/{$startYear} | Amount: Rp 550,000 | Paid: Rp " . number_format($allocatedPaid) . " | Status: {$status}");
        }
    }

    /**
     * Ensure PaymentRates & PaymentRateItems have 12 monthly items for SYAHRIAH BillTypes
     */
    private function ensureSyahriahPaymentRateItems12Months(bool $isDryRun)
    {
        $syahriahBillTypes = BillType::with(['academicYear', 'paymentRates.paymentRateItems'])
            ->where('name', 'LIKE', '%SYAHRIAH%')
            ->get();

        foreach ($syahriahBillTypes as $bt) {
            $acadYear = $bt->academicYear;
            if (!$acadYear) continue;

            $startYear = $acadYear->getStartYearSafe() ?? 2026;
            $endYear = $startYear + 1;

            $monthsSequence = [
                ['month' => 7, 'year' => $startYear],
                ['month' => 8, 'year' => $startYear],
                ['month' => 9, 'year' => $startYear],
                ['month' => 10, 'year' => $startYear],
                ['month' => 11, 'year' => $startYear],
                ['month' => 12, 'year' => $startYear],
                ['month' => 1, 'year' => $endYear],
                ['month' => 2, 'year' => $endYear],
                ['month' => 3, 'year' => $endYear],
                ['month' => 4, 'year' => $endYear],
                ['month' => 5, 'year' => $endYear],
                ['month' => 6, 'year' => $endYear],
            ];

            foreach ($bt->paymentRates as $pr) {
                if ($pr->paymentRateItems->count() < 12) {
                    $this->info("  [FIX RATE] Expanding Syahriah PaymentRate #{$pr->id} ({$bt->name}) to 12 monthly items of Rp 500,000...");
                    if (!$isDryRun) {
                        PaymentRateItem::where('payment_rate_id', $pr->id)->delete();

                        foreach ($monthsSequence as $mInfo) {
                            PaymentRateItem::create([
                                'id' => (string)Str::uuid(),
                                'payment_rate_id' => $pr->id,
                                'month' => $mInfo['month'],
                                'year' => $mInfo['year'],
                                'amount' => 500000,
                            ]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Helper to normalize fee category string across naming variations
     */
    private function normalizeFeeCategory(string $rawName): string
    {
        $upper = strtoupper($rawName);

        if (str_contains($upper, 'SYAHRIAH')) {
            return 'SYAHRIAH';
        }
        if (str_contains($upper, 'ZARKASI')) {
            return 'ZARKASI';
        }
        if (str_contains($upper, 'APLIKASI')) {
            return 'APLIKASI';
        }
        if (str_contains($upper, 'REGISTRASI') || str_contains($upper, 'PSB') || str_contains($upper, 'PPDB')) {
            return 'REGISTRASI';
        }

        $clean = preg_replace('/(BIAYA|APLIKASI|CT|SMP|MA|SD|-|\s|20\d\d\/20\d\d|\d{4})+/', '', $upper);
        return trim($clean) ?: 'GENERAL';
    }

    /**
     * Audit and clean up orphaned bills (deleted/missing students) and post-departure bills for non-active students
     */
    private function cleanupOrphanedAndInactiveBills(bool $isDryRun): void
    {
        $this->info("Auditing orphaned bills (bills with deleted/missing students)...");
        $orphanedBillsCount = Bill::whereDoesntHave('student')
            ->where('status', Bill::STATUS_UNPAID)
            ->whereNull('deleted_at')
            ->count();

        if ($orphanedBillsCount > 0) {
            $this->warn("Found {$orphanedBillsCount} orphaned unpaid bills.");
            if (!$isDryRun) {
                Bill::whereDoesntHave('student')
                    ->where('status', Bill::STATUS_UNPAID)
                    ->whereNull('deleted_at')
                    ->delete();
                $this->info("Deleted {$orphanedBillsCount} orphaned unpaid bills.");
            }
        } else {
            $this->info("No orphaned unpaid bills found.");
        }

        $this->info("Auditing future unpaid bills for non-active students...");
        $inactiveStudents = Student::where('status', '!=', Student::STATUS_ACTIVE)->get();
        $cleanedCount = 0;

        foreach ($inactiveStudents as $student) {
            if (!$isDryRun) {
                $student->cleanupFutureUnpaidBills();
            }
            $cleanedCount++;
        }

        $this->info("Processed future unpaid bills cleanup for {$cleanedCount} non-active students.");
    }
}
