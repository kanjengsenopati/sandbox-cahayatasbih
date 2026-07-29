<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Admin;
use App\Models\Outlet;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Create role 'Koordinator Cahaya Mart'
        $role = Role::firstOrCreate([
            'name' => 'Koordinator Cahaya Mart',
            'guard_name' => 'web'
        ]);

        // 3. Define permissions required for Koordinator Cahaya Mart
        $permissions = [
            'Manage Barang',
            'Manage Pos Kasir',
            'Manage Laporan Pos Kasir',
            'Manage Laporan Pos Multi Outlet',
            'Manage Laporan Rugi Laba',
            'Manage Karyawan',
            'Manage Shift',
            'Manage Laporan Presensi',
            'Manage Tagihan',
            'Manage Saldo Santri',
            'Create Saldo Santri',
            'Edit Saldo Santri',
            'View Saldo Santri',
            'Manage Tabungan Santri',
            'Manage Laporan Transaksi',
            'Manage Laporan Tagihan',
            'Manage Laporan Santri',
            'Manage Audit dan Sinkron',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'web'
            ]);
        }

        $role->syncPermissions($permissions);

        // 4. Get Koperasi Outlet ID
        $koperasi = Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
        $koperasiId = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';

        // 5. Assign role & outlet to target users (Ambar, Maulani, Maulana)
        $targetAdmins = Admin::where('email', 'maulana@cahayatasbih.or.id')
            ->orWhere('name', 'like', '%Ambar%')
            ->orWhere('name', 'like', '%Maulani%')
            ->orWhere('name', 'like', '%Maulana%')
            ->get();

        foreach ($targetAdmins as $admin) {
            $admin->syncRoles(['Koordinator Cahaya Mart']);
            $admin->update([
                'role_id' => $role->id,
                'outlet_id' => $koperasiId
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $role = Role::where('name', 'Koordinator Cahaya Mart')->first();
        if ($role) {
            $role->delete();
        }
    }
};
