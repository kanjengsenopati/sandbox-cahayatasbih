<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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

        // 1. Buat izin CRUD untuk Shift
        $shiftPermissions = [
            'Manage Shift',
            'Create Shift',
            'Edit Shift',
            'Delete Shift',
        ];

        foreach ($shiftPermissions as $permName) {
            Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'web'
            ]);
        }

        // 2. Asosiasikan ke Super Admin dan Superadmin
        $roles = ['Super Admin', 'Superadmin'];
        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->givePermissionTo($shiftPermissions);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $shiftPermissions = [
            'Manage Shift',
            'Create Shift',
            'Edit Shift',
            'Delete Shift',
        ];

        Permission::whereIn('name', $shiftPermissions)->delete();
    }
};
