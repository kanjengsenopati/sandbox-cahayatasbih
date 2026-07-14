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
            // Laporan
            'Manage Laporan Pos Kasir',
            'Manage Laporan Tagihan',
            'Manage Laporan Santri',
            'Manage Laporan Nilai Akademik',
            'Manage Laporan Tahfidz',
            'Manage Laporan Perilaku Siswa',
            'Manage Laporan Saldo Santri',
            'Manage Laporan Fee Aplikasi',
            'Manage Laporan Transaksi',

            // Master Data
            'Manage Wali Santri',
            'Manage Santri',
            'Manage PPDB',
            'Manage Kategori Arus Kas',
            'Manage Barang',
            'Manage Pos Kasir',

            // Entri Data
            'Manage Tabungan Santri',
            'Manage Jadwal',
            'Manage Tahfidz',
            'Manage Tagihan',
            'Manage Perilaku Santri',
            'Manage Nilai Pelajaran',
            'Manage Prestasi Santri',
            'Manage Nilai Santri',
            'Manage Arus Kas',

            // Pengaturan
            'permission',
            'Manage Informasi',
            'Manage Metode Pembayaran',
            'Manage Menu Aplikasi',
            'Manage Kontak Bantuan',
            'Manage Bank',
            'Item Bayar',
            'Manage Item Bayar',
            'Manage Jenis Bayar',
            'Manage Petugas',
            'app-information',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Automatically assign all permissions with guard 'web' to Super Admin and Superadmin
        $allPermissions = Permission::where('guard_name', 'web')->get();
        $rolesToSync = ['Super Admin', 'Superadmin'];
        foreach ($rolesToSync as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->syncPermissions($allPermissions);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op to prevent accidental deletion of permissions in production rollback
    }
};
