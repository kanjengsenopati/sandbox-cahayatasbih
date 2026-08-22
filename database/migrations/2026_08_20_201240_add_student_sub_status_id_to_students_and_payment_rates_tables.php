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
        Schema::table('students', function (Blueprint $table) {
            $table->uuid('student_sub_status_id')->nullable()->after('status');
        });

        Schema::table('payment_rates', function (Blueprint $table) {
            $table->uuid('student_sub_status_id')->nullable()->after('jamaah_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('student_sub_status_id');
        });

        Schema::table('payment_rates', function (Blueprint $table) {
            $table->dropColumn('student_sub_status_id');
        });
    }
};
