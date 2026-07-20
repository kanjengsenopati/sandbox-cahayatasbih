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
            Log::info('Running migration to normalize monthly bills to 12 months and clean ghost/duplicate bills...');
            Artisan::call('bills:cleanup-ghost-duplicates');
            Log::info('Migration monthly bill normalization finished: ' . Artisan::output());
        } catch (\Throwable $e) {
            Log::error('Migration monthly bill normalization failed: ' . $e->getMessage());
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
