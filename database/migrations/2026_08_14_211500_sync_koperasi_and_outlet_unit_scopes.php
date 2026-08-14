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
        // 1. Reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Define clean Koperasi Pesantren permissions (Shared & Koperasi only, NO Outlet-only perms)
        $koperasiPermissions = [
            'Manage Barang',
            'Create Barang',
            'Edit Barang',
            'Delete Barang',
            'Manage Pos Kasir',
            'Create Pos Kasir',
            'Manage Laporan Pos Kasir',
            'Manage Laporan Pos Multi Outlet',
            'Manage Saldo Santri',
            'Create Saldo Santri',
            'Edit Saldo Santri',
            'View Saldo Santri',
            'Manage Tabungan Santri',
            'Manage Tagihan',
            'Manage Laporan Tagihan',
            'Manage Laporan Santri',
            'Manage Laporan Saldo Santri',
            'Manage Laporan Transaksi',
            'Manage Audit dan Sinkron',
        ];

        foreach ($koperasiPermissions as $permName) {
            Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'web'
            ]);
        }

        // Sync to both name variants of Koordinator Cahaya Mart if any
        $roles = Role::whereIn('name', ['Koordinator Cahaya Mart', 'KOORDINATOR CAHAYA MART'])->get();
        foreach ($roles as $role) {
            $role->syncPermissions($koperasiPermissions);
        }

        // 3. Reset cached permissions again
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
