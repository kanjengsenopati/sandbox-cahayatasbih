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
            $table->index('bill_type_id');
            $table->index('student_id');
            $table->index('classroom_id');
            $table->index('academic_year_id');
            $table->index('month');
            $table->index('year');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropIndex(['bill_type_id']);
            $table->dropIndex(['student_id']);
            $table->dropIndex(['classroom_id']);
            $table->dropIndex(['academic_year_id']);
            $table->dropIndex(['month']);
            $table->dropIndex(['year']);
            $table->dropIndex(['status']);
        });
    }
};
