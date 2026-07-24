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

            // Petugas / Ustadz CRUD Permissions
            'Manage Petugas',
            'Create Petugas',
            'Edit Petugas',
            'Delete Petugas',

            // Asrama / Kamar CRUD Permissions
            'Manage Asrama',
            'Create Asrama',
            'Edit Asrama',
            'Delete Asrama',

            // Laporan Pos Multi Outlet CRUD Permissions
            'Manage Laporan Pos Multi Outlet',
            'Create Laporan Pos Multi Outlet',
            'Edit Laporan Pos Multi Outlet',
            'Delete Laporan Pos Multi Outlet',
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

        // Penanggung Jawab Asrama (Dormitory Head) Role
        $ustadzAsramaRole = Role::firstOrCreate([
            'name' => 'Penanggung Jawab Asrama',
            'guard_name' => 'web'
        ]);
        $ustadzAsramaRole->syncPermissions([
            'Manage Perizinan',
            'Approve Perizinan',
            'Manage Asrama',
            'Create Asrama',
            'Edit Asrama',
            'Delete Asrama'
        ]);

        // Petugas Keamanan (Security) Role
        $securityRole = Role::firstOrCreate([
            'name' => 'Petugas Keamanan',
            'guard_name' => 'web'
        ]);
        $securityRole->syncPermissions([
            'Scan Perizinan'
        ]);

        // Kasir Karyawan Outlet Role
        $kasirOutletRole = Role::firstOrCreate([
            'name' => 'Kasir Karyawan Outlet',
            'guard_name' => 'web'
        ]);
        $kasirOutletPermissions = [
            'Manage Barang', 'Create Barang', 'Edit Barang', 'Delete Barang', 'View Barang',
            'View Kategori Barang', 'Create Kategori Barang', 'Edit Kategori Barang', 'Delete Kategori Barang',
            'View Stock History', 'Create Stock History', 'Edit Stock History', 'Delete Stock History',
            'Manage Pos Kasir', 'Create Pos Kasir', 'POS Outlet', 'Laporan'
        ];
        foreach ($kasirOutletPermissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        $kasirOutletRole->givePermissionTo(Permission::whereIn('name', $kasirOutletPermissions)->get());
    }
}
