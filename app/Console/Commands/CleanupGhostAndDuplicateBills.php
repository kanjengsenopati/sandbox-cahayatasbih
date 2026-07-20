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

        $this->info('Starting audit and cleanup of ghost and duplicate bills...');

        // 1. Identify MA schools / classrooms
        $maSchoolIds = [];
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
            // Target MA students (or all students if MA school filter is applied)
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
            // Ghost bills are UNPAID bills attached to MA / wrong unit for past academic years when student was actually in another unit (e.g. SMP)
            foreach ($bills as $bill) {
                if ($bill->status !== Bill::STATUS_UNPAID || (int)$bill->paid_amount > 0) {
                    continue; // Never touch paid bills
                }

                $billAcadYearId = $bill->academic_year_id;
                $billType = $bill->billType;
                $billTypeName = $billType?->name ?? '';
                $billItemName = $billType?->billItem?->name ?? '';

                // Check historical enrollment for this academic year
                if (isset($historyByAcademicYear[$billAcadYearId])) {
                    $enrolledSchoolId = $historyByAcademicYear[$billAcadYearId]['school_id'];
                    $enrolledSchoolName = strtoupper($historyByAcademicYear[$billAcadYearId]['school_name']);

                    // Is this bill an MA bill while student was enrolled in SMP/SD in that academic year?
                    $isBillForMA = (stripos($billTypeName, 'MA') !== false || stripos($billItemName, 'MA') !== false);
                    $isStudentInSMP = (stripos($enrolledSchoolName, 'SMP') !== false || stripos($enrolledSchoolName, 'SD') !== false);

                    if ($isBillForMA && $isStudentInSMP) {
                        $this->warn("  [GHOST DETECTED] Bill #{$bill->id} ({$billTypeName} - {$bill->academicYear?->name}) is for MA, but student was enrolled in {$enrolledSchoolName} in that academic year.");
                        if (!$isDryRun) {
                            $bill->delete(); // Soft delete
                        }
                        $totalGhostDeleted++;
                        continue;
                    }
                }

                // Generic Ghost Check: Parallel SMP vs MA bill check in the same academic year
                // If student has a PAID bill for SMP fee (e.g. SYAHRIAH SMP, ZARKASI SMP, BIAYA APLIKASI CT - SMP) for month/year/acad_year,
                // and an UNPAID MA bill (e.g. SYAHRIAH MA, ZARKASI, BIAYA APLIKASI CT - MA) exists for the same month/year/acad_year, it is a ghost bill!
                $hasPaidParallelBill = $bills->first(function($otherBill) use ($bill, $billTypeName, $billItemName) {
                    if ($otherBill->id === $bill->id) return false;
                    if ($otherBill->academic_year_id !== $bill->academic_year_id) return false;
                    if ($otherBill->month != $bill->month || $otherBill->year != $bill->year) return false;
                    if ((int)$otherBill->paid_amount == 0 && $otherBill->status === Bill::STATUS_UNPAID) return false;

                    // Check if otherBill is the SMP counterpart that is PAID
                    $otherTypeName = $otherBill->billType?->name ?? '';
                    $otherItemName = $otherBill->billType?->billItem?->name ?? '';

                    $baseCategoryBill = preg_replace('/(SMP|MA|CT|-|\s)+/i', '', $billTypeName);
                    $baseCategoryOther = preg_replace('/(SMP|MA|CT|-|\s)+/i', '', $otherTypeName);

                    return (strtolower($baseCategoryBill) === strtolower($baseCategoryOther));
                });

                if ($hasPaidParallelBill) {
                    $this->warn("  [GHOST DETECTED] Bill #{$bill->id} ({$billTypeName} {$bill->month}/{$bill->year}) is UNPAID ghost bill. Parallel paid bill #{$hasPaidParallelBill->id} ({$hasPaidParallelBill->billType?->name}) exists!");
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
            // Group bills by (academic_year_id, month, year, normalized_category)
            $grouped = [];
            foreach ($remainingBills as $b) {
                $typeName = $b->billType?->name ?? 'UNKNOWN';
                // Normalize category name (e.g. "SYAHRIAH" vs "SYAHRIAH 2026/2027")
                $normCat = preg_replace('/(20\d\d\/20\d\d|\d{4}|\s)+/', '', strtoupper($typeName));
                $key = $b->academic_year_id . '_' . $b->month . '_' . $b->year . '_' . $normCat;

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
        $this->info("AUDIT & CLEANUP COMPLETED.");
        $this->info("Ghost Bills Soft-Deleted     : {$totalGhostDeleted}");
        $this->info("Duplicate Bills Soft-Deleted : {$totalDuplicateDeleted}");

        if ($isDryRun) {
            $this->warn("=== DRY RUN MODE - No actual changes were saved ===");
        }

        return Command::SUCCESS;
    }
}
