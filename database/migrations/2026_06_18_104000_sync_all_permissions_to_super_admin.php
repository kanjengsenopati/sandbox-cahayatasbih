<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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

        // 1. Pastikan izin-izin krusial yang baru dibuat sudah ada di database
        $newPermissions = [
            'Manage Laporan Presensi',
            'Manage Payroll',
            'Create Payroll',
            'Approve Payroll',
            'Pay Payroll',
            'Manage Pengaturan Aplikasi'
        ];

        foreach ($newPermissions as $permName) {
            Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'web'
            ]);
        }

        // 2. Ambil semua izin dengan guard 'web'
        $allPermissions = Permission::where('guard_name', 'web')->get();

        // 3. Sinkronisasi ke role 'Super Admin' dan 'Superadmin'
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
        // Kebbalikan sinkronisasi tidak menghapus izin utama
    }
};
