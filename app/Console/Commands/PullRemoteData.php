<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PullRemoteData extends Command
{
    protected $signature = 'db:pull-remote
        {--source=both : Sumber data (master, aplikasi, both)}
        {--tables= : Tabel tertentu, dipisah koma}
        {--days=30 : Rentang hari ke belakang}
        {--all : Pull semua data tanpa batas waktu}
        {--dry-run : Simulasi tanpa menulis ke database}';

    protected $description = 'Pull data dari VPS database (mysql_master & mysql_aplikasi) ke SQLite lokal untuk monitoring & development';

    /**
     * Config/reference tables - always fully synced.
     */
    private array $configTables = [
        'schools',
        'outlets',
        'admin_outlets',
        'academic_years',
        'banks',
        'topup_banks',
        'category_items',
        'items',
        'bill_items',
        'cash_flow_categories',
    ];

    /**
     * Transactional tables - synced with date filter.
     */
    private array $transactionalTables = [
        'classrooms',
        'students',
        'student_classroom_histories',
        'saldo_histories',
        'saving_histories',
        'transactions',
        'transaction_details',
        'transaction_proofs',
        'point_of_sale_transactions',
        'point_of_sale_transaction_details',
        'point_of_sale_carts',
        'stock_histories',
        'cash_flows',
    ];

    public function handle()
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '2048M');

        $source = strtolower($this->option('source') ?? 'both');
        $dryRun = $this->option('dry-run');
        $syncAll = $this->option('all');
        $days = (int) ($this->option('days') ?? 30);
        $cutoffDate = now()->subDays($days)->toDateTimeString();
        $syncTablesOption = $this->option('tables');
        $startTime = now();

        $this->info("╔══════════════════════════════════════════════════════╗");
        $this->info("║     PULL REMOTE DATA → SQLite Lokal                 ║");
        $this->info("╚══════════════════════════════════════════════════════╝");
        $this->newLine();

        if ($dryRun) {
            $this->warn('🔍 MODE DRY-RUN — Tidak ada data yang akan ditulis.');
        }

        $this->info("Source    : {$source}");
        $this->info("Rentang   : " . ($syncAll ? 'SEMUA DATA' : "≤ {$days} hari terakhir"));
        $this->info("Target DB : sqlite (local_replica.sqlite)");
        $this->newLine();

        // Determine which connections to pull from
        $connections = $this->resolveConnections($source);
        if (empty($connections)) {
            $this->error("Source '{$source}' tidak dikenali. Gunakan: master, aplikasi, atau both.");
            return 1;
        }

        $allTables = array_merge($this->configTables, $this->transactionalTables);

        // Filter tables if specified
        if ($syncTablesOption) {
            $selectedTables = array_map('trim', explode(',', $syncTablesOption));
            $allTables = array_filter($allTables, fn($t) => in_array($t, $selectedTables));
            if (empty($allTables)) {
                $this->error("Tidak ada tabel valid yang cocok dengan filter: {$syncTablesOption}");
                return 1;
            }
        }

        $localConn = DB::connection(); // sqlite
        $grandTotal = 0;
        $report = [];

        // Optimize local connection if it's sqlite
        try {
            if ($localConn->getDriverName() === 'sqlite') {
                $localConn->statement('PRAGMA journal_mode=WAL;');
                $localConn->statement('PRAGMA synchronous=NORMAL;');
                $localConn->statement('PRAGMA foreign_keys=OFF;');
            } else if ($localConn->getDriverName() === 'mysql') {
                $localConn->statement('SET FOREIGN_KEY_CHECKS=0;');
            }
        } catch (\Throwable $e) {
            $this->warn("Optimization setting skipped: " . $e->getMessage());
        }

        foreach ($connections as $connName => $connLabel) {
            $this->newLine();
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("📡 Pulling from: {$connLabel} ({$connName})");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

            // Test connection
            try {
                DB::connection($connName)->getPdo();
                $this->info("✅ Koneksi berhasil.");
            } catch (\Throwable $e) {
                $this->error("❌ Gagal terhubung ke {$connName}: " . $e->getMessage());
                $report[$connName] = ['status' => 'connection_failed', 'count' => 0, 'message' => $e->getMessage()];
                continue;
            }

            foreach ($allTables as $table) {
                $result = $this->pullTable($connName, $table, $cutoffDate, $syncAll, $dryRun);
                $report["{$connName}.{$table}"] = $result;
                $grandTotal += $result['count'] ?? 0;
            }
        }

        // Re-enable FK
        try {
            if ($localConn->getDriverName() === 'sqlite') {
                $localConn->statement('PRAGMA foreign_keys=ON;');
            } else if ($localConn->getDriverName() === 'mysql') {
                $localConn->statement('SET FOREIGN_KEY_CHECKS=1;');
            }
        } catch (\Throwable $e) {}

        // Summary
        $this->newLine();
        $this->info("╔══════════════════════════════════════════════════════╗");
        $this->info("║                    RINGKASAN PULL                   ║");
        $this->info("╚══════════════════════════════════════════════════════╝");

        $headers = ['Source.Table', 'Status', 'Records', 'Info'];
        $rows = [];
        foreach ($report as $key => $val) {
            $rows[] = [
                $key,
                $val['status'] ?? '-',
                $val['count'] ?? 0,
                $val['message'] ?? '-',
            ];
        }
        $this->table($headers, $rows);

        $elapsed = $startTime->diffInSeconds(now());
        $this->newLine();
        $this->info("🏁 Total records pulled: {$grandTotal}");
        $this->info("⏱️  Durasi: {$elapsed} detik");
        if ($dryRun) {
            $this->warn("⚠️  Ini hanya simulasi. Jalankan tanpa --dry-run untuk menulis data.");
        }

        return 0;
    }

    /**
     * Resolve connection names based on source option.
     */
    private function resolveConnections(string $source): array
    {
        return match ($source) {
            'master' => ['mysql_master' => 'VPS Master (cahayatasbihdb)'],
            'aplikasi' => ['mysql_aplikasi' => 'VPS Aplikasi (aplikasidb)'],
            'both' => [
                'mysql_master' => 'VPS Master (cahayatasbihdb)',
                'mysql_aplikasi' => 'VPS Aplikasi (aplikasidb)',
            ],
            default => [],
        };
    }

    /**
     * Pull a single table from remote to local SQLite.
     */
    private function pullTable(string $connName, string $table, string $cutoffDate, bool $syncAll, bool $dryRun): array
    {
        $localConn = DB::connection(); // sqlite

        // Check table exists on remote
        try {
            $remoteHasTable = Schema::connection($connName)->hasTable($table);
        } catch (\Throwable $e) {
            return ['status' => 'error', 'count' => 0, 'message' => 'Remote check failed: ' . $e->getMessage()];
        }

        if (!$remoteHasTable) {
            $this->line("  ⏭️  {$table}: tidak ada di remote");
            return ['status' => 'skipped', 'count' => 0, 'message' => 'Not found in remote'];
        }

        // Check local table exists
        try {
            if (!Schema::connection(config('database.default'))->hasTable($table)) {
                $this->line("  ⏭️  {$table}: tidak ada di lokal");
                return ['status' => 'skipped', 'count' => 0, 'message' => 'Not found in local database'];
            }
        } catch (\Throwable $e) {
            return ['status' => 'error', 'count' => 0, 'message' => 'Local check failed: ' . $e->getMessage()];
        }

        // Get common columns
        try {
            $remoteColumns = Schema::connection($connName)->getColumnListing($table);
            $localColumns = Schema::connection(config('database.default'))->getColumnListing($table);
            $commonColumns = array_values(array_intersect($localColumns, $remoteColumns));
        } catch (\Throwable $e) {
            return ['status' => 'error', 'count' => 0, 'message' => 'Column listing failed: ' . $e->getMessage()];
        }

        if (empty($commonColumns) || !in_array('id', $commonColumns)) {
            $this->line("  ⏭️  {$table}: no common columns or missing id");
            return ['status' => 'skipped', 'count' => 0, 'message' => 'No common columns or missing id'];
        }

        // Build query
        $query = DB::connection($connName)->table($table)->select($commonColumns);

        // Apply date filter for transactional tables
        $isConfig = in_array($table, $this->configTables);
        $hasCreatedAt = in_array('created_at', $commonColumns);
        $hasUpdatedAt = in_array('updated_at', $commonColumns);

        if (!$isConfig && !$syncAll && ($hasCreatedAt || $hasUpdatedAt)) {
            $query->where(function ($q) use ($cutoffDate, $hasCreatedAt, $hasUpdatedAt) {
                if ($hasCreatedAt) {
                    $q->orWhere('created_at', '>=', $cutoffDate);
                }
                if ($hasUpdatedAt) {
                    $q->orWhere('updated_at', '>=', $cutoffDate);
                }
            });
        }

        // Count
        try {
            $totalCount = $query->count();
        } catch (\Throwable $e) {
            return ['status' => 'error', 'count' => 0, 'message' => 'Count failed: ' . $e->getMessage()];
        }

        if ($totalCount === 0) {
            $this->line("  ⏭️  {$table}: 0 records");
            return ['status' => 'empty', 'count' => 0, 'message' => 'No records'];
        }

        $this->line("  📥 {$table}: {$totalCount} records...");

        if ($dryRun) {
            return ['status' => 'dry-run', 'count' => $totalCount, 'message' => "Would pull {$totalCount} records"];
        }

        // Pull in chunks with UPSERT
        $pulled = 0;
        $chunkSize = 500;
        $columnsToUpdate = array_values(array_filter($commonColumns, fn($c) => $c !== 'id'));

        try {
            $query->orderBy('id')->chunk($chunkSize, function ($rows) use ($localConn, $table, $commonColumns, $columnsToUpdate, &$pulled) {
                $data = $rows->map(fn($row) => (array) $row)->toArray();
                if (empty($data)) return;

                // Batch insert using Laravel Native Upsert
                foreach (array_chunk($data, 100) as $batch) {
                    DB::connection(config('database.default'))->table($table)->upsert($batch, ['id'], $columnsToUpdate);
                }

                $pulled += count($data);
            });
        } catch (\Throwable $e) {
            return ['status' => 'partial', 'count' => $pulled, 'message' => "Error after {$pulled}: " . $e->getMessage()];
        }

        $this->line("  ✅ {$table}: {$pulled} records pulled");
        return ['status' => 'ok', 'count' => $pulled, 'message' => "Pulled {$pulled} records"];
    }

}
