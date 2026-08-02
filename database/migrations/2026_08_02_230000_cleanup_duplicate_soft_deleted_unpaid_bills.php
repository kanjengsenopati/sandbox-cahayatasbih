<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Permanently remove legacy soft-deleted UNPAID bills that collide with unique_active_record constraint
        DB::table('bills')
            ->whereNotNull('deleted_at')
            ->where('status', 'UNPAID')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-reversible data cleanup
    }
};
