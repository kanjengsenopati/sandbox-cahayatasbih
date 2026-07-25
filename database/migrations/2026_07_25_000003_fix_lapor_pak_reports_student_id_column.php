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
        if (Schema::hasTable('lapor_pak_reports')) {
            DB::statement("ALTER TABLE lapor_pak_reports MODIFY student_id VARCHAR(255) NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('lapor_pak_reports')) {
            DB::statement("ALTER TABLE lapor_pak_reports MODIFY student_id BIGINT UNSIGNED NULL");
        }
    }
};
