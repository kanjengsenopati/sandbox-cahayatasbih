<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class SyncMasterDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:sync-master {--all : Sync all records instead of only last 30 days} {--tables= : Comma-separated list of tables to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Incremental sync of the last 30 days of transactions and students data from master to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database synchronization from master...');
        $startTime = now();
        $oneMonthAgo = now()->subDays(30)->toDateTimeString();
        $syncAll = $this->option('all');
        $syncTablesOption = $this->option('tables');

        $tables = [
            'schools',
            'outlets',
            'admin_outlets',
            'category_items',
            'items',
            'stock_histories',
            'academic_years',
            'bill_items',
            'bill_types',
            'banks',
            'bill_type_banks',
            'topup_banks',
            'payment_rates',
            'payment_rate_classrooms',
            'payment_rate_students',
            'payment_rate_items',
            'students',
            'student_classroom_histories',
            'bills',
            'transaction_proofs',
            'point_of_sale_carts',
            'point_of_sale_transactions',
            'point_of_sale_transaction_details',
            'cash_flow_categories',
            'cash_flows',
            'transactions',
            'transaction_details',
            'saldo_histories',
            'saving_histories'
        ];

        $report = [];
        $hasErrors = false;
        $errorMessage = '';

        // Clean up previous stuck running sync logs in database (older than 15 minutes)
        try {
            DB::connection('mysql')->table('database_sync_logs')
                ->where('status', 'running')
                ->where('started_at', '<', now()->subMinutes(15))
                ->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                    'duration' => 120,
                    'error' => 'Proses sinkronisasi terhenti secara tidak terduga (Stuck/Timeout/Server Restart).'
                ]);
        } catch (\Throwable $e) {
            $this->warn('Could not clean up stuck database logs: ' . $e->getMessage());
        }

        // Mark sync as running
        $cacheData = [
            'status' => 'running',
            'started_at' => $startTime->toDateTimeString(),
            'finished_at' => null,
            'report' => [],
            'error' => null
        ];
        Cache::put('last_db_sync_status', $cacheData, 1800); // 30 minutes expire

        // Persist to database log
        try {
            $dbLog = \App\Models\DatabaseSyncLog::create($cacheData);
        } catch (\Throwable $e) {
            $this->warn('Could not create database log: ' . $e->getMessage());
        }

        // Start database transaction and disable FK checks to ensure atomicity across the entire sync process
        try {
            DB::connection('mysql')->beginTransaction();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');

            // Build classroom ID mapping to map old formats (e.g. 10-D, X-D) to local standardized names
            $classroomMapping = [];
            $defaultOutletId = null;
            try {
                $koperasi = DB::connection('mysql')->table('outlets')->where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
                $defaultOutletId = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';
            } catch (\Throwable $e) {
                $defaultOutletId = '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';
            }

            $masterClassrooms = DB::connection('mysql_master')->table('classrooms')->get();
            $localClassrooms = DB::connection('mysql')->table('classrooms')->get();
            
            // Group only ACTIVE local classrooms by school_id and normalized name
            $localGroups = [];
            foreach ($localClassrooms as $lc) {
                if ($lc->deleted_at === null) {
                    $norm = $this->normalizeClassroomName($lc->name);
                    $localGroups[$lc->school_id][$norm] = $lc->id;
                }
            }
            
            foreach ($masterClassrooms as $mc) {
                $norm = $this->normalizeClassroomName($mc->name);
                
                // Only merge if the master classroom is ACTIVE and we have an active local classroom matching its normalized name
                if ($mc->deleted_at === null && isset($localGroups[$mc->school_id][$norm])) {
                    $localId = $localGroups[$mc->school_id][$norm];
                } else {
                    // For soft-deleted master classrooms, or when no matching local active classroom exists,
                    // we keep the master ID to prevent merging deleted classrooms into active ones.
                    $localId = $mc->id;
                    $exists = DB::connection('mysql')->table('classrooms')->where('id', $mc->id)->exists();
                    if (!$exists) {
                        DB::connection('mysql')->table('classrooms')->insert([
                            'id' => $localId,
                            'school_id' => $mc->school_id,
                            'name' => $mc->deleted_at !== null ? ($mc->name ?? 'Kelas Dihapus') : $norm,
                            'created_at' => $mc->created_at ?? now(),
                            'updated_at' => $mc->updated_at ?? now(),
                            'deleted_at' => $mc->deleted_at ?? null,
                        ]);
                    } else {
                        // If it exists but is soft-deleted, ensure local deleted_at is updated
                        if ($mc->deleted_at !== null) {
                            DB::connection('mysql')->table('classrooms')
                                ->where('id', $localId)
                                ->update(['deleted_at' => $mc->deleted_at]);
                        }
                    }
                }
                
                $classroomMapping[$mc->id] = $localId;
            }
            $this->info("Built classroom mapping for " . count($classroomMapping) . " classrooms.");

            $localBillsMap = null;

            foreach ($tables as $table) {
                if ($syncTablesOption) {
                    $selectedTables = array_map('trim', explode(',', $syncTablesOption));
                    if (!in_array($table, $selectedTables)) {
                        $this->info("Skipping table (not selected): {$table}");
                        $report[$table] = ['status' => 'skipped', 'message' => 'Tidak Dipilih'];
                        continue;
                    }
                }
                
                $this->info("Syncing table: {$table}");
                
                // Safety check: verify table exists in both connections
                if (!Schema::connection('mysql')->hasTable($table)) {
                    $this->warn("Table {$table} does not exist in target database.");
                    $report[$table] = ['status' => 'skipped', 'message' => 'Table missing in target'];
                    continue;
                }
                if (!Schema::connection('mysql_master')->hasTable($table)) {
                    $this->warn("Table {$table} does not exist in master database.");
                    $report[$table] = ['status' => 'skipped', 'message' => 'Table missing in master'];
                    continue;
                }

                // Get common columns to prevent schema drift errors
                $targetColumns = Schema::connection('mysql')->getColumnListing($table);
                $masterColumns = Schema::connection('mysql_master')->getColumnListing($table);
                $commonColumns = array_intersect($targetColumns, $masterColumns);

                // Filter out generated columns since they cannot be written to
                try {
                    $generatedColumns = DB::connection('mysql')
                        ->select("
                            SELECT COLUMN_NAME 
                            FROM INFORMATION_SCHEMA.COLUMNS 
                            WHERE TABLE_SCHEMA = ? 
                              AND TABLE_NAME = ? 
                              AND EXTRA LIKE '%GENERATED%'
                        ", [DB::connection('mysql')->getDatabaseName(), $table]);
                    
                    $generatedColumnNames = array_map(fn($col) => $col->COLUMN_NAME, $generatedColumns);
                    $commonColumns = array_diff($commonColumns, $generatedColumnNames);
                } catch (\Throwable $e) {
                    // Fallback to manual exclusion if query fails
                    if ($table === 'bills') {
                        $commonColumns = array_diff($commonColumns, ['active_status']);
                    }
                }

                if (empty($commonColumns)) {
                    $this->warn("No common columns found for table: {$table}");
                    $report[$table] = ['status' => 'skipped', 'message' => 'No common columns'];
                    continue;
                }

                // Query builder for master database
                $query = DB::connection('mysql_master')
                    ->table($table)
                    ->select($commonColumns);

                // Add time window if time columns exist
                $hasCreatedAt = in_array('created_at', $commonColumns);
                $hasUpdatedAt = in_array('updated_at', $commonColumns);
                // Configuration/reference tables should be synced fully to avoid missing references
                $isConfigTable = in_array($table, [
                    'schools',
                    'outlets',
                    'admin_outlets',
                    'category_items',
                    'items',
                    'academic_years',
                    'bill_items',
                    'bill_types',
                    'banks',
                    'bill_type_banks',
                    'topup_banks',
                    'payment_rates',
                    'payment_rate_classrooms',
                    'payment_rate_students',
                    'payment_rate_items',
                    'cash_flow_categories',
                    'students',
                    'student_classroom_histories',
                    'bills'
                ]);
                if (($hasCreatedAt || $hasUpdatedAt) && !$isConfigTable && !$syncAll) {
                    $query->where(function ($q) use ($oneMonthAgo, $hasCreatedAt, $hasUpdatedAt) {
                        if ($hasCreatedAt) {
                            $q->orWhere('created_at', '>=', $oneMonthAgo);
                        }
                        if ($hasUpdatedAt) {
                            $q->orWhere('updated_at', '>=', $oneMonthAgo);
                        }
                    });
                }

                $inserted = 0;
                $hasId = in_array('id', $commonColumns);
                
                // Track min and max dates of records being synced
                $minTimestamp = null;
                $maxTimestamp = null;

                $processChunk = function ($rows) use ($table, $commonColumns, &$inserted, &$minTimestamp, &$maxTimestamp, $classroomMapping, $targetColumns, $defaultOutletId, &$localBillsMap) {
                    $data = $rows->map(fn($row) => (array) $row)->toArray();
                    if (!empty($data)) {
                        $columnsToUpdate = array_filter($commonColumns, fn($col) => $col !== 'id');
                        
                        // Check if the target table has an outlet_id column but the master table does not
                        $targetHasOutlet = in_array('outlet_id', $targetColumns);
                        $masterHasOutlet = in_array('outlet_id', $commonColumns);
                        $assignDefaultOutlet = $targetHasOutlet && !$masterHasOutlet && !is_null($defaultOutletId);

                        if ($assignDefaultOutlet) {
                            $columnsToUpdate[] = 'outlet_id';
                        }
                        
                        // Pre-load local students for chunk optimization
                        $localStudents = collect();
                        if ($table === 'students') {
                            $studentIds = array_column($data, 'id');
                            $localStudents = DB::connection('mysql')->table('students')
                                ->whereIn('id', $studentIds)
                                ->select('id', 'saldo', 'saving')
                                ->get()
                                ->keyBy('id');
                        }

                        // Identify date range and map classroom IDs
                        foreach ($data as &$row) {
                            if ($assignDefaultOutlet) {
                                $row['outlet_id'] = $defaultOutletId;
                            }
                            
                            // Map classroom_id
                            if (isset($row['classroom_id']) && isset($classroomMapping[$row['classroom_id']])) {
                                $row['classroom_id'] = $classroomMapping[$row['classroom_id']];
                            }
                            // Map to_classroom_id
                            if (isset($row['to_classroom_id']) && isset($classroomMapping[$row['to_classroom_id']])) {
                                $row['to_classroom_id'] = $classroomMapping[$row['to_classroom_id']];
                            }

                            // Check student balance difference and log adjustment
                            if ($table === 'students' && isset($row['id']) && isset($row['saldo'])) {
                                $localStudent = $localStudents->get($row['id']);
                                if ($localStudent && isset($localStudent->saldo)) {
                                    $diff = $row['saldo'] - $localStudent->saldo;
                                    if ($diff != 0) {
                                        DB::connection('mysql')->table('saldo_histories')->insert([
                                            'id' => (string) \Illuminate\Support\Str::uuid(),
                                            'student_id' => $row['id'],
                                            'type' => $diff > 0 ? 'IN' : 'OUT',
                                            'amount' => abs($diff),
                                            'description' => 'Adjustment sinkronisasi master (selisih saldo)',
                                            'status' => 'SUCCESS',
                                            'usage' => $diff > 0 ? 'TOPUP' : 'BILL',
                                            'balance_before' => $localStudent->saldo,
                                            'balance_after' => $row['saldo'],
                                            'created_at' => now(),
                                            'updated_at' => now(),
                                        ]);
                                    }
                                }
                            }

                            // Check student saving difference and log adjustment
                            if ($table === 'students' && isset($row['id']) && isset($row['saving'])) {
                                $localStudent = $localStudents->get($row['id']);
                                if ($localStudent && isset($localStudent->saving)) {
                                    $diff = $row['saving'] - $localStudent->saving;
                                    if ($diff != 0) {
                                        DB::connection('mysql')->table('saving_histories')->insert([
                                            'id' => (string) \Illuminate\Support\Str::uuid(),
                                            'student_id' => $row['id'],
                                            'type' => $diff > 0 ? 'IN' : 'OUT',
                                            'amount' => abs($diff),
                                            'description' => 'Adjustment sinkronisasi master (selisih tabungan)',
                                            'status' => 'SUCCESS',
                                            'date' => now(),
                                            'created_at' => now(),
                                            'updated_at' => now(),
                                        ]);
                                    }
                                }
                            }

                            $dateStr = $row['created_at'] ?? $row['updated_at'] ?? null;
                            if ($dateStr) {
                                $ts = strtotime($dateStr);
                                if ($ts) {
                                    if (is_null($minTimestamp) || $ts < $minTimestamp) {
                                        $minTimestamp = $ts;
                                    }
                                    if (is_null($maxTimestamp) || $ts > $maxTimestamp) {
                                        $maxTimestamp = $ts;
                                    }
                                }
                            }
                        }
                        unset($row);

                        // Deduplicate and resolve UUID collisions for bills table
                        if ($table === 'bills') {
                            // 1. Deduplicate active bills within the incoming chunk itself
                            $chunkGroups = [];
                            foreach ($data as $index => $row) {
                                if (is_null($row['deleted_at'])) {
                                    $key = $row['student_id'] . '_' . 
                                           $row['bill_type_id'] . '_' . 
                                           $row['academic_year_id'] . '_' . 
                                           $row['month'] . '_' . 
                                           $row['year'];
                                    $chunkGroups[$key][] = [
                                        'index' => $index,
                                        'id' => $row['id'],
                                        'status' => $row['status'] ?? 'UNPAID',
                                        'paid_amount' => $row['paid_amount'] ?? 0,
                                        'updated_at' => $row['updated_at'] ?? null,
                                        'created_at' => $row['created_at'] ?? null,
                                    ];
                                }
                            }

                            foreach ($chunkGroups as $key => $groupBills) {
                                if (count($groupBills) > 1) {
                                    usort($groupBills, function($a, $b) {
                                        $aPaid = ($a['status'] === 'PAID' || $a['paid_amount'] > 0);
                                        $bPaid = ($b['status'] === 'PAID' || $b['paid_amount'] > 0);
                                        if ($aPaid !== $bPaid) {
                                            return $bPaid <=> $aPaid;
                                        }
                                        if ($a['paid_amount'] != $b['paid_amount']) {
                                            return $b['paid_amount'] <=> $a['paid_amount'];
                                        }
                                        $aTime = strtotime($a['updated_at'] ?? $a['created_at'] ?? '1970-01-01');
                                        $bTime = strtotime($b['updated_at'] ?? $b['created_at'] ?? '1970-01-01');
                                        return $bTime <=> $aTime;
                                    });

                                    $keepId = $groupBills[0]['id'];
                                    foreach ($groupBills as $gb) {
                                        if ($gb['id'] !== $keepId) {
                                            $data[$gb['index']]['deleted_at'] = now()->toDateTimeString();
                                        }
                                    }
                                }
                            }

                            // 2. Resolve ID collisions with existing active bills in local database
                            // Load existing bills lookup map if not loaded
                            if (is_null($localBillsMap)) {
                                $this->info("Loading existing active bills map from local database...");
                                $localBillsMap = [];
                                DB::connection('mysql')->table('bills')
                                    ->whereNull('deleted_at')
                                    ->select('id', 'student_id', 'bill_type_id', 'academic_year_id', 'month', 'year')
                                    ->chunkById(10000, function($bills) use (&$localBillsMap) {
                                        foreach ($bills as $b) {
                                            $key = $b->student_id . '_' . 
                                                   $b->bill_type_id . '_' . 
                                                   $b->academic_year_id . '_' . 
                                                   $b->month . '_' . 
                                                   $b->year;
                                            $localBillsMap[$key] = $b->id;
                                        }
                                    });
                                $this->info("Loaded " . count($localBillsMap) . " bills into lookup map.");
                            }

                            foreach ($data as &$row) {
                                if (is_null($row['deleted_at'])) {
                                    $key = $row['student_id'] . '_' . 
                                           $row['bill_type_id'] . '_' . 
                                           $row['academic_year_id'] . '_' . 
                                           $row['month'] . '_' . 
                                           $row['year'];
                                    
                                    if (isset($localBillsMap[$key])) {
                                        $localBillId = $localBillsMap[$key];
                                        if ($localBillId !== $row['id']) {
                                            $row['id'] = $localBillId;
                                        }
                                    }
                                }
                            }
                            unset($row);
                        }

                        // Run upsert operation
                        DB::connection('mysql')
                            ->table($table)
                            ->upsert($data, ['id'], $columnsToUpdate);
                        $inserted += count($data);
                    }
                };

                if ($hasId) {
                    $query->chunkById(500, $processChunk);
                } else {
                    $query->chunk(500, $processChunk);
                }

                $this->info("Synced {$inserted} rows for table: {$table}");
                $report[$table] = [
                    'status' => 'success',
                    'rows_synced' => $inserted,
                    'min_date' => $minTimestamp ? date('Y-m-d H:i:s', $minTimestamp) : null,
                    'max_date' => $maxTimestamp ? date('Y-m-d H:i:s', $maxTimestamp) : null,
                    'message' => "Successfully synced {$inserted} rows"
                ];
            }

            // Recalculate bill paid_amount and status to maintain consistency and integrity
            $this->info("Recalculating paid_amount and status for all bills...");
            
            // 1. Recalculate paid_amount for all bills using successful transaction details
            DB::connection('mysql')->statement("
                UPDATE bills 
                LEFT JOIN (
                    SELECT 
                        td.bill_id, 
                        SUM(COALESCE(td.amount, b.amount)) AS total_paid
                    FROM transaction_details td
                    JOIN transactions t ON td.transaction_id = t.id
                    JOIN bills b ON td.bill_id = b.id
                    WHERE t.status IN ('PAID', 'approved', 'SUCCESS')
                      AND td.deleted_at IS NULL
                      AND t.deleted_at IS NULL
                    GROUP BY td.bill_id
                ) AS payments ON bills.id = payments.bill_id
                SET bills.paid_amount = COALESCE(payments.total_paid, 0)
            ");
            
            // 2. For any bill where paid_amount >= amount, status must be PAID
            DB::connection('mysql')->table('bills')
                ->whereRaw('paid_amount >= amount')
                ->update(['status' => 'PAID']);
                
            // 3. For any bill where status is PAID but paid_amount is less than amount,
            // we must set paid_amount = amount to ensure UI consistency.
            DB::connection('mysql')->table('bills')
                ->where('status', 'PAID')
                ->whereRaw('paid_amount < amount')
                ->update(['paid_amount' => DB::raw('amount')]);

            // 4. Soft-delete payment_rate_classrooms pointing to soft-deleted classrooms
            $this->info("Cleaning up orphaned payment_rate_classrooms...");
            $deletedClassroomIds = DB::connection('mysql')->table('classrooms')->whereNotNull('deleted_at')->pluck('id');
            if ($deletedClassroomIds->isNotEmpty()) {
                DB::connection('mysql')->table('payment_rate_classrooms')
                    ->whereIn('classroom_id', $deletedClassroomIds)
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => now()]);
            }

            // 5. Soft-delete payment_rate_students pointing to soft-deleted students
            $this->info("Cleaning up orphaned payment_rate_students...");
            $deletedStudentIds = DB::connection('mysql')->table('students')->whereNotNull('deleted_at')->pluck('id');
            if ($deletedStudentIds->isNotEmpty()) {
                DB::connection('mysql')->table('payment_rate_students')
                    ->whereIn('student_id', $deletedStudentIds)
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => now()]);
            }

            // 6. Automatically resolve duplicate active classroom payment rate mappings
            $this->info("Resolving duplicate active classroom payment rate mappings...");
            $duplicates = DB::connection('mysql')->table('payment_rate_classrooms as prc')
                ->join('payment_rates as pr', 'prc.payment_rate_id', '=', 'pr.id')
                ->whereNull('prc.deleted_at')
                ->whereNull('pr.deleted_at')
                ->select('pr.bill_type_id', 'prc.classroom_id', DB::raw('COUNT(*) as qty'))
                ->groupBy('pr.bill_type_id', 'prc.classroom_id')
                ->having('qty', '>', 1)
                ->get();

            foreach ($duplicates as $dup) {
                $targetClassroom = DB::connection('mysql')->table('classrooms')->where('id', $dup->classroom_id)->first();
                if (!$targetClassroom) continue;

                $targetName = $targetClassroom->name;
                
                $getGradePrefix = function(?string $name): ?int {
                    if (!$name) return null;
                    if (preg_match('/^([0-9]+)/', $name, $matches)) {
                        return (int) $matches[1];
                    }
                    return null;
                };
                
                $targetGrade = $getGradePrefix($targetName);

                $mappings = DB::connection('mysql')->table('payment_rate_classrooms as prc')
                    ->join('payment_rates as pr', 'prc.payment_rate_id', '=', 'pr.id')
                    ->where('pr.bill_type_id', $dup->bill_type_id)
                    ->where('prc.classroom_id', $dup->classroom_id)
                    ->whereNull('prc.deleted_at')
                    ->whereNull('pr.deleted_at')
                    ->select('prc.id as mapping_id', 'pr.id as rate_id')
                    ->get();

                $evaluatedMappings = [];
                foreach ($mappings as $m) {
                    $otherClassroomNames = DB::connection('mysql')->table('payment_rate_classrooms as prc')
                        ->join('classrooms as c', 'prc.classroom_id', '=', 'c.id')
                        ->where('prc.payment_rate_id', $m->rate_id)
                        ->where('prc.classroom_id', '!=', $dup->classroom_id)
                        ->whereNull('prc.deleted_at')
                        ->whereNull('c.deleted_at')
                        ->pluck('c.name')
                        ->toArray();

                    $score = 0;
                    if ($targetGrade !== null) {
                        foreach ($otherClassroomNames as $otherName) {
                            if ($getGradePrefix($otherName) === $targetGrade) {
                                $score++;
                            }
                        }
                    }

                    $evaluatedMappings[] = [
                        'mapping' => $m,
                        'score' => $score,
                        'other_classes_count' => count($otherClassroomNames)
                    ];
                }

                usort($evaluatedMappings, function ($a, $b) {
                    if ($a['score'] !== $b['score']) {
                        return $b['score'] <=> $a['score'];
                    }
                    return $b['other_classes_count'] <=> $a['other_classes_count'];
                });

                $correctMappingId = $evaluatedMappings[0]['mapping']->mapping_id;

                foreach ($evaluatedMappings as $eval) {
                    $m = $eval['mapping'];
                    if ($m->mapping_id !== $correctMappingId) {
                        DB::connection('mysql')->table('payment_rate_classrooms')
                            ->where('id', $m->mapping_id)
                            ->update(['deleted_at' => now()]);

                        // Soft-delete unpaid bills generated from the incorrect mapping
                        $wrongRateItemIds = DB::connection('mysql')->table('payment_rate_items')
                            ->where('payment_rate_id', $m->rate_id)
                            ->pluck('id');

                        if ($wrongRateItemIds->isNotEmpty()) {
                            $studentIdsInClass = DB::connection('mysql')->table('students')
                                ->where('classroom_id', $dup->classroom_id)
                                ->pluck('id');

                            if ($studentIdsInClass->isNotEmpty()) {
                                DB::connection('mysql')->table('bills')
                                    ->whereIn('student_id', $studentIdsInClass)
                                    ->whereIn('payment_rate_item_id', $wrongRateItemIds)
                                    ->where('status', 'UNPAID')
                                    ->whereNull('deleted_at')
                                    ->update(['deleted_at' => now()]);
                            }
                        }
                    }
                }
            }
 
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::connection('mysql')->commit();
        } catch (\Throwable $e) {
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::connection('mysql')->rollBack();
            $hasErrors = true;
            $errorMessage = $e->getMessage();
            $this->error("Sync failed: " . $errorMessage);
        }

        $endTime = now();
        $statusData = [
            'status' => $hasErrors ? 'failed' : 'success',
            'started_at' => $startTime->toDateTimeString(),
            'finished_at' => $endTime->toDateTimeString(),
            'duration' => $endTime->diffInSeconds($startTime) . ' seconds',
            'report' => $report,
            'error' => $hasErrors ? $errorMessage : null
        ];

        Cache::put('last_db_sync_status', $statusData);

        // Update database log
        try {
            if (isset($dbLog)) {
                $dbLog->update($statusData);
            } else {
                \App\Models\DatabaseSyncLog::create($statusData);
            }
        } catch (\Throwable $e) {
            $this->error('Failed to update database log: ' . $e->getMessage());
        }

        $this->info('Database sync completed.');
    }

    /**
     * Helper to normalize classroom names.
     */
    private function normalizeClassroomName(?string $name): string
    {
        if (is_null($name)) {
            return '';
        }

        // 1. Remove all spaces and hyphens
        $cleaned = str_replace([' ', '-'], '', $name);
        
        // 2. Extract grade part and letter part.
        // Grade part can be Roman numerals (XII, XI, X, IX, VIII, VII, VI, V, IV, III, II, I) or digits.
        if (preg_match('/^(XII|XI|X|IX|VIII|VII|VI|V|IV|III|II|I|12|11|10|[789])([A-Za-z]+)$/i', $cleaned, $matches)) {
            $grade = strtoupper($matches[1]);
            $letter = strtoupper($matches[2]);
            
            // Map Roman numerals to Arabic numbers
            $romanMap = [
                'XII' => '12',
                'XI' => '11',
                'X' => '10',
                'IX' => '9',
                'VIII' => '8',
                'VII' => '7',
                'VI' => '6',
                'V' => '5',
                'IV' => '4',
                'III' => '3',
                'II' => '2',
                'I' => '1',
            ];
            
            if (isset($romanMap[$grade])) {
                $grade = $romanMap[$grade];
            }
            
            return $grade . $letter;
        }
        
        return strtoupper($cleaned);
    }
}
