<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\BillType;
use App\Models\Classroom;
use App\Models\PaymentRate;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixCrossSchoolRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:fix-cross-school-rates
                            {--dry-run : Perform dry run without making database changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit and clean up PaymentRates containing classrooms from multiple schools, and remove invalid UNPAID bills.';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('=== DRY RUN MODE - No database changes will be executed ===');
        }

        $this->info('Starting audit of PaymentRates for cross-school classroom mappings...');

        $paymentRates = PaymentRate::with(['billType.billItem', 'paymentRateClassrooms.classroom.school'])
            ->where('type', PaymentRate::TYPE_REGULAR)
            ->whereNull('deleted_at')
            ->get();

        $totalCleanedRates = 0;
        $totalRemovedMappings = 0;
        $totalDeletedBills = 0;

        foreach ($paymentRates as $rate) {
            $billType = $rate->billType;
            if (!$billType) continue;

            $classrooms = $rate->paymentRateClassrooms->map(fn($prc) => $prc->classroom)->filter();
            if ($classrooms->isEmpty()) continue;

            // Check if classrooms span multiple schools
            $schoolIds = $classrooms->pluck('school_id')->unique()->values()->toArray();

            if (count($schoolIds) <= 1) {
                continue; // Pure single-school rate, no action needed
            }

            $this->newLine();
            $this->warn("Found multi-school PaymentRate ID: {$rate->id} | BillType: {$billType->name} (ID: {$billType->id})");

            // Determine target school ID for this payment rate
            $targetSchoolId = null;

            // Match based on BillType name or BillItem name
            $nameToCheck = strtoupper($billType->name . ' ' . ($billType->billItem?->name ?? ''));
            if (str_contains($nameToCheck, 'MA') || str_contains($nameToCheck, 'ALIYAH')) {
                $targetSchoolId = DB::table('schools')->where('name', 'LIKE', '%MA%')->orWhere('name', 'LIKE', '%ALIYAH%')->value('id');
            } elseif (str_contains($nameToCheck, 'SMP')) {
                $targetSchoolId = DB::table('schools')->where('name', 'LIKE', '%SMP%')->value('id');
            } elseif (str_contains($nameToCheck, 'PONDOK')) {
                $targetSchoolId = DB::table('schools')->where('name', 'LIKE', '%PONDOK%')->value('id');
            }

            if (!$targetSchoolId) {
                // Majority rule among current mapped classrooms
                $targetSchoolId = $classrooms->groupBy('school_id')
                    ->sortByDesc(fn($group) => $group->count())
                    ->keys()
                    ->first();
            }

            $targetSchoolName = DB::table('schools')->where('id', $targetSchoolId)->value('name') ?? $targetSchoolId;
            $this->info("  Target School identified: {$targetSchoolName}");

            // Identify classrooms that belong to OTHER schools
            $invalidClassroomIds = $classrooms->where('school_id', '!=', $targetSchoolId)->pluck('id')->toArray();
            $invalidClassroomNames = $classrooms->where('school_id', '!=', $targetSchoolId)->pluck('name')->implode(', ');

            $this->warn("  Removing invalid classrooms from other schools: {$invalidClassroomNames}");

            if (!empty($invalidClassroomIds)) {
                // Delete UNPAID bills created for students in these invalid classrooms under this bill_type
                $queryBills = Bill::where('bill_type_id', $billType->id)
                    ->whereIn('classroom_id', $invalidClassroomIds)
                    ->where('status', Bill::STATUS_UNPAID);

                $billsCount = $queryBills->count();

                if (!$isDryRun) {
                    $queryBills->delete();
                    DB::table('payment_rate_classrooms')
                        ->where('payment_rate_id', $rate->id)
                        ->whereIn('classroom_id', $invalidClassroomIds)
                        ->delete();
                }

                $totalCleanedRates++;
                $totalRemovedMappings += count($invalidClassroomIds);
                $totalDeletedBills += $billsCount;

                $this->info("  [CLEANED] Removed " . count($invalidClassroomIds) . " classroom mapping(s) and {$billsCount} UNPAID bill(s).");
            }
        }

        $this->newLine();
        $this->info("==========================================");
        $this->info("AUDIT & CLEANUP COMPLETE");
        $this->info("PaymentRates Cleaned : {$totalCleanedRates}");
        $this->info("Mappings Removed    : {$totalRemovedMappings}");
        $this->info("Unpaid Bills Deleted : {$totalDeletedBills}");
        $this->info("==========================================");

        if ($isDryRun) {
            $this->warn('=== DRY RUN MODE COMPLETE - No changes saved ===');
        }

        return Command::SUCCESS;
    }
}
