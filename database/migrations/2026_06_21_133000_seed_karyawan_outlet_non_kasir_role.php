<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat permission 'Manage Karyawan' jika belum ada
        $manageKaryawan = Permission::firstOrCreate([
            'name' => 'Manage Karyawan',
            'guard_name' => 'web'
        ]);

        // Buat role 'Karyawan Outlet ( Non Kasir )'
        Role::firstOrCreate([
            'name' => 'Karyawan Outlet ( Non Kasir )',
            'guard_name' => 'web'
        ]);

        // Buat role 'Kasir' jika belum ada
        $kasirRole = Role::firstOrCreate([
            'name' => 'Kasir',
            'guard_name' => 'web'
        ]);

        // Berikan permission Manage Biometric ke Kasir agar bisa mengakses Kiosk
        $biometricPermission = Permission::where('name', 'Manage Biometric')->first();
        if ($biometricPermission) {
            $kasirRole->givePermissionTo($biometricPermission);
        }

        // Cari Super Admin role dan sync permission Manage Karyawan
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($manageKaryawan);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus role Karyawan Outlet ( Non Kasir )
        $role = Role::where('name', 'Karyawan Outlet ( Non Kasir )')->first();
        if ($role) {
            $role->delete();
        }
    }
};
