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
        Schema::create('student_achievements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('academic_year_id');
            $table->uuid('classroom_id');
            $table->uuid('school_id');
            $table->enum('semester', ['GANJIL', 'GENAP']);
            $table->string('title');
            $table->string('champion');
            $table->string('level');
            $table->string('reward');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_achievements');
    }
};
