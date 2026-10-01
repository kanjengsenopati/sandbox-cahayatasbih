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
            // Tagihan
            'Manage Tagihan',
            'Create Tagihan',
            'Edit Tagihan',
            'Delete Tagihan',
            'Edit Status Tagihan',

            // Transaksi
            'Manage Transaksi',
            'Create Transaksi',
            'Edit Transaksi',
            'Delete Transaksi',

            // Saldo Santri
            'Manage Saldo Santri',
            'Create Saldo Santri',
            'Edit Saldo Santri',
            'Delete Saldo Santri',

            // Tabungan Santri
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

            // Santri & Siswa Granular
            'Create Santri',
            'Edit Santri',
            'Delete Santri',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Otomatis sinkronkan seluruh permission web ke Super Admin agar tidak terkunci
        $allPermissions = Permission::where('guard_name', 'web')->get();
        $rolesToSync = ['Super Admin', 'Superadmin'];
        foreach ($rolesToSync as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->givePermissionTo($allPermissions);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op to avoid breaking active permissions on rollback
    }
};
