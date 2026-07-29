<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Admin;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Ensure permissions exist
        $permissions = [
            'Create Saldo Santri',
            'Edit Saldo Santri',
            'View Saldo Santri',
            'Manage Saldo Santri',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'web'
            ]);
        }

        // 3. Sync permissions to role 'Koordinator Cahaya Mart'
        $role = Role::where('name', 'Koordinator Cahaya Mart')->first();
        if ($role) {
            $role->givePermissionTo($permissions);
        }

        // 4. Assign permissions directly to target users if needed
        $targetAdmins = Admin::where('email', 'maulana@cahayatasbih.or.id')
            ->orWhere('name', 'like', '%Ambar%')
            ->orWhere('name', 'like', '%Maulani%')
            ->orWhere('name', 'like', '%Maulana%')
            ->get();

        foreach ($targetAdmins as $admin) {
            if ($role) {
                $admin->assignRole($role);
            }
            $admin->givePermissionTo($permissions);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
