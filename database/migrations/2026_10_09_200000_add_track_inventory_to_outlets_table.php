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
        if (!Schema::hasColumn('outlets', 'track_inventory')) {
            Schema::table('outlets', function (Blueprint $table) {
                $table->boolean('track_inventory')->default(true)->after('is_active');
            });
        }

        // Set track_inventory = false for Koperasi by default to maintain consistent legacy behavior
        DB::table('outlets')
            ->where('code', 'KPR')
            ->orWhere('name', 'Koperasi')
            ->update(['track_inventory' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('outlets', 'track_inventory')) {
            Schema::table('outlets', function (Blueprint $table) {
                $table->dropColumn('track_inventory');
            });
        }
    }
};
