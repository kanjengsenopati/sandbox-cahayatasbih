<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $mappings = [
            // Biaya Aplikasi
            'APLIKASI CT - MA' => 'Biaya Aplikasi',
            'APLIKASI CT - PONDOK' => 'Biaya Aplikasi',
            'BIAYA APLIKASI CT - MA' => 'Biaya Aplikasi',
            'BIAYA APLIKASI CT - PONDOK' => 'Biaya Aplikasi',
            'BIAYA APLIKASI CT - SMP' => 'Biaya Aplikasi',
            'Biaya Aplikasi' => 'Biaya Aplikasi',

            // LKS Semester 1
            'LKS SMT 1 - MA' => 'LKS Semester 1',
            'LKS SMT 1 - SMP' => 'LKS Semester 1',
            'LKS SMT 1' => 'LKS Semester 1',

            // LKS Semester 2
            'LKS SMT 2 - MA' => 'LKS Semester 2',
            'LKS SMT 2 - SMP' => 'LKS Semester 2',
            'LKS SMT 2' => 'LKS Semester 2',

            // Kalender
            'KALENDER MA 2026' => 'Kalender',
            'KALENDER SMP 2026' => 'Kalender',

            // Zarkasi
            'ZARKASI MA' => 'Zarkasi',
            'ZARKASI' => 'Zarkasi',

            // Syahriah
            'SYAHRIAH MA' => 'Syahriah',
            'SYAHRIAH PONDOK' => 'Syahriah',
            'SYAHRIAH SMP' => 'Syahriah',

            // Biaya Ujian / Akhir Tahun
            'BIAYA KEGIATAN AKHIR TAHUN' => 'Biaya Ujian / Akhir Tahun',
            'BIAYA UJIAN MA - 2025/2026' => 'Biaya Ujian / Akhir Tahun',
            'BIAYA UJIAN SMP AKHIR TAHUN' => 'Biaya Ujian / Akhir Tahun',

            // Infaq Kenaikan Kelas
            'INFAQ KENAIKAN KELAS' => 'Infaq Kenaikan Kelas',
        ];

        foreach ($mappings as $old => $new) {
            DB::table('bill_types')
                ->where('name', $old)
                ->update(['name' => $new]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Destructive migration
    }
};
