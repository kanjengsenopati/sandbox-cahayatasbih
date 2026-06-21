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

        // Buat permission CRUD Karyawan
        $permissions = [
            'Create Karyawan',
            'Edit Karyawan',
            'Delete Karyawan'
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'web'
            ]);
        }

        // Dapatkan permission Manage Karyawan juga jika ada
        $manageKaryawan = Permission::where('name', 'Manage Karyawan')->first();

        // Hubungkan ke role 'Super Admin' dan 'Superadmin'
        $roles = ['Super Admin', 'Superadmin'];
        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                if ($manageKaryawan) {
                    $role->givePermissionTo($manageKaryawan);
                }
                $role->givePermissionTo($permissions);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = [
            'Create Karyawan',
            'Edit Karyawan',
            'Delete Karyawan'
        ];

        Permission::whereIn('name', $permissions)->delete();
    }
};
