<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Bersihkan pada koneksi default
        $this->cleanupDummyHandovers(null);

        // 2. Bersihkan juga pada koneksi mysql_aplikasi jika tersedia
        try {
            if (config('database.connections.mysql_aplikasi')) {
                $this->cleanupDummyHandovers('mysql_aplikasi');
            }
        } catch (\Throwable $e) {
            // Abaikan jika koneksi remote tidak aktif saat migrasi offline
        }
    }

    /**
     * Helper pembersihan record dummy 'Test' dan 'oke'
     */
    protected function cleanupDummyHandovers(?string $connection = null): void
    {
        $db = $connection ? DB::connection($connection) : DB::connection();

        if (!Schema::connection($connection)->hasTable('outlet_handovers')) {
            return;
        }

        // Cari ID handover dummy dengan keterangan 'Test', 'oke', atau nominal dummy 10 Okt 2026
        $dummyHandoverIds = $db->table('outlet_handovers')
            ->where(function ($q) {
                $q->whereIn(DB::raw('LOWER(TRIM(notes))'), ['test', 'oke'])
                  ->orWhere(function ($sub) {
                      $sub->whereDate('handover_date', '2026-10-10')
                          ->whereIn('amount', [546000, 1319000, 1865000]);
                  });
            })
            ->pluck('id')
            ->toArray();

        if (!empty($dummyHandoverIds)) {
            // Hapus pencatatan arus kas terkait
            if (Schema::connection($connection)->hasTable('cash_flows')) {
                foreach ($dummyHandoverIds as $id) {
                    $db->table('cash_flows')
                        ->where('description', 'like', "%[Handover ID: {$id}]%")
                        ->delete();
                }
            }

            // Hapus record handover dummy
            $db->table('outlet_handovers')->whereIn('id', $dummyHandoverIds)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data dummy tidak perlu direstore saat rollback
    }
};
