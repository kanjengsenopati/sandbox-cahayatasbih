<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\BillItem;
use App\Models\BillType;
use App\Models\Classroom;
use App\Models\PaymentRate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegistrationPaymentRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Academic Year: 2026/2027
            $academicYear = AcademicYear::firstOrCreate([
                'name' => '2026/2027',
            ], [
                'start_year' => 2026,
                'end_year' => 2027,
                'is_active' => true,
            ]);

            // 2. Bill Item: REGISTRASI
            $billItem = BillItem::firstOrCreate([
                'name' => 'REGISTRASI',
            ], [
                'description' => 'Registrasi Santri dan Siswa Baru',
            ]);

            // 3. Bill Type: Registrasi Santri dan Siswa Baru 2026/2027
            $billType = BillType::firstOrCreate([
                'bill_item_id' => $billItem->id,
                'academic_year_id' => $academicYear->id,
            ], [
                'name' => 'Registrasi Santri dan Siswa Baru 2026/2027',
                'type' => BillType::TYPE_OTHER,
                'use_wali_filter' => true,
                'use_gender_filter' => false,
            ]);

            // 4. Clean up existing payment rates for this bill type if any (to avoid duplicates on seed re-run)
            $existingRates = $billType->paymentRates;
            foreach ($existingRates as $rate) {
                $rate->paymentRateClassrooms()->forceDelete();
                $rate->paymentRateItems()->forceDelete();
                $rate->forceDelete();
            }

            // 5. Define Rates Configuration
            $ratesConfig = [
                [
                    'amount' => 3000000,
                    'jamaah_status' => 'JAMAAH',
                ],
                [
                    'amount' => 5000000,
                    'jamaah_status' => 'NON_JAMAAH',
                ],
                [
                    'amount' => 1000000,
                    'jamaah_status' => 'MUKIMIN',
                ],
            ];

            $classrooms = Classroom::all();

            foreach ($ratesConfig as $config) {
                // Create Payment Rate
                $paymentRate = $billType->paymentRates()->create([
                    'amount' => $config['amount'],
                    'type' => PaymentRate::TYPE_REGULAR,
                    'jamaah_status' => $config['jamaah_status'],
                    'gender' => null,
                ]);

                // Create Payment Rate Item for month 7 (July) of 2026
                $paymentRate->paymentRateItems()->create([
                    'month' => 7,
                    'year' => 2026,
                    'amount' => $config['amount'],
                ]);

                // Associate all classrooms
                foreach ($classrooms as $classroom) {
                    $paymentRate->paymentRateClassrooms()->create([
                        'classroom_id' => $classroom->id,
                    ]);
                }
            }
        });
    }
}
