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
        Schema::create('ppdb_tracks', function (Blueprint $table) {
           $table->uuid('id')->primary();
            
            // Relasi ke Gelombang & Sekolah
            $table->uuid('ppdb_wave_id');
            $table->uuid('school_id');
            
            // Tipe Pendaftaran (Kunci Logika Biaya)
            $table->enum('registration_type', ['UMUM', 'JAMAAH', 'ALUMNI']);
            
            // Konfigurasi
            $table->bigInteger('registration_fee')->default(0); // Biaya Pendaftaran
            $table->integer('quota')->default(0); // Kuota Maksimal
            $table->boolean('is_open')->default(true); // Switch Buka/Tutup Manual
            $table->string('close_reason')->nullable(); // Pesan jika ditutup
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppdb_tracks');
    }
};
