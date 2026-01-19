<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\BillType;
use App\Models\PaymentRate;
use App\Models\PaymentRateItem;
use App\Models\PpdbTrack;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PsbBillingService
{
    /**
     * Ensure BillType and PaymentRate exist for a Track.
     * Called when a Track is created/updated with an installment plan.
     */
    public function syncTrackBillType(PpdbTrack $track): void
    {
        if (!$track->installment_plan || empty($track->installment_plan)) {
            return;
        }

        try {
            DB::beginTransaction();

            // 1. Find or Create BillItem for PSB Daftar Ulang
            $billItem = BillItem::firstOrCreate(
                ['name' => 'Daftar Ulang PSB'],
                ['description' => 'Biaya Daftar Ulang Penerimaan Santri Baru']
            );

            // 2. Get Academic Year from the Wave
            $academicYearId = $track->ppdbWave->academic_year_id
                ?? AcademicYear::where('is_active', true)->first()?->id;

            // 3. Create or Get BillType if not set
            if (!$track->bill_type_id) {
                $schoolName = $track->school->name ?? 'Unknown';
                $trackType = ucfirst(strtolower($track->registration_type ?? 'Reguler'));
                $trackName = $track->name ?? $trackType;

                $billType = BillType::create([
                    'bill_item_id' => $billItem->id,
                    'academic_year_id' => $academicYearId,
                    'name' => "Daftar Ulang - {$schoolName} - {$trackName}",
                    'type' => BillType::TYPE_MONTHLY,
                ]);

                $track->bill_type_id = $billType->id;
                $track->save();
            } else {
                $billType = BillType::find($track->bill_type_id);
            }

            // 4. Ensure PaymentRate exists
            $totalAmount = collect($track->installment_plan)->sum('amount');

            $paymentRate = PaymentRate::firstOrCreate(
                ['bill_type_id' => $billType->id],
                ['amount' => $totalAmount]
            );

            // 5. Update PaymentRate amount if changed
            if ($paymentRate->amount !== $totalAmount) {
                $paymentRate->update(['amount' => $totalAmount]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PsbBillingService::syncTrackBillType Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate Bill records for an accepted student based on track's installment plan.
     */
    public function generateBillsForStudent(Student $student, PpdbTrack $track): void
    {
        if (!$track->installment_plan || empty($track->installment_plan)) {
            return;
        }

        // Ensure BillType is synced
        if (!$track->bill_type_id) {
            $this->syncTrackBillType($track);
            $track->refresh();
        }

        if (!$track->bill_type_id) {
            Log::warning("Cannot generate bills: Track {$track->id} has no bill_type_id after sync.");
            return;
        }

        try {
            DB::beginTransaction();

            // Get context
            $billType = BillType::find($track->bill_type_id);
            $academicYear = $track->ppdbWave->academicYear
                ?? AcademicYear::where('is_active', true)->first();

            if (!$academicYear) {
                throw new \Exception('No academic year found for billing.');
            }

            // Extract start year from academic year name (e.g., "2026/2027" -> 2026)
            $startYear = $this->extractStartYear($academicYear->name);

            // Get or create PaymentRate
            $paymentRate = PaymentRate::firstOrCreate(
                ['bill_type_id' => $billType->id],
                ['amount' => collect($track->installment_plan)->sum('amount')]
            );

            // Process each installment item
            foreach ($track->installment_plan as $item) {
                $month = (int) $item['month'];
                $yearOffset = (int) ($item['year_offset'] ?? 0);
                $amount = (int) $item['amount'];

                // Calculate the actual billing year
                $billYear = $startYear + $yearOffset;

                // Find or create PaymentRateItem
                $paymentRateItem = PaymentRateItem::firstOrCreate(
                    [
                        'payment_rate_id' => $paymentRate->id,
                        'month' => $month,
                        'year' => $billYear,
                    ],
                    ['amount' => $amount]
                );

                // Check if bill already exists
                $existingBill = Bill::where('student_id', $student->id)
                    ->where('bill_type_id', $billType->id)
                    ->where('month', $month)
                    ->where('year', $billYear)
                    ->first();

                if (!$existingBill) {
                    Bill::create([
                        'student_id' => $student->id,
                        'bill_type_id' => $billType->id,
                        'classroom_id' => $student->classroom_id,
                        'academic_year_id' => $academicYear->id,
                        'payment_rate_item_id' => $paymentRateItem->id,
                        'month' => $month,
                        'year' => $billYear,
                        'amount' => $amount,
                        'status' => Bill::STATUS_UNPAID,
                    ]);
                }
            }

            DB::commit();
            Log::info("Generated bills for student {$student->id} from track {$track->id}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PsbBillingService::generateBillsForStudent Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Extract start year from academic year name.
     * E.g., "2026/2027" -> 2026, "TA 2026-2027" -> 2026
     */
    private function extractStartYear(string $name): int
    {
        preg_match('/(\d{4})/', $name, $matches);
        return isset($matches[1]) ? (int) $matches[1] : (int) date('Y');
    }
}
