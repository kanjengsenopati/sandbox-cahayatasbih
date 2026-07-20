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
            Log::info('Running migration to clean up ghost and duplicate bills...');
            Artisan::call('bills:cleanup-ghost-duplicates');
            Log::info('Migration ghost & duplicate bills cleanup finished: ' . Artisan::output());
        } catch (\Throwable $e) {
            Log::error('Migration ghost & duplicate bills cleanup failed: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op for cleanup migration
    }
};
