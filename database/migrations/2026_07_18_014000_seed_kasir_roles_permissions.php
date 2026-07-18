<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define permissions list
        $koperasiPermissions = [
            'Manage Pos Kasir',
            'Create Pos Kasir',
            'Manage Laporan Pos Kasir',
            'Manage Laporan Pos Multi Outlet',
            'Manage Laporan Rugi Laba',
            'Create Laporan Pos Multi Outlet',
            'Edit Laporan Pos Multi Outlet',
            'Delete Laporan Pos Multi Outlet',
            'Create Laporan Rugi Laba',
            'Edit Laporan Rugi Laba',
            'Delete Laporan Rugi Laba',
            'Manage Barang',
            'Create Barang',
            'Edit Barang',
            'Delete Barang',
        ];

        $outletKepegawaianPermissions = [
            'Manage Karyawan',
            'Manage Shift',
            'Manage Laporan Presensi',
            'Manage Biometric',
            'Create Karyawan',
            'Edit Karyawan',
            'Delete Karyawan',
            'Create Shift',
            'Edit Shift',
            'Delete Shift',
            'Create Laporan Presensi',
            'Edit Laporan Presensi',
            'Delete Laporan Presensi',
            'Create Biometric',
            'Edit Biometric',
            'Delete Biometric',
        ];

        // Ensure all permissions exist in database
        $allPermissions = array_merge($koperasiPermissions, $outletKepegawaianPermissions);
        foreach ($allPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 2. Setup Role: Kasir Koperasi
        $kasirKoperasi = Role::firstOrCreate(['name' => 'Kasir Koperasi', 'guard_name' => 'web']);
        $kasirKoperasi->syncPermissions($koperasiPermissions);

        // 3. Setup Role: Kasir Karyawan Outlet
        $kasirOutlet = Role::firstOrCreate(['name' => 'Kasir Karyawan Outlet', 'guard_name' => 'web']);
        $kasirOutlet->syncPermissions($allPermissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove roles
        Role::whereIn('name', ['Kasir Koperasi', 'Kasir Karyawan Outlet'])->delete();
    }
};
