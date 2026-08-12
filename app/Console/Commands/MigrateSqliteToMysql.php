<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class MigrateSqliteToMysql extends Command
{
    protected $signature = 'db:migrate-sqlite-mysql';

    protected $description = 'Migrate data from local SQLite to local MySQL';

    public function handle()
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '2048M');

        $this->info("========================================");
        $this->info("    MIGRASI LOKAL: SQLite -> MySQL      ");
        $this->info("========================================");

        // Define SQLite connection dynamically
        $sqlitePath = database_path('local_replica.sqlite');
        if (!file_exists($sqlitePath)) {
            $this->error("File SQLite tidak ditemukan di: {$sqlitePath}");
            return 1;
        }

        Config::set('database.connections.sqlite_old', [
            'driver' => 'sqlite',
            'database' => $sqlitePath,
            'foreign_key_constraints' => false,
        ]);

        $sqlite = DB::connection('sqlite_old');
        $mysql = DB::connection('mysql'); // default

        // Verify MySQL connection
        try {
            $mysql->getPdo();
            $this->info("Koneksi MySQL '{$mysql->getDatabaseName()}' berhasil.");
        } catch (\Exception $e) {
            $this->error("Gagal konek ke MySQL: " . $e->getMessage());
            return 1;
        }

        // Disable FK checks on MySQL
        $mysql->statement('SET FOREIGN_KEY_CHECKS=0;');

        // Get all tables from SQLite (excluding internal tables)
        $tables = $sqlite->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' AND name != 'migrations'");
        $tables = array_map(fn($t) => $t->name, $tables);

        $totalRecords = 0;

        foreach ($tables as $table) {
            $this->info("Memproses tabel: {$table}...");

            // Check if table exists in MySQL
            if (!Schema::connection('mysql')->hasTable($table)) {
                $this->warn("  ⏭️  Tabel {$table} tidak ada di MySQL. Di-skip.");
                continue;
            }

            // Get columns matching both
            $sqliteColumns = Schema::connection('sqlite_old')->getColumnListing($table);
            $mysqlColumns = Schema::connection('mysql')->getColumnListing($table);
            $commonColumns = array_values(array_intersect($sqliteColumns, $mysqlColumns));

            if (empty($commonColumns)) {
                $this->warn("  ⏭️  Tabel {$table} tidak memiliki kolom yang sama. Di-skip.");
                continue;
            }

            // Clear MySQL table before insert (to prevent conflicts, as we want exact copy)
            $mysql->table($table)->truncate();

            // Read from SQLite and insert to MySQL using cursor for fast O(1) performance
            $count = 0;
            $batch = [];
            
            $mysql->beginTransaction();
            try {
                foreach ($sqlite->table($table)->cursor() as $row) {
                    $batch[] = (array)$row;
                    if (count($batch) >= 2000) {
                        $mysql->table($table)->insert($batch);
                        $count += count($batch);
                        $batch = [];
                    }
                }
                if (!empty($batch)) {
                    $mysql->table($table)->insert($batch);
                    $count += count($batch);
                }
                $mysql->commit();
            } catch (\Exception $e) {
                $mysql->rollBack();
                $this->error("Gagal memindahkan tabel {$table}: " . $e->getMessage());
                continue;
            }

            $this->info("  ✅ Selesai: {$count} baris dipindahkan.");
            $totalRecords += $count;
        }

        // Re-enable FK checks on MySQL
        $mysql->statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->newLine();
        $this->info("🎉 Migrasi selesai! Total baris dipindahkan: {$totalRecords}");

        return 0;
    }
}
