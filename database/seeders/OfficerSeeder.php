<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Officer;
use App\Models\ApplicationMenu;

class OfficerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed default officers
        $officers = [
            [
                'name' => 'Ustadz H. Ahmad Fauzi, M.Pd.',
                'position' => 'Kepala Pengasuhan Santri',
                'duty' => 'Konsultasi ketertiban, kedisiplinan, izin kepulangan santri, dan administrasi asrama.',
                'phone' => '6281234567890',
                'photo' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Ustadzah Siti Aminah, S.Ag.',
                'position' => 'Kepala Kurikulum & Tahfidz',
                'duty' => 'Konsultasi perkembangan hafalan quran, setoran tajwid, ujian tahfidz, dan penilaian rapor santri.',
                'phone' => '6289876543210',
                'photo' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Ustadz M. Ridwan, S.E.',
                'position' => 'Bendahara Pesantren',
                'duty' => 'Konsultasi administrasi keuangan, SPP bulanan, pencairan tabungan santri, dan top up kartu jajan.',
                'phone' => '6281122334455',
                'photo' => null,
                'is_active' => true,
            ],
        ];

        foreach ($officers as $officer) {
            Officer::updateOrCreate(
                ['name' => $officer['name']],
                $officer
            );
        }

        // 2. Seed ApplicationMenu
        ApplicationMenu::updateOrCreate(
            ['flag' => 'petugas'],
            [
                'name' => 'Petugas',
                'status' => true,
            ]
        );
    }
}
