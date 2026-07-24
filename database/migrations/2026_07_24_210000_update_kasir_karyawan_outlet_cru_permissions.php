<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Admin;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure permissions exist
        $perms = [
            'Manage Pos Kasir',
            'Create Pos Kasir',
            'Manage Barang',
            'Create Barang',
            'Edit Barang',
            'Manage Laporan Presensi',
            'Manage Biometric',
        ];

        foreach ($perms as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 2. Sync permissions for role Kasir Karyawan Outlet (CRU for Barang, no Delete)
        $role = Role::firstOrCreate(['name' => 'Kasir Karyawan Outlet', 'guard_name' => 'web']);
        $role->syncPermissions($perms);

        // 3. Ensure YOGO, WAWA, QURATUL AINI have Kasir Karyawan Outlet role
        $users = Admin::whereIn('name', ['YOGO', 'WAWA', 'QURATUL AINI'])->get();
        foreach ($users as $user) {
            $user->syncRoles(['Kasir Karyawan Outlet']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
