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
        Schema::table('karyawans', function (Blueprint $table) {
            $table->decimal('potongan_terlambat', 12, 2)->default(0)->after('hari_kerja');
            $table->decimal('potongan_absen', 12, 2)->default(0)->after('potongan_terlambat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropColumn(['potongan_terlambat', 'potongan_absen']);
        });
    }
};
