<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_monthly_shifts', function (Blueprint $table) {
            $table->dropUnique('emp_monthly_date_unique');
            $table->unique(['presensiable_type', 'presensiable_id', 'date', 'working_shift_id'], 'emp_monthly_date_shift_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_monthly_shifts', function (Blueprint $table) {
            $table->dropUnique('emp_monthly_date_shift_unique');
            $table->unique(['presensiable_type', 'presensiable_id', 'date'], 'emp_monthly_date_unique');
        });
    }
};
