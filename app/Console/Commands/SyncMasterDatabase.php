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
    protected $signature = 'db:sync-master {--all : Sync all records instead of only last 30 days}';

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

        $tables = [
            'schools',
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
            'transactions',
            'transaction_details',
            'saldo_histories',
            'saving_histories'
        ];

        $report = [];
        $hasErrors = false;
        $errorMessage = '';

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

        // Build classroom ID mapping to map old formats (e.g. 10-D, X-D) to local standardized names
        $classroomMapping = [];
        try {
            $masterClassrooms = DB::connection('mysql_master')->table('classrooms')->get();
            $localClassrooms = DB::connection('mysql')->table('classrooms')->get();
            
            // Group local classrooms by school_id and normalized name
            $localGroups = [];
            foreach ($localClassrooms as $lc) {
                $norm = $this->normalizeClassroomName($lc->name);
                $localGroups[$lc->school_id][$norm] = $lc->id;
            }
            
            foreach ($masterClassrooms as $mc) {
                $norm = $this->normalizeClassroomName($mc->name);
                
                // Check if we already mapped or have a local classroom for this school_id & normalized name
                if (isset($localGroups[$mc->school_id][$norm])) {
                    $localId = $localGroups[$mc->school_id][$norm];
                } else {
                    // Create new local classroom
                    $localId = (string) \Illuminate\Support\Str::uuid();
                    DB::connection('mysql')->table('classrooms')->insert([
                        'id' => $localId,
                        'school_id' => $mc->school_id,
                        'name' => $norm,
                        'created_at' => $mc->created_at ?? now(),
                        'updated_at' => $mc->updated_at ?? now(),
                        'deleted_at' => $mc->deleted_at ?? null,
                    ]);
                    // Update local groups cache
                    $localGroups[$mc->school_id][$norm] = $localId;
                }
                
                $classroomMapping[$mc->id] = $localId;
            }
            $this->info("Built classroom mapping for " . count($classroomMapping) . " classrooms.");
        } catch (\Throwable $e) {
            $this->warn("Could not build classroom mapping: " . $e->getMessage());
        }

        try {
            DB::connection('mysql')->beginTransaction();
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($tables as $table) {
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
                    'academic_years',
                    'bill_items',
                    'bill_types',
                    'banks',
                    'bill_type_banks',
                    'topup_banks',
                    'payment_rates',
                    'payment_rate_classrooms',
                    'payment_rate_students',
                    'payment_rate_items'
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

                $processChunk = function ($rows) use ($table, $commonColumns, &$inserted, &$minTimestamp, &$maxTimestamp, $classroomMapping) {
                    $data = $rows->map(fn($row) => (array) $row)->toArray();
                    if (!empty($data)) {
                        $columnsToUpdate = array_filter($commonColumns, fn($col) => $col !== 'id');
                        
                        // Identify date range and map classroom IDs
                        foreach ($data as &$row) {
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
                                $localStudent = DB::connection('mysql')->table('students')->where('id', $row['id'])->first();
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
                                $localStudent = DB::connection('mysql')->table('students')->where('id', $row['id'])->first();
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
