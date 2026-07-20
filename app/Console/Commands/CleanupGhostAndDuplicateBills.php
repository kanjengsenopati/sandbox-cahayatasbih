<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\Bill;
use App\Models\BillType;
use App\Models\Classroom;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentClassroomHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
    protected $description = 'Audit and clean up ghost bills for transfer students and duplicate bills for MA students (grades 10, 11, 12).';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $targetNis = $this->option('nis');
        $targetSchool = $this->option('school');

        if ($isDryRun) {
            $this->warn('=== DRY RUN MODE - No database changes will be executed ===');
        }

        $this->info('Starting comprehensive audit and cleanup of ghost and duplicate bills...');

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

        $students = $studentsQuery->get();
        $this->info("Found {$students->count()} students for auditing.");

        $totalGhostDeleted = 0;
        $totalDuplicateDeleted = 0;

        foreach ($students as $student) {
            $this->line("--------------------------------------------------");
            $this->info("Auditing Student: {$student->name} (NIS: {$student->nis}, ID: {$student->id})");

            // Fetch classroom history for student
            $histories = StudentClassroomHistory::with(['classroom.school', 'academicYear'])
                ->where('student_id', $student->id)
                ->whereNull('deleted_at')
                ->get();

            // Map academic_year_id to actual enrolled school/classroom
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

                    $isBillForMA = (str_contains($billTypeName, 'MA') || str_contains($billItemName, 'MA') || (!str_contains($billTypeName, 'SMP') && !str_contains($billTypeName, 'SD')));
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

                // Check 2: Parallel category payment matching in same academic year
                // If student ALREADY HAS a PAID bill in the same fee category for the same academic year (e.g. SMP paid bill),
                // then any UNPAID bill in that same fee category is a ghost/duplicate bill!
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
            // Group remaining bills by (academic_year_id, month, year, fee_category)
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

                    // Sort: PAID bills first (highest paid_amount), then most recently updated
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
                    $this->info("    Keeping Bill #{$keepBill->id} ({$keepBill->billType?->name}, Paid: Rp {$keepBill->paid_amount}, Status: {$keepBill->status})");

                    for ($i = 1; $i < count($groupBills); $i++) {
                        $dupBill = $groupBills[$i];
                        if ($dupBill->status === Bill::STATUS_UNPAID && (int)$dupBill->paid_amount == 0) {
                            $this->warn("    [DELETING DUPLICATE] Bill #{$dupBill->id} ({$dupBill->billType?->name}, Paid: Rp 0, Status: UNPAID)");
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

        // Clean out common noise words
        $clean = preg_replace('/(BIAYA|APLIKASI|CT|SMP|MA|SD|-|\s|20\d\d\/20\d\d|\d{4})+/', '', $upper);
        return trim($clean) ?: 'GENERAL';
    }
}
