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

        // Buat izin CRUD untuk Biometrik
        $biometricPermissions = [
            'Manage Biometric',
            'Create Biometric',
            'Edit Biometric',
            'Delete Biometric',
        ];

        foreach ($biometricPermissions as $permName) {
            Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'web'
            ]);
        }

        // Asosiasikan ke Super Admin dan Superadmin
        $roles = ['Super Admin', 'Superadmin'];
        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->givePermissionTo($biometricPermissions);
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

        $biometricPermissions = [
            'Manage Biometric',
            'Create Biometric',
            'Edit Biometric',
            'Delete Biometric',
        ];

        Permission::whereIn('name', $biometricPermissions)->delete();
    }
};
