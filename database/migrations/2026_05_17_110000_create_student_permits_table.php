<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function run(): void
    {
        Schema::create('student_permits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Wali Santri pemohon
            $table->uuid('admin_id')->nullable(); // Ustadz/Ustadzah penanggung jawab asrama penyetuju izin
            
            $table->enum('permit_type', ['keluar_pondok', 'pulang_sementara', 'sakit']);
            $table->text('reason');
            $table->dateTime('planned_exit_date');
            $table->dateTime('planned_return_date');
            
            // --- DATA VERIFIKASI KEBERANGKATAN (KELUAR) ---
            $table->dateTime('actual_exit_date')->nullable();
            $table->string('exit_photo_santri')->nullable();   // Foto santri saat keluar
            $table->string('exit_photo_escort')->nullable();   // Foto penjemput saat keluar
            $table->string('exit_escort_name')->nullable();    // Nama penjemput
            $table->string('exit_escort_relation')->nullable(); // Hubungan penjemput (Ayah, Ibu, Paman, dll)
            $table->string('exit_latitude')->nullable();        // GPS Latitude keluar
            $table->string('exit_longitude')->nullable();       // GPS Longitude keluar
            
            // --- DATA VERIFIKASI KEPULANGAN (KEMBALI) ---
            $table->dateTime('actual_return_date')->nullable();
            $table->string('return_photo_santri')->nullable();  // Foto santri saat kembali
            $table->string('return_photo_escort')->nullable();  // Foto pengantar saat kembali
            $table->string('return_latitude')->nullable();       // GPS Latitude kembali
            $table->string('return_longitude')->nullable();      // GPS Longitude kembali
            
            $table->enum('status', ['pending', 'approved', 'rejected', 'out', 'returned', 'expired'])->default('pending');
            $table->string('rejection_reason')->nullable();
            $table->string('barcode_token')->unique()->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_permits');
    }
};
