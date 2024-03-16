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
        Schema::create('study_grades', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('study_id');
            $table->uuid('student_id');
            $table->uuid('academic_year_id');
            $table->uuid('semester_id');
            $table->integer('grade');
            $table->string('letter_grade');
            $table->integer('kkm');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_grades');
    }
};
