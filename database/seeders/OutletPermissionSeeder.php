<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class OutletPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'Manage Outlet',
            'Create Outlet',
            'Edit Outlet',
            'Delete Outlet',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Auto assign to Superadmin if exists
        $role = Role::where('name', 'Superadmin')->first();
        if ($role) {
            $role->givePermissionTo($permissions);
        }
    }
}
