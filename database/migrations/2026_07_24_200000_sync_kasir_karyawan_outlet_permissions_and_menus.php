<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Admin;
use App\Models\MenuNavigation;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure required permissions exist
        $requiredPerms = [
            'Manage Pos Kasir',
            'Create Pos Kasir',
            'Manage Barang',
            'Manage Laporan Presensi',
            'Manage Biometric',
        ];

        foreach ($requiredPerms as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 2. Sync permissions for role Kasir Karyawan Outlet
        $role = Role::firstOrCreate(['name' => 'Kasir Karyawan Outlet', 'guard_name' => 'web']);
        $role->syncPermissions($requiredPerms);

        // 3. Assign role to YOGO, WAWA, QURATUL AINI strictly
        $users = Admin::whereIn('name', ['YOGO', 'WAWA', 'QURATUL AINI'])->get();
        foreach ($users as $user) {
            $user->syncRoles(['Kasir Karyawan Outlet']);
        }

        // 4. Update parent menu Laporan permission in DB (exclude Manage Laporan Presensi from parent Laporan menu)
        $laporanMenu = MenuNavigation::where('name', 'Laporan')->first();
        if ($laporanMenu) {
            $laporanMenu->update([
                'permission' => 'Manage Laporan Tagihan,Manage Laporan Santri,Manage Laporan Nilai Akademik,Manage Laporan Tahfidz,Manage Laporan Perilaku Siswa,Manage Laporan Saldo Santri,Manage Laporan Fee Aplikasi,Manage Laporan Transaksi,Manage Laporan Audit Log'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert parent menu Laporan permission
        $laporanMenu = MenuNavigation::where('name', 'Laporan')->first();
        if ($laporanMenu) {
            $laporanMenu->update([
                'permission' => 'Manage Laporan Tagihan,Manage Laporan Santri,Manage Laporan Nilai Akademik,Manage Laporan Tahfidz,Manage Laporan Perilaku Siswa,Manage Laporan Saldo Santri,Manage Laporan Fee Aplikasi,Manage Laporan Transaksi,Manage Laporan Audit Log,Manage Laporan Presensi'
            ]);
        }
    }
};
