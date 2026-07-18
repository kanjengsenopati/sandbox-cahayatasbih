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

            foreach ($students as $student) {
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
                    } elseif ($forceUpdate && $existingBill->status === Bill::STATUS_UNPAID) {
                        $needsUpdate = ($existingBill->payment_rate_item_id !== $item->id || (int)$existingBill->amount !== (int)$billAmount);

                        if ($needsUpdate) {
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
        $query = Student::query()->where('status', 'ACTIVE');

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
}
