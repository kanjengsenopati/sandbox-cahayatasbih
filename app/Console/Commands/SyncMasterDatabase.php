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
    protected $signature = 'db:sync-master';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Incremental sync of the last 30 days of transactions from cahayatasbihdb (master) to aplikasidb';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database synchronization from master...');
        $startTime = now();
        $oneMonthAgo = now()->subDays(30)->toDateTimeString();

        $tables = [
            'point_of_sale_carts',
            'point_of_sale_transactions',
            'transactions',
            'bills',
            'point_of_sale_transaction_details',
            'transaction_details',
            'saldo_histories'
        ];

        $report = [];
        $hasErrors = false;
        $errorMessage = '';

        // Mark sync as running
        Cache::put('last_db_sync_status', [
            'status' => 'running',
            'started_at' => $startTime->toDateTimeString(),
            'finished_at' => null,
            'report' => [],
            'error' => null
        ], 1800); // 30 minutes expire

        try {
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

                if ($hasCreatedAt || $hasUpdatedAt) {
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

                $processChunk = function ($rows) use ($table, $commonColumns, &$inserted) {
                    $data = $rows->map(fn($row) => (array) $row)->toArray();
                    if (!empty($data)) {
                        $columnsToUpdate = array_filter($commonColumns, fn($col) => $col !== 'id');
                        
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
                    'message' => "Successfully synced {$inserted} rows"
                ];
            }
        } catch (\Throwable $e) {
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
        $this->info('Database sync completed.');
    }
}
