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
            $table->index(['student_id', 'deleted_at'], 'idx_bills_student_deleted');
            $table->index(['academic_year_id', 'deleted_at'], 'idx_bills_ay_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropIndex('idx_bills_student_deleted');
            $table->dropIndex('idx_bills_ay_deleted');
        });
    }
};
