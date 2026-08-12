<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\BillType;
use App\Models\PaymentRate;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncPaymentRateBills extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:sync-rate
                            {--rate= : Specific PaymentRate ID to sync}
                            {--bill-type= : Specific BillType ID to sync}
                            {--user-id= : Specific User ID to sync}
                            {--student-id= : Specific Student ID to sync}
                            {--dry-run : Show what would be done without making changes}
                            {--force : Also update UNPAID bills with wrong amount/rate_item}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and sync bills for all students based on PaymentRate configuration. Handles missing bills and corrects amount mismatches.';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $forceUpdate = $this->option('force');
        $rateId = $this->option('rate');
        $billTypeId = $this->option('bill-type');

        if ($isDryRun) {
            $this->warn('=== DRY RUN MODE - No changes will be made ===');
        }

        // Build query
        $query = PaymentRate::with([
            'billType',
            'paymentRateItems',
            'paymentRateClassrooms.classroom',
            'paymentRateStudents',
        ]);

        if ($rateId) {
            $query->where('id', $rateId);
        }

        if ($billTypeId) {
            $query->where('bill_type_id', $billTypeId);
        }

        $paymentRates = $query->get();

        if ($paymentRates->isEmpty()) {
            $this->error('Tidak ada PaymentRate yang ditemukan.');
            return Command::FAILURE;
        }

        $totalCreated = 0;
        $totalUpdated = 0;
        $totalSkipped = 0;

        foreach ($paymentRates as $paymentRate) {
            $billType = $paymentRate->billType;

            if (!$billType) {
                $this->warn("PaymentRate {$paymentRate->id}: BillType tidak ditemukan, dilewati.");
                continue;
            }

            if ($paymentRate->paymentRateItems->isEmpty()) {
                $this->warn("PaymentRate {$paymentRate->id} ({$billType->name}): Tidak ada PaymentRateItem, dilewati.");
                continue;
            }

            $this->line('');
            $this->info("=== PaymentRate: {$paymentRate->id}");
            $this->line("    BillType  : {$billType->name} (Type: {$billType->type})");
            $this->line("    Jamaah    : " . ($paymentRate->jamaah_status ?? 'Semua'));
            $this->line("    Gender    : " . ($paymentRate->gender ?? 'Semua'));
            $this->line("    Items     : " . $paymentRate->paymentRateItems->count() . " items");

            // Fetch students based on PaymentRate configuration
            $students = $this->getStudentsForRate($paymentRate);

            $this->line("    Students  : {$students->count()} siswa aktif");

            if ($students->isEmpty()) {
                $this->warn("    Tidak ada siswa yang memenuhi filter, dilewati.");
                continue;
            }

            // Process each student
            $timestamp = now();
            $billsToInsert = [];
            $startYear = $billType->academicYear?->getStartYearSafe();

            foreach ($students as $student) {
                // Prevent generating bills for years before the student's entry year (only for past/inactive academic years)
                if ($startYear !== null && !$billType->academicYear?->is_active && $student->getEntryYear() > $startYear) {
                    $this->warn("    [SKIP] {$student->name} (NIS: {$student->nis}) entered in {$student->getEntryYear()}, bill is for {$billType->academicYear->name}.");
                    continue;
                }

                // Check historical classroom history for past academic years to protect transfer students
                if ($billType->academicYear && !$billType->academicYear->is_active) {
                    $history = DB::table('student_classroom_histories')
                        ->where('student_id', $student->id)
                        ->where('academic_year_id', $billType->academic_year_id)
                        ->whereNull('deleted_at')
                        ->first();

                    if ($history) {
                        // Check if history classroom matches payment rate classroom
                        $allowedClassroomIds = $paymentRate->paymentRateClassrooms->pluck('classroom_id')->toArray();
                        if (!empty($allowedClassroomIds) && !in_array($history->classroom_id, $allowedClassroomIds)) {
                            $this->warn("    [SKIP HISTORICAL] {$student->name} was in classroom {$history->classroom_id} during {$billType->academicYear->name}, not in target rate classrooms.");
                            continue;
                        }
                    }
                }

                foreach ($paymentRate->paymentRateItems as $item) {
                    $billMonth = $item->month;
                    $billYear  = $item->year;
                    $billAmount = $item->amount;

                    // Check existing bill
                    $existingBill = DB::table('bills')
                        ->where('student_id', $student->id)
                        ->where('bill_type_id', $billType->id)
                        ->where('month', $billMonth)
                        ->where('year', $billYear)
                        ->whereNull('deleted_at')
                        ->first();

                    if (!$existingBill) {
                        // Bill belum ada - buat baru
                        if (!$isDryRun) {
                            $billsToInsert[] = [
                                'id'                   => Str::uuid()->toString(),
                                'bill_type_id'         => $billType->id,
                                'classroom_id'         => $student->classroom_id,
                                'student_id'           => $student->id,
                                'academic_year_id'     => $billType->academic_year_id,
                                'month'                => $billMonth,
                                'year'                 => $billYear,
                                'amount'               => $billAmount,
                                'paid_amount'          => 0,
                                'status'               => Bill::STATUS_UNPAID,
                                'payment_rate_item_id' => $item->id,
                                'created_at'           => $timestamp,
                                'updated_at'           => $timestamp,
                            ];
                        }
                        $totalCreated++;
                        $this->line("    [CREATE] {$student->name} | Bulan {$billMonth}/{$billYear} | Rp " . number_format($billAmount, 0, ',', '.'));
                    } elseif ($forceUpdate) {
                        $needsUpdate = ($existingBill->payment_rate_item_id !== $item->id || (int)$existingBill->amount !== (int)$billAmount);

                        if ($needsUpdate) {
                            if ($existingBill->status === Bill::STATUS_PAID || $existingBill->status === 'PARTIAL') {
                                // For PAID/PARTIAL bills, ONLY update payment_rate_item_id and classroom, NEVER change the amount!
                                if ($existingBill->payment_rate_item_id !== $item->id) {
                                    if (!$isDryRun) {
                                        DB::table('bills')
                                            ->where('id', $existingBill->id)
                                            ->update([
                                                'payment_rate_item_id' => $item->id,
                                                'classroom_id'         => $student->classroom_id,
                                                'updated_at'           => $timestamp,
                                            ]);
                                    }
                                    $totalUpdated++;
                                    $this->line("    [UPDATE-LINK] {$student->name} | Bulan {$billMonth}/{$billYear} | Diperbarui relasi item ID");
                                } else {
                                    $totalSkipped++;
                                }
                            } else {
                                // For UNPAID bills, update both amount and item_id
                                if (!$isDryRun) {
                                    DB::table('bills')
                                        ->where('id', $existingBill->id)
                                        ->update([
                                            'amount'               => $billAmount,
                                            'payment_rate_item_id' => $item->id,
                                            'classroom_id'         => $student->classroom_id,
                                            'updated_at'           => $timestamp,
                                        ]);
                                }
                                $totalUpdated++;
                                $this->line("    [UPDATE] {$student->name} | Bulan {$billMonth}/{$billYear} | " .
                                    "Rp " . number_format($existingBill->amount, 0, ',', '.') . " -> Rp " . number_format($billAmount, 0, ',', '.'));
                            }
                        } else {
                            $totalSkipped++;
                        }
                    } else {
                        $totalSkipped++;
                    }
                }
            }

            // Bulk insert
            if (!$isDryRun && !empty($billsToInsert)) {
                foreach (array_chunk($billsToInsert, 500) as $chunk) {
                    DB::table('bills')->insert($chunk);
                }
                $billsToInsert = [];
            }
        }

        $this->line('');
        $this->info('=== SELESAI ===');
        $this->info("Bills dibuat    : {$totalCreated}");
        $this->info("Bills diupdate  : {$totalUpdated}");
        $this->info("Bills dilewati  : {$totalSkipped}");

        if ($isDryRun) {
            $this->warn('=== DRY RUN - Tidak ada perubahan yang disimpan ===');
        }

        return Command::SUCCESS;
    }

    /**
     * Get students matching a PaymentRate's filters
     */
    private function getStudentsForRate(PaymentRate $paymentRate): \Illuminate\Support\Collection
    {
        $billType = $paymentRate->billType;
        $isPondok = false;
        
        if ($billType) {
            $nameToCheck = strtoupper($billType->name . ' ' . ($billType->billItem?->name ?? ''));
            $isPondok = str_contains($nameToCheck, 'PONDOK') || str_contains($nameToCheck, 'PPTQ');
        }

        $query = Student::query();
        if ($isPondok) {
            $query->whereIn('status', ['ACTIVE', 'GRADUATED']);
        } else {
            $query->where('status', 'ACTIVE');
        }
        
        if ($this->option('user-id')) {
            $query->where('user_id', $this->option('user-id'));
        }

        if ($this->option('student-id')) {
            $query->where('id', $this->option('student-id'));
        }

        // Filter by classroom or specific students
        if ($paymentRate->type === PaymentRate::TYPE_REGULAR) {
            $classroomIds = $paymentRate->paymentRateClassrooms->pluck('classroom_id')->toArray();
            if (empty($classroomIds)) {
                return collect([]);
            }
            $query->whereIn('classroom_id', $classroomIds);
        } else {
            $studentIds = $paymentRate->paymentRateStudents->pluck('student_id')->toArray();
            if (empty($studentIds)) {
                return collect([]);
            }
            $query->whereIn('id', $studentIds);
        }

        // Strict 3-UPT Guard: Ensure student's classroom belongs strictly to the target UPT school of the BillType
        $billType = $paymentRate->billType;
        if ($billType) {
            $targetSchoolId = $this->resolveTargetSchoolIdForBillType($billType);
            if ($targetSchoolId) {
                $query->whereHas('classroom', function ($q) use ($targetSchoolId) {
                    $q->where('school_id', $targetSchoolId);
                });
            }
        }

        // Filter by gender
        if ($paymentRate->gender) {
            $query->whereIn('gender', array_map('trim', explode(',', $paymentRate->gender)));
        }

        // Filter by jamaah_status
        if ($paymentRate->jamaah_status) {
            $statuses = array_map('trim', explode(',', $paymentRate->jamaah_status));
            $query->where(function ($q) use ($statuses) {
                $q->whereHas('user', function ($userQ) use ($statuses) {
                    $userQ->whereIn('jamaah_status', $statuses);
                });
                if (in_array('NON_JAMAAH', $statuses)) {
                    $q->orWhereNull('user_id')
                      ->orWhereDoesntHave('user')
                      ->orWhereHas('user', function ($userQ) {
                          $userQ->whereNull('jamaah_status');
                      });
                }
            });
        }

        return $query->get(['id', 'name', 'classroom_id', 'gender', 'user_id']);
    }

    /**
     * Resolve the target school_id for a BillType based on its name (3-UPT: SMP, MA, PONDOK).
     * Returns null if no specific UPT can be determined.
     */
    private function resolveTargetSchoolIdForBillType(BillType $billType): ?string
    {
        $nameToCheck = strtoupper($billType->name . ' ' . ($billType->billItem?->name ?? ''));

        if (str_contains($nameToCheck, 'PONDOK') || str_contains($nameToCheck, 'PPTQ')) {
            return DB::table('schools')
                ->where(function ($q) {
                    $q->where('type', 'PONDOK')
                      ->orWhere('name', 'LIKE', '%PONDOK%')
                      ->orWhere('name', 'LIKE', '%PPTQ%');
                })
                ->whereNull('deleted_at')
                ->value('id');
        }

        if (str_contains($nameToCheck, 'MA') || str_contains($nameToCheck, 'ALIYAH')) {
            return DB::table('schools')
                ->where(function ($q) {
                    $q->where('type', 'MA')
                      ->orWhere('name', 'LIKE', '%MA%')
                      ->orWhere('name', 'LIKE', '%ALIYAH%');
                })
                ->whereNull('deleted_at')
                ->value('id');
        }

        if (str_contains($nameToCheck, 'SMP')) {
            return DB::table('schools')
                ->where(function ($q) {
                    $q->where('type', 'SMP')
                      ->orWhere('name', 'LIKE', '%SMP%');
                })
                ->whereNull('deleted_at')
                ->value('id');
        }

        return null;
    }
}

