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

        $permissions = [
            'Manage Asrama',
            'Create Asrama',
            'Edit Asrama',
            'Delete Asrama',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Automatically assign new permissions to all existing web roles initially to avoid lockouts
        $roles = Role::where('guard_name', 'web')->get();
        foreach ($roles as $role) {
            $role->givePermissionTo($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'Manage Asrama',
            'Create Asrama',
            'Edit Asrama',
            'Delete Asrama',
        ];

        foreach ($permissions as $name) {
            Permission::where(['name' => $name, 'guard_name' => 'web'])->delete();
        }
    }
};
