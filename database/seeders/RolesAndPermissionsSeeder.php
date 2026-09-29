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

            // Tagihan CRUD Matrix
            'Manage Tagihan',
            'Create Tagihan',
            'Edit Tagihan',
            'Delete Tagihan',
            'Edit Status Tagihan',

            // Transaksi CRUD Matrix
            'Manage Transaksi',
            'Create Transaksi',
            'Edit Transaksi',
            'Delete Transaksi',

            // Saldo Santri CRUD Matrix
            'Manage Saldo Santri',
            'Create Saldo Santri',
            'Edit Saldo Santri',
            'Delete Saldo Santri',

            // Tabungan Santri CRUD Matrix
            'Manage Tabungan Santri',
            'Create Tabungan Santri',
            'Edit Tabungan Santri',
            'Delete Tabungan Santri',

            // Arus Kas & Kategori Arus Kas
            'Manage Arus Kas',
            'Create Arus Kas',
            'Edit Arus Kas',
            'Delete Arus Kas',
            'Manage Kategori Arus Kas',
            'Create Kategori Arus Kas',
            'Edit Kategori Arus Kas',
            'Delete Kategori Arus Kas',

            // Keuangan & Bank
            'Manage Bank',
            'Create Bank',
            'Edit Bank',
            'Delete Bank',
            'Manage Jenis Bayar',
            'Create Jenis Bayar',
            'Edit Jenis Bayar',
            'Delete Jenis Bayar',
            'Manage Item Bayar',
            'Create Item Bayar',
            'Edit Item Bayar',
            'Delete Item Bayar',
            'Manage Tarif Pembayaran',
            'Create Tarif Pembayaran',
            'Edit Tarif Pembayaran',
            'Delete Tarif Pembayaran',

            // Laporan & POS
            'Delete Transaksi POS',
            'Delete Laporan Saldo Santri',
            'Delete Laporan Transaksi',

            // Santri Granular
            'Manage Santri',
            'Create Santri',
            'Edit Santri',
            'Delete Santri',
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
