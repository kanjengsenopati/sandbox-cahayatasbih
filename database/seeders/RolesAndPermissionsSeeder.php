<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define all permissions
        $permissions = [
            // Admin management
            'Manage Admin',
            'Create Admin',
            'Edit Admin',
            'Delete Admin',
            
            // Role management
            'Manage Role',
            'Create Role',
            'Edit Role',
            'Delete Role',
            
            // Perizinan (Permits) CRUD Matrix
            'Manage Perizinan',
            'Approve Perizinan',
            'Scan Perizinan',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // 2. Define and setup Roles
        
        // Super Admin Role
        $superAdminRole = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web'
        ]);
        // Super Admin gets all permissions
        $superAdminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        // Ustadz Asrama (Dormitory Head) Role
        $ustadzAsramaRole = Role::firstOrCreate([
            'name' => 'Ustadz Asrama',
            'guard_name' => 'web'
        ]);
        $ustadzAsramaRole->syncPermissions([
            'Manage Perizinan',
            'Approve Perizinan'
        ]);

        // Petugas Keamanan (Security) Role
        $securityRole = Role::firstOrCreate([
            'name' => 'Petugas Keamanan',
            'guard_name' => 'web'
        ]);
        $securityRole->syncPermissions([
            'Scan Perizinan'
        ]);
    }
}
