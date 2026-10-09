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
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'View Nilai Profit POS',
            'Manage Barang', 'Create Barang', 'Edit Barang', 'Delete Barang', 'View Barang',
            'View Kategori Barang', 'Create Kategori Barang', 'Edit Kategori Barang', 'Delete Kategori Barang',
            'View Stock History', 'Create Stock History', 'Edit Stock History', 'Delete Stock History',
            'Manage Pos Kasir', 'Create Pos Kasir', 'POS Outlet', 'Laporan',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // Grant to Kasir Karyawan Outlet
        $kasirOutletRole = Role::where('name', 'Kasir Karyawan Outlet')->first();
        if ($kasirOutletRole) {
            $kasirOutletPermissions = [
                'Manage Barang', 'Create Barang', 'Edit Barang', 'Delete Barang', 'View Barang',
                'View Kategori Barang', 'Create Kategori Barang', 'Edit Kategori Barang', 'Delete Kategori Barang',
                'View Stock History', 'Create Stock History', 'Edit Stock History', 'Delete Stock History',
                'Manage Pos Kasir', 'Create Pos Kasir', 'POS Outlet', 'Laporan'
            ];
            $kasirOutletRole->givePermissionTo($kasirOutletPermissions);
        }

        // Grant View Nilai Profit POS to Super Admin and Koordinator
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo('View Nilai Profit POS');
        }

        $koordinator = Role::where('name', 'Koordinator Cahaya Mart')->first();
        if ($koordinator) {
            $koordinator->givePermissionTo('View Nilai Profit POS');
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep permissions or remove specific one if necessary
        $perm = Permission::where('name', 'View Nilai Profit POS')->first();
        if ($perm) {
            $perm->delete();
        }
    }
};
