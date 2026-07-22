<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\BillType;
use App\Models\Classroom;
use App\Models\PaymentRate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FixRates2026 extends Command
{
    protected $signature = 'bills:fix-rates-2026
                            {--dry-run : Show what would be done without making changes}';

    protected $description = 'Fix master payment rates for TA 2026/2027: Syahriah (12x500k), Biaya Aplikasi (12x10k), Zarkasi (6 angsuran). Cleans duplicates, fixes rate items, maps classrooms. Safe to run multiple times (idempotent).';

    /**
     * Rate configuration - single source of truth
     */
    private array $rateConfig = [
        'SYAHRIAH' => [
            'base_amount' => 6000000,
            'items' => [
                ['m' => 7,  'y' => 2026, 'amount' => 500000],
                ['m' => 8,  'y' => 2026, 'amount' => 500000],
                ['m' => 9,  'y' => 2026, 'amount' => 500000],
                ['m' => 10, 'y' => 2026, 'amount' => 500000],
                ['m' => 11, 'y' => 2026, 'amount' => 500000],
                ['m' => 12, 'y' => 2026, 'amount' => 500000],
                ['m' => 1,  'y' => 2027, 'amount' => 500000],
                ['m' => 2,  'y' => 2027, 'amount' => 500000],
                ['m' => 3,  'y' => 2027, 'amount' => 500000],
                ['m' => 4,  'y' => 2027, 'amount' => 500000],
                ['m' => 5,  'y' => 2027, 'amount' => 500000],
                ['m' => 6,  'y' => 2027, 'amount' => 500000],
            ],
        ],
        'BIAYA APLIKASI' => [
            'base_amount' => 120000,
            'items' => [
                ['m' => 7,  'y' => 2026, 'amount' => 10000],
                ['m' => 8,  'y' => 2026, 'amount' => 10000],
                ['m' => 9,  'y' => 2026, 'amount' => 10000],
                ['m' => 10, 'y' => 2026, 'amount' => 10000],
                ['m' => 11, 'y' => 2026, 'amount' => 10000],
                ['m' => 12, 'y' => 2026, 'amount' => 10000],
                ['m' => 1,  'y' => 2027, 'amount' => 10000],
                ['m' => 2,  'y' => 2027, 'amount' => 10000],
                ['m' => 3,  'y' => 2027, 'amount' => 10000],
                ['m' => 4,  'y' => 2027, 'amount' => 10000],
                ['m' => 5,  'y' => 2027, 'amount' => 10000],
                ['m' => 6,  'y' => 2027, 'amount' => 10000],
            ],
        ],
        'ZARKASI' => [
            'base_amount' => 550000,
            'items' => [
                ['m' => 7,  'y' => 2026, 'amount' => 100000],
                ['m' => 8,  'y' => 2026, 'amount' => 100000],
                ['m' => 9,  'y' => 2026, 'amount' => 100000],
                ['m' => 10, 'y' => 2026, 'amount' => 100000],
                ['m' => 11, 'y' => 2026, 'amount' => 100000],
                ['m' => 12, 'y' => 2026, 'amount' => 50000],
            ],
        ],
    ];

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('=== DRY RUN MODE - No changes will be made ===');
        }

        // 1. Find active Academic Year 2026/2027
        $activeAY = AcademicYear::where('is_active', true)->first();

        if (!$activeAY) {
            $this->error('No active Academic Year found!');
            return Command::FAILURE;
        }

        $this->info("Active Academic Year: {$activeAY->name} (ID: {$activeAY->id})");

        // Verify it's 2026/2027
        if (!str_contains($activeAY->name, '2026') && !str_contains($activeAY->name, '2027')) {
            $this->warn("Active AY is '{$activeAY->name}', not 2026/2027. Proceeding anyway...");
        }

        // 2. Get all active classrooms
        $allClassrooms = Classroom::whereNull('deleted_at')->pluck('id')->toArray();
        $this->info("Active classrooms: " . count($allClassrooms));

        if (empty($allClassrooms)) {
            $this->error('No active classrooms found!');
            return Command::FAILURE;
        }

        if ($isDryRun) {
            $this->newLine();
            $this->info('=== DRY RUN: Previewing fixes ===');
        }

        DB::beginTransaction();

        try {
            foreach ($this->rateConfig as $typeName => $config) {
                $this->newLine();
                $this->info("========================================");
                $this->info("Processing: {$typeName}");
                $this->info("========================================");

                $this->fixBillTypeRate($activeAY, $typeName, $config, $allClassrooms, $isDryRun);
            }

            if (!$isDryRun) {
                DB::commit();
                $this->newLine();
                $this->info('✅ ALL MASTER RATES FIXED SUCCESSFULLY!');
                $this->info('Run "php artisan bills:sync-rate --force" to generate/sync student bills.');
            } else {
                DB::rollBack();
                $this->warn('=== DRY RUN COMPLETE - No changes saved ===');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("ERROR: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    private function fixBillTypeRate(AcademicYear $ay, string $typeName, array $config, array $allClassrooms, bool $isDryRun): void
    {
        // Find all bill_types matching this name for this AY
        $billTypes = BillType::where('academic_year_id', $ay->id)
            ->where(function ($q) use ($typeName) {
                $q->where('name', $typeName)
                  ->orWhere('name', 'LIKE', "%{$typeName}%");
            })
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'asc')
            ->get();

        if ($billTypes->isEmpty()) {
            $this->warn("  No BillType found for '{$typeName}' in AY {$ay->name}. Skipping.");
            return;
        }

        $this->line("  Found {$billTypes->count()} BillType(s) for '{$typeName}'");

        // Process EACH bill_type (not just the first one - production may have per-school bill_types)
        foreach ($billTypes as $billType) {
            $this->line("  ---");
            $this->line("  BillType: {$billType->name} (ID: {$billType->id})");
            $this->line("  BillItem: {$billType->bill_item_id}");

            // Get or create PaymentRate for this bill_type
            $paymentRates = PaymentRate::where('bill_type_id', $billType->id)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'asc')
                ->get();

            $paymentRate = null;

            if ($paymentRates->isEmpty()) {
                // Create new PaymentRate
                if (!$isDryRun) {
                    $paymentRate = PaymentRate::create([
                        'id' => Str::uuid()->toString(),
                        'bill_type_id' => $billType->id,
                        'amount' => $config['base_amount'],
                        'type' => PaymentRate::TYPE_REGULAR,
                    ]);
                }
                $this->info("    [CREATE] PaymentRate | Base: Rp " . number_format($config['base_amount'], 0, ',', '.'));
            } else {
                $paymentRate = $paymentRates->first();

                // Soft-delete duplicates
                if ($paymentRates->count() > 1) {
                    $duplicates = $paymentRates->slice(1);
                    if (!$isDryRun) {
                        PaymentRate::whereIn('id', $duplicates->pluck('id')->toArray())
                            ->update(['deleted_at' => now()]);
                    }
                    $this->warn("    [CLEANUP] Soft-deleted {$duplicates->count()} duplicate PaymentRate(s)");
                }

                // Fix base amount
                if ((int) $paymentRate->amount !== $config['base_amount']) {
                    $oldAmount = $paymentRate->amount;
                    if (!$isDryRun) {
                        $paymentRate->update(['amount' => $config['base_amount']]);
                    }
                    $this->info("    [FIX] Base Amount: Rp " . number_format($oldAmount, 0, ',', '.') . " -> Rp " . number_format($config['base_amount'], 0, ',', '.'));
                } else {
                    $this->line("    [OK] Base Amount: Rp " . number_format($config['base_amount'], 0, ',', '.'));
                }
            }

            if ($isDryRun && !$paymentRate) {
                $this->line("    [DRY RUN] Would create PaymentRate and items");
                continue;
            }

            // Fix PaymentRateItems
            $this->fixRateItems($paymentRate, $config['items'], $isDryRun);

            // Fix classroom mapping
            $this->fixClassroomMapping($paymentRate, $allClassrooms, $isDryRun);
        }
    }

    private function fixRateItems(PaymentRate $paymentRate, array $targetItems, bool $isDryRun): void
    {
        $existingItems = DB::table('payment_rate_items')
            ->where('payment_rate_id', $paymentRate->id)
            ->whereNull('deleted_at')
            ->get()
            ->keyBy(fn($item) => "{$item->month}_{$item->year}");

        $itemsCreated = 0;
        $itemsUpdated = 0;
        $itemsCorrect = 0;

        foreach ($targetItems as $target) {
            $key = "{$target['m']}_{$target['y']}";
            $existing = $existingItems->get($key);

            if (!$existing) {
                // Create missing item
                if (!$isDryRun) {
                    DB::table('payment_rate_items')->insert([
                        'id' => Str::uuid()->toString(),
                        'payment_rate_id' => $paymentRate->id,
                        'month' => $target['m'],
                        'year' => $target['y'],
                        'amount' => $target['amount'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $itemsCreated++;
            } elseif ((int) $existing->amount !== $target['amount']) {
                // Fix wrong amount
                if (!$isDryRun) {
                    DB::table('payment_rate_items')
                        ->where('id', $existing->id)
                        ->update(['amount' => $target['amount'], 'updated_at' => now()]);
                }
                $this->warn("    [FIX ITEM] {$target['m']}/{$target['y']}: Rp " . number_format($existing->amount, 0, ',', '.') . " -> Rp " . number_format($target['amount'], 0, ',', '.'));
                $itemsUpdated++;
            } else {
                $itemsCorrect++;
            }

            // Remove from tracking to find extras
            $existingItems->forget($key);
        }

        // Remove extra items that shouldn't exist
        $extrasDeleted = 0;
        foreach ($existingItems as $extra) {
            if (!$isDryRun) {
                DB::table('payment_rate_items')
                    ->where('id', $extra->id)
                    ->update(['deleted_at' => now()]);
            }
            $extrasDeleted++;
        }

        $this->line("    Items: {$itemsCreated} created, {$itemsUpdated} fixed, {$itemsCorrect} correct, {$extrasDeleted} extras removed");
    }

    private function fixClassroomMapping(BillType $billType, PaymentRate $paymentRate, bool $isDryRun): void
    {
        $existingMappings = DB::table('payment_rate_classrooms')
            ->where('payment_rate_id', $paymentRate->id)
            ->whereNull('deleted_at')
            ->pluck('classroom_id')
            ->toArray();

        // Determine target school ID for this payment rate
        $targetSchoolId = null;

        if (!empty($existingMappings)) {
            // Take the majority school_id among existing mapped classrooms
            $targetSchoolId = DB::table('classrooms')
                ->whereIn('id', $existingMappings)
                ->whereNull('deleted_at')
                ->select('school_id', DB::raw('count(*) as total'))
                ->groupBy('school_id')
                ->orderBy('total', 'desc')
                ->value('school_id');
        }

        if (!$targetSchoolId) {
            // Try matching BillType name or BillItem name to school
            $nameToCheck = strtoupper($billType->name . ' ' . ($billType->billItem?->name ?? ''));
            if (str_contains($nameToCheck, 'MA') || str_contains($nameToCheck, 'ALIYAH')) {
                $targetSchoolId = DB::table('schools')->where('name', 'LIKE', '%MA%')->orWhere('name', 'LIKE', '%ALIYAH%')->value('id');
            } elseif (str_contains($nameToCheck, 'SMP')) {
                $targetSchoolId = DB::table('schools')->where('name', 'LIKE', '%SMP%')->value('id');
            } elseif (str_contains($nameToCheck, 'PONDOK')) {
                $targetSchoolId = DB::table('schools')->where('name', 'LIKE', '%PONDOK%')->value('id');
            }
        }

        // Get classrooms belonging strictly to the target school (or all if no school identified)
        $targetClassroomsQuery = Classroom::whereNull('deleted_at');
        if ($targetSchoolId) {
            $targetClassroomsQuery->where('school_id', $targetSchoolId);
        }
        $targetClassroomIds = $targetClassroomsQuery->pluck('id')->toArray();

        // Clean up invalid cross-school classroom mappings if targetSchoolId is known
        if ($targetSchoolId && !empty($existingMappings)) {
            $invalidMappings = DB::table('classrooms')
                ->whereIn('id', $existingMappings)
                ->where('school_id', '!=', $targetSchoolId)
                ->pluck('id')
                ->toArray();

            if (!empty($invalidMappings)) {
                if (!$isDryRun) {
                    DB::table('payment_rate_classrooms')
                        ->where('payment_rate_id', $paymentRate->id)
                        ->whereIn('classroom_id', $invalidMappings)
                        ->delete();
                }
                $this->warn("    [CLEAN] Removed " . count($invalidMappings) . " cross-school classroom mapping(s)");
                $existingMappings = array_diff($existingMappings, $invalidMappings);
            }
        }

        $missingClassrooms = array_diff($targetClassroomIds, $existingMappings);

        if (empty($missingClassrooms)) {
            $this->line("    Classrooms: All " . count($targetClassroomIds) . " mapped for target school ✓");
            return;
        }

        if (!$isDryRun) {
            $inserts = [];
            foreach ($missingClassrooms as $classroomId) {
                $inserts[] = [
                    'id' => Str::uuid()->toString(),
                    'payment_rate_id' => $paymentRate->id,
                    'classroom_id' => $classroomId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('payment_rate_classrooms')->insert($inserts);
        }

        $this->info("    [MAP] Added " . count($missingClassrooms) . " missing classroom(s) (total target: " . count($targetClassroomIds) . ")");
    }
}
