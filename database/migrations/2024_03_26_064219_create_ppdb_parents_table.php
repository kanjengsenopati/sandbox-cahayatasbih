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
        Schema::create('ppdb_parents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('family_card_number');
            $table->string('father_name');
            $table->string('father_nik');
            $table->string('father_status');
            $table->string('father_education');
            $table->string('father_job');
            $table->string('mother_name');
            $table->string('mother_nik');
            $table->string('mother_status');
            $table->string('mother_education');
            $table->string('mother_job');
            $table->string('government_aid_card_type')->nullable();
            $table->boolean('is_member')->default(false);
            $table->string('mdti_branch')->nullable();
            $table->string('member_number')->nullable();
            $table->string('photo_card')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppdb_parents');
    }
};
