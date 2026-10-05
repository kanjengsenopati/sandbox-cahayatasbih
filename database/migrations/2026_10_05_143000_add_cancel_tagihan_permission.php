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

        // 1. Buat permission khusus pembatalan tagihan
        $cancelPermissions = [
            'Cancel Tagihan',
            'Batal Transaksi Tagihan',
        ];

        foreach ($cancelPermissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // 2. Berikan izin ini HANYA kepada Super Admin secara default.
        // Role lain (seperti kasir, admin keuangan biasa) TIDAK otomatis mendapatkan permission ini.
        $superAdminRoles = Role::whereIn('name', ['Super Admin', 'Superadmin'])
            ->where('guard_name', 'web')
            ->get();

        foreach ($superAdminRoles as $role) {
            $role->givePermissionTo($cancelPermissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op to preserve permission security
    }
};
