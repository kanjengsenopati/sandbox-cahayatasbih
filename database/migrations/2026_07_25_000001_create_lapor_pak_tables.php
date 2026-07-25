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
        Schema::create('lapor_pak_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('student_name');
            $table->string('school');
            $table->string('class_name');
            $table->string('parent_name');
            $table->string('parent_phone');
            $table->boolean('is_parent_updated')->default(false);
            $table->string('kendala');
            $table->text('keterangan')->nullable();
            $table->enum('status', ['Kendala', 'Teratasi'])->default('Kendala');
            $table->timestamps();
        });

        Schema::create('lapor_pak_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->dateTime('start_datetime')->nullable();
            $table->dateTime('end_datetime')->nullable();
            $table->text('closed_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lapor_pak_reports');
        Schema::dropIfExists('lapor_pak_settings');
    }
};
