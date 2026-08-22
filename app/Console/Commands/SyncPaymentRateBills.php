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

            $classrooms = $paymentRate->paymentRateClassrooms->pluck('classroom_id')->sort()->implode('_');
            $classroomsStr = empty($classrooms) ? 'all' : $classrooms;
            $lockKey = md5($billType->name . '_' . $billType->academic_year_id . '_' . $paymentRate->amount . '_' . $classroomsStr);
            $lockName = 'sync_bills_' . $lockKey;
            $lock = \Illuminate\Support\Facades\Cache::lock($lockName, 300);

            if (!$lock->get()) {
                $this->warn("PaymentRate {$paymentRate->id}: Proses sinkronisasi untuk konfigurasi tagihan dan kelas yang sama sedang berjalan (Race Condition Protection). Dilewati.");
                continue;
            }

            try {
                // Pre-fetch related bill type IDs (same name and academic year) to protect against identically named duplicates
                $relatedBillTypeIds = DB::table('bill_types')
                    ->where('name', $billType->name)
                    ->where('academic_year_id', $billType->academic_year_id)
                    ->pluck('id')
                    ->toArray();

                // Fetch students based on PaymentRate configuration
                $students = $this->getStudentsForRate($paymentRate, $relatedBillTypeIds);

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

                    // Get the correct classroom ID for the bill based on academic year
                    $targetClassroomId = $student->classroom_id;

                    // Check historical classroom history for past academic years to protect transfer students
                    if ($billType->academicYear && !$billType->academicYear->is_active) {
                        $history = DB::table('student_classroom_histories')
                            ->where('student_id', $student->id)
                            ->where('academic_year_id', $billType->academic_year_id)
                            ->whereNull('deleted_at')
                            ->first();

                        if ($history) {
                            $targetClassroomId = $history->classroom_id;
                            // Check if history classroom matches payment rate classroom
                            $allowedClassroomIds = $paymentRate->paymentRateClassrooms->pluck('classroom_id')->toArray();
                            if (!empty($allowedClassroomIds) && !in_array($history->classroom_id, $allowedClassroomIds)) {
                                $this->warn("    [SKIP HISTORICAL] {$student->name} was in classroom {$history->classroom_id} during {$billType->academicYear->name}, not in target rate classrooms.");
                                continue;
                            }
                        } else {
                            // For regular rates, if there is no history in that past year -> skip them entirely!
                            if ($paymentRate->type === PaymentRate::TYPE_REGULAR) {
                                $this->warn("    [SKIP LEAKAGE] {$student->name} had no classroom history in {$billType->academicYear->name}. Skipping to prevent leakage.");
                                continue;
                            }
                        }
                    }

                    foreach ($paymentRate->paymentRateItems as $item) {
                        $billMonth = $item->month;
                        $billYear  = $item->year;
                        $billAmount = $item->amount;

                        // Check existing bill across ANY identical bill type names
                        $existingBill = DB::table('bills')
                            ->where('student_id', $student->id)
                            ->whereIn('bill_type_id', $relatedBillTypeIds)
                            ->where('month', $billMonth)
                            ->where('year', $billYear)
                            ->whereNull('deleted_at')
                            ->first();

                        if ($existingBill && $existingBill->bill_type_id !== $billType->id) {
                            $this->warn("    [DUPLICATE AVOIDED] {$student->name} | Bulan {$billMonth}/{$billYear} diabaikan karena sudah ada tagihan identik dari tipe tagihan lain.");
                            $totalSkipped++;
                            continue;
                        }

                        if (!$existingBill) {
                            // Bill belum ada - buat baru
                            if (!$isDryRun) {
                                $billsToInsert[] = [
                                    'id'                   => Str::uuid()->toString(),
                                    'bill_type_id'         => $billType->id,
                                    'classroom_id'         => $targetClassroomId,
                                    'student_id'           => $student->id,
                                    'academic_year_id'     => $billType->academic_year_id,
                                    'month'                => $billMonth,
                                    'year'                 => $billYear,
                                    'amount'               => $billAmount,
                                    'paid_amount'          => 0,
                                    'status'               => \App\Models\Bill::STATUS_UNPAID,
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
                                $paidAmount = (int) $existingBill->paid_amount;
                                $newAmount  = (int) $billAmount;
                                
                                if ($paidAmount > 0) {
                                    // There is already a payment made on this bill
                                    if ($paidAmount > $newAmount) {
                                        $overpayment = $paidAmount - $newAmount;
                                        if (!$isDryRun) {
                                            $studentModel = \App\Models\Student::find($student->id);
                                            if ($studentModel) {
                                                $oldBalance = $studentModel->saldo;
                                                $studentModel->increment('saldo', $overpayment);
                                                
                                                \App\Models\SaldoHistory::create([
                                                    'student_id' => $student->id,
                                                    'type' => \App\Models\SaldoHistory::TYPE_IN,
                                                    'amount' => $overpayment,
                                                    'description' => 'Alokasi Kelebihan Bayar ' . $billType->name,
                                                    'status' => \App\Models\SaldoHistory::STATUS_SUCCESS,
                                                    'usage' => \App\Models\SaldoHistory::USAGE_BILL,
                                                    'balance_before' => $oldBalance,
                                                    'balance_after' => $oldBalance + $overpayment,
                                                ]);
                                            }
                                            
                                            DB::table('bills')->where('id', $existingBill->id)->update([
                                                'amount' => $newAmount,
                                                'paid_amount' => $newAmount,
                                                'status' => \App\Models\Bill::STATUS_PAID,
                                                'payment_rate_item_id' => $item->id,
                                                'classroom_id'         => $targetClassroomId,
                                                'updated_at'           => $timestamp,
                                            ]);
                                        }
                                        $totalUpdated++;
                                        $this->line("    [ADJUSTMENT] {$student->name} | Kelebihan Rp " . number_format($overpayment, 0, ',', '.') . " dialokasikan ke saldo.");
                                    } elseif ($paidAmount == $newAmount) {
                                        if (!$isDryRun) {
                                            DB::table('bills')->where('id', $existingBill->id)->update([
                                                'amount' => $newAmount,
                                                'paid_amount' => $newAmount,
                                                'status' => \App\Models\Bill::STATUS_PAID,
                                                'payment_rate_item_id' => $item->id,
                                                'classroom_id'         => $targetClassroomId,
                                                'updated_at'           => $timestamp,
                                            ]);
                                        }
                                        $totalUpdated++;
                                        $this->line("    [UPDATE-PAID] {$student->name} | Bulan {$billMonth}/{$billYear} | Status Lunas.");
                                    } else {
                                        // $paidAmount < $newAmount
                                        if (!$isDryRun) {
                                            DB::table('bills')->where('id', $existingBill->id)->update([
                                                'amount' => $newAmount,
                                                'status' => 'PARTIAL',
                                                'payment_rate_item_id' => $item->id,
                                                'classroom_id'         => $targetClassroomId,
                                                'updated_at'           => $timestamp,
                                            ]);
                                        }
                                        $totalUpdated++;
                                        $this->line("    [UPDATE-PARTIAL] {$student->name} | Bulan {$billMonth}/{$billYear} | Status Cicilan (Terbayar: Rp " . number_format($paidAmount, 0, ',', '.') . " dari Rp " . number_format($newAmount, 0, ',', '.') . ").");
                                    }
                                } else {
                                    // paidAmount == 0: UNPAID
                                    if (!$isDryRun) {
                                        DB::table('bills')
                                            ->where('id', $existingBill->id)
                                            ->update([
                                                'amount'               => $newAmount,
                                                'status'               => \App\Models\Bill::STATUS_UNPAID,
                                                'payment_rate_item_id' => $item->id,
                                                'classroom_id'         => $targetClassroomId,
                                                'updated_at'           => $timestamp,
                                            ]);
                                    }
                                    $totalUpdated++;
                                    $this->line("    [UPDATE] {$student->name} | Bulan {$billMonth}/{$billYear} | " .
                                        "Rp " . number_format($existingBill->amount, 0, ',', '.') . " -> Rp " . number_format($newAmount, 0, ',', '.'));
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
                        DB::table('bills')->insertOrIgnore($chunk);
                    }
                    $billsToInsert = [];
                }
            } finally {
                $lock->release();
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
    private function getStudentsForRate(PaymentRate $paymentRate, array $relatedBillTypeIds = []): \Illuminate\Support\Collection
    {
        $billType = $paymentRate->billType;
        $isPondok = false;
        
        if ($billType) {
            $nameToCheck = strtoupper($billType->name . ' ' . ($billType->billItem?->name ?? ''));
            $isPondok = str_contains($nameToCheck, 'PONDOK') || str_contains($nameToCheck, 'PPTQ');
        }

        $query = Student::query();
        
        // Hanya filter status untuk tarif REGULAR. Untuk tarif TRANSFER (susulan), 
        // proses semua siswa yang terdaftar, termasuk yang DROPPED_OUT.
        if ($paymentRate->type === PaymentRate::TYPE_REGULAR) {
            if ($isPondok) {
                $query->whereIn('status', ['ACTIVE', 'GRADUATED']);
            } else {
                $query->where('status', 'ACTIVE');
            }
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
            $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
            if ($billType && $billType->academic_year_id && $activeYear && $billType->academic_year_id != $activeYear->id) {
                // Fetch historical students for this academic year
                $historicalStudentIds = DB::table('student_classroom_histories')
                    ->where('academic_year_id', $billType->academic_year_id)
                    ->whereIn('classroom_id', $classroomIds)
                    ->whereNull('deleted_at')
                    ->pluck('student_id')
                    ->toArray();
                $query->whereIn('id', $historicalStudentIds);
            } else {
                $query->whereIn('classroom_id', $classroomIds);
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

            // Filter by student_sub_status_id (PPTQ Matrix)
            if ($paymentRate->student_sub_status_id) {
                $query->where('student_sub_status_id', $paymentRate->student_sub_status_id);
            }

            // Exclude students who have a Special / Transfer rate for this bill type
            $targetBtIds = !empty($relatedBillTypeIds) ? $relatedBillTypeIds : [$paymentRate->bill_type_id];
            $transferStudentIds = DB::table('payment_rate_students')
                ->join('payment_rates', 'payment_rate_students.payment_rate_id', '=', 'payment_rates.id')
                ->whereIn('payment_rates.bill_type_id', $targetBtIds)
                ->where('payment_rates.type', PaymentRate::TYPE_TRANSFER)
                ->whereNull('payment_rate_students.deleted_at')
                ->whereNull('payment_rates.deleted_at')
                ->pluck('payment_rate_students.student_id')
                ->toArray();
            if (!empty($transferStudentIds)) {
                $query->whereNotIn('id', $transferStudentIds);
            }
        } else {
            $studentIds = $paymentRate->paymentRateStudents->pluck('student_id')->toArray();
            if (empty($studentIds)) {
                return collect([]);
            }
            $query->whereIn('id', $studentIds);
        }

        return $query->get(['id', 'name', 'classroom_id', 'gender', 'user_id', 'student_sub_status_id']);
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

