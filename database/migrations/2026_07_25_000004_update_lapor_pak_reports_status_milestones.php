<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('lapor_pak_reports')) {
            DB::statement("ALTER TABLE lapor_pak_reports MODIFY status VARCHAR(50) NOT NULL DEFAULT 'Laporan Masuk'");

            // Update data legacy jika ada
            DB::table('lapor_pak_reports')
                ->where('status', 'Kendala')
                ->update(['status' => 'Laporan Masuk']);

            DB::table('lapor_pak_reports')
                ->where('status', 'Teratasi')
                ->update(['status' => 'Selesai']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('lapor_pak_reports')) {
            DB::table('lapor_pak_reports')
                ->whereIn('status', ['Laporan Masuk', 'Diterima', 'Sedang Ditangani'])
                ->update(['status' => 'Kendala']);

            DB::table('lapor_pak_reports')
                ->where('status', 'Selesai')
                ->update(['status' => 'Teratasi']);

            DB::statement("ALTER TABLE lapor_pak_reports MODIFY status ENUM('Kendala', 'Teratasi') NOT NULL DEFAULT 'Kendala'");
        }
    }
};
