<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncAplikasidbDelta extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:aplikasidb-delta';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull delta data from aplikasidb (VPS) to localhost based on updated_at/created_at for specific tables';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=========================================================");
        $this->info("MULAI SINKRONISASI PULL DATA DARI APLIKASIDB (VPS)");
        $this->info("=========================================================");

        $tablesToSync = [
            'bills', 
            'bill_items', 
            'bill_types',
            'transactions', 
            'transaction_details', 
            'transaction_proofs',
            'transaction_reports',
            'saldo_histories', 
            'students',
            'point_of_sale_transactions', 
            'point_of_sale_items'
        ];

        $localDb = DB::connection('sqlite');
        $remoteDb = DB::connection('mysql_aplikasi');

        foreach ($tablesToSync as $table) {
            $this->info("\nMemproses tabel: {$table} ...");

            if (!Schema::connection('sqlite')->hasTable($table)) {
                $this->warn("Tabel {$table} tidak ada di database lokal. Dilewati.");
                continue;
            }

            if (!Schema::connection('mysql_aplikasi')->hasTable($table)) {
                $this->warn("Tabel {$table} tidak ada di database VPS (aplikasidb). Dilewati.");
                continue;
            }

            // 1. Dapatkan max date dari lokal menggunakan Cache state (Lebih kebal dari anomali Soft-Delete lokal)
            $localLatest = null;
            $dateColumn = null;
            $cacheKey = 'sync_delta_last_time_' . $table;

            if (Schema::connection('sqlite')->hasColumn($table, 'updated_at')) {
                $dateColumn = 'updated_at';
                // Gunakan cache, jika tidak ada, fallback ke record yang tidak pernah diedit lokal (created_at = updated_at) 
                // atau nilai terlama agar tidak terlewat.
                $localLatest = \Illuminate\Support\Facades\Cache::get($cacheKey, function() use ($localDb, $table) {
                    return clone $localDb->table($table)->whereColumn('created_at', 'updated_at')->max('updated_at') 
                           ?? clone $localDb->table($table)->max('created_at');
                });
            } elseif (Schema::connection('sqlite')->hasColumn($table, 'created_at')) {
                $dateColumn = 'created_at';
                $localLatest = \Illuminate\Support\Facades\Cache::get($cacheKey, function() use ($localDb, $table) {
                    return clone $localDb->table($table)->max('created_at');
                });
            }

            // 2. Query dari remote berdasarkan delta
            $query = clone $remoteDb->table($table);
            
            if ($localLatest && $dateColumn) {
                $this->info("   Last sync date di lokal: {$localLatest} (kolom: {$dateColumn})");
                $query->where($dateColumn, '>', $localLatest);
                
                try {
                    $totalToPull = clone $query;
                    $count = $totalToPull->count();
                    $this->info("   Menemukan {$count} record baru/update.");
                } catch (\Exception $e) {
                    $this->error("   Gagal menghitung jumlah record baru: " . $e->getMessage());
                    continue;
                }
            } else {
                $this->info("   Tabel kosong atau tidak punya kolom timestamp. Akan ditarik keseluruhan (hati-hati).");
                try {
                    $totalToPull = clone $query;
                    $count = $totalToPull->count();
                    $this->info("   Menemukan total {$count} record.");
                } catch (\Exception $e) {
                    $this->error("   Gagal menghitung jumlah record: " . $e->getMessage());
                    continue;
                }
            }

            if ($count === 0) {
                $this->info("   Tabel up-to-date. Tidak ada yang perlu ditarik.");
                continue;
            }

            // 3. Tarik data secara bertahap (chunking) dan upsert ke SQLite
            $this->info("   Memulai proses pull data (chunk 500)...");
            $bar = $this->output->createProgressBar($count);
            $bar->start();

            try {
                $chunkSize = 500;
                $offset = 0;
                while ($offset < $count) {
                    $records = clone $query;
                    if ($dateColumn) {
                        $records->orderBy($dateColumn, 'asc');
                    }
                    $items = $records->offset($offset)->limit($chunkSize)->get()->map(function($item) {
                        return (array) $item;
                    })->toArray();
                    
                    if (empty($items)) {
                        break;
                    }

                    // SQLite batasi jumlah variable per query, upsert dalam batch lebih kecil (50)
                    $upsertChunks = array_chunk($items, 50);
                    $uniqueBy = ['id'];
                    
                    foreach ($upsertChunks as $batch) {
                        $localDb->table($table)->upsert($batch, $uniqueBy);
                    }
                    
                    $bar->advance(count($items));
                    $offset += $chunkSize;
                }
                
                $bar->finish();
                $this->info("\n   Berhasil sinkronisasi tabel {$table}.");
                if (isset($cacheKey)) {
                    \Illuminate\Support\Facades\Cache::put($cacheKey, \Carbon\Carbon::now(), 60 * 24 * 30); // 30 hari
                }

            } catch (\Exception $e) {
                $bar->finish();
                $this->error("\n   Terjadi error saat sinkronisasi {$table}: " . $e->getMessage());
            }
        }

        $this->info("\n=========================================================");
        $this->info("SINKRONISASI SELESAI");
        $this->info("=========================================================");
    }
}
