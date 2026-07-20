<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Log::info('Running migration to fix Zarkasi annual bills (550k) and clean ghost bills...');
            Artisan::call('bills:cleanup-ghost-duplicates');
            Log::info('Migration Zarkasi bill fix finished: ' . Artisan::output());
        } catch (\Throwable $e) {
            Log::error('Migration Zarkasi bill fix failed: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op for cleanup/normalization migration
    }
};
