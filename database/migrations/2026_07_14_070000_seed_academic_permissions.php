<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = ['Sekolah', 'Tahun Ajaran', 'Semester', 'Mata Pelajaran', 'Kenaikan Kelas', 'Kelulusan Santri'];
        $permissions = [];

        foreach ($modules as $module) {
            $permissions[] = 'Manage ' . $module;
            $permissions[] = 'Create ' . $module;
            $permissions[] = 'Edit ' . $module;
            $permissions[] = 'Delete ' . $module;
        }

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Automatically assign new permissions to Super Admin and Superadmin to avoid lockout
        $rolesToSync = ['Super Admin', 'Superadmin'];
        foreach ($rolesToSync as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }

        // Dynamically assign permissions to role ID 14 if it exists
        $role14 = Role::find(14);
        if ($role14) {
            $role14->givePermissionTo($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = ['Sekolah', 'Tahun Ajaran', 'Semester', 'Mata Pelajaran', 'Kenaikan Kelas', 'Kelulusan Santri'];
        $permissions = [];

        foreach ($modules as $module) {
            $permissions[] = 'Manage ' . $module;
            $permissions[] = 'Create ' . $module;
            $permissions[] = 'Edit ' . $module;
            $permissions[] = 'Delete ' . $module;
        }

        foreach ($permissions as $name) {
            Permission::where(['name' => $name, 'guard_name' => 'web'])->delete();
        }
    }
};
