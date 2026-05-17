<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Officer;
use App\Models\ApplicationMenu;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class OfficerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Clear Spatie Permission Cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Seed Spatie Permissions for Petugas & Perizinan
        $permissions = [
            'Manage Petugas',
            'Create Petugas',
            'Edit Petugas',
            'Delete Petugas',
            'Manage Perizinan',
            'Approve Perizinan',
            'Scan Perizinan',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'web',
            ]);
        }

        // 3. Assign permissions to all existing Roles
        $roles = Role::all();
        foreach ($roles as $role) {
            $role->givePermissionTo($permissions);
        }

        // 4. Seed default officers
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

        // 5. Seed ApplicationMenu
        ApplicationMenu::updateOrCreate(
            ['flag' => 'petugas'],
            [
                'name' => 'Petugas',
                'status' => true,
            ]
        );

        ApplicationMenu::updateOrCreate(
            ['flag' => 'perizinan'],
            [
                'name' => 'Perizinan',
                'status' => true,
            ]
        );
    }
}
