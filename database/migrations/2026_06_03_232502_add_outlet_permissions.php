<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add Outlet permissions
        $permissions = [
            'Manage Outlet',
            'Create Outlet',
            'Edit Outlet',
            'Delete Outlet',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Auto assign to Superadmin if exists
        $role = Role::where('name', 'Superadmin')->first();
        if ($role) {
            $role->givePermissionTo($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = [
            'Manage Outlet',
            'Create Outlet',
            'Edit Outlet',
            'Delete Outlet',
        ];

        Permission::whereIn('name', $permissions)->delete();
    }
};
