<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\BillType;
use App\Models\PaymentRate;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckBillClass extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-bill-class';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and create missing bills for students based on active academic year and payment rates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Fetch active academic year's bill types
        $billTypes = BillType::whereHas(
            'academicYear',
            function ($query) {
                $query->where('is_active', true);
            }
        )->get();

        $totalCreated = 0;

        foreach ($billTypes as $billType) {
            $paymentRates = PaymentRate::with(['paymentRateClassrooms', 'paymentRateStudents', 'paymentRateItems'])
                ->where('bill_type_id', $billType->id)
                ->get();

            foreach ($paymentRates as $paymentRate) {
                if ($paymentRate->paymentRateItems->isEmpty()) {
                    continue;
                }

                $students = $this->getStudentsForRate($paymentRate);

                if ($students->isEmpty()) {
                    continue;
                }

                $timestamp = now();
                $billsToInsert = [];
                $startYear = $billType->academicYear?->getStartYearSafe();

                foreach ($students as $student) {
                    // Prevent generating bills for years before the student's entry year
                    if ($startYear !== null && $student->getEntryYear() > $startYear) {
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
                            $allowedClassroomIds = $paymentRate->paymentRateClassrooms->pluck('classroom_id')->toArray();
                            if (!empty($allowedClassroomIds) && !in_array($history->classroom_id, $allowedClassroomIds)) {
                                continue;
                            }
                        }
                    }

                    foreach ($paymentRate->paymentRateItems as $item) {
                        // Check if bill exists
                        $exists = DB::table('bills')
                            ->where('student_id', $student->id)
                            ->where('bill_type_id', $billType->id)
                            ->where('month', $item->month)
                            ->where('year', $item->year)
                            ->whereNull('deleted_at')
                            ->exists();

                        if (!$exists) {
                            $billsToInsert[] = [
                                'id'                   => Str::uuid()->toString(),
                                'bill_type_id'         => $billType->id,
                                'classroom_id'         => $student->classroom_id,
                                'student_id'           => $student->id,
                                'academic_year_id'     => $billType->academic_year_id,
                                'month'                => $item->month,
                                'year'                 => $item->year,
                                'amount'               => $item->amount,
                                'paid_amount'          => 0,
                                'status'               => Bill::STATUS_UNPAID,
                                'payment_rate_item_id' => $item->id,
                                'created_at'           => $timestamp,
                                'updated_at'           => $timestamp,
                            ];
                            $totalCreated++;
                        }
                    }
                }

                if (!empty($billsToInsert)) {
                    foreach (array_chunk($billsToInsert, 500) as $chunk) {
                        DB::table('bills')->insert($chunk);
                    }
                }
            }
        }

        $this->info("Completed checking and syncing bills. Created {$totalCreated} missing bills.");
    }

    /**
     * Get students matching a PaymentRate's filters
     */
    private function getStudentsForRate(PaymentRate $paymentRate): \Illuminate\Support\Collection
    {
        $query = Student::query()->where('status', 'ACTIVE');

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

        if ($paymentRate->gender) {
            $query->whereIn('gender', array_map('trim', explode(',', $paymentRate->gender)));
        }

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
     * Resolve target UPT (School ID) for a BillType based on the 3 main UPTs: SMP, MA, PONDOK
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
