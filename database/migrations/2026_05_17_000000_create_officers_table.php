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
        Schema::create('officers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('position'); // Jabatan
            $table->string('duty');     // Tugas / Deskripsi
            $table->string('phone');    // No WA (e.g. 628xxx)
            $table->string('photo')->nullable(); // Foto opsional
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('officers');
    }
};
