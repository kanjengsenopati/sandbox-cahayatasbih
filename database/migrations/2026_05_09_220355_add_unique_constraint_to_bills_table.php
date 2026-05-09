<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            // Virtual column to resolve MySQL's handling of multiple NULLs in Unique Indexes
            // This ensures soft-deleted rows (deleted_at != NULL) have NULL here, so they can be duplicated.
            // Active rows will have '1', enforcing absolute uniqueness for active records.
            $table->string('active_status')->virtualAs('IF(deleted_at IS NULL, "1", NULL)')->nullable();
            
            // Composite Unique Index
            $table->unique(
                ['student_id', 'bill_type_id', 'academic_year_id', 'month', 'year', 'active_status'],
                'bills_unique_active_record'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropUnique('bills_unique_active_record');
            $table->dropColumn('active_status');
        });
    }
};
