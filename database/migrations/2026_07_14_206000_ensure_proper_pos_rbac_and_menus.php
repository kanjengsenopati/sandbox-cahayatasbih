<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
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

        // 1. Revoke POS / Koperasi / Outlet permissions from TU roles if they exist
        $tuRoles = Role::whereIn('name', ['Tata Usaha', 'TU', 'tu', 'tata usaha', 'Staff TU', 'Staf Tata Usaha'])->get();
        $permissionsToRevoke = [
            'Manage Pos Kasir',
            'Create Pos Kasir',
            'Manage Laporan Pos Kasir',
            'Manage Laporan Pos Multi Outlet',
            'Create Laporan Pos Multi Outlet',
            'Edit Laporan Pos Multi Outlet',
            'Delete Laporan Pos Multi Outlet',
            'Manage Laporan Rugi Laba',
            'Create Laporan Rugi Laba',
            'Edit Laporan Rugi Laba',
            'Delete Laporan Rugi Laba',
            'Manage Outlet',
            'Create Outlet',
            'Edit Outlet',
            'Delete Outlet',
            'Manage Barang',
            'Create Barang',
            'Edit Barang',
            'Delete Barang',
        ];

        foreach ($tuRoles as $role) {
            foreach ($permissionsToRevoke as $perm) {
                if ($role->hasPermissionTo($perm)) {
                    $role->revokePermissionTo($perm);
                }
            }
        }

        // 2. Ensure dynamic submenus in the database have proper permission assignments
        // POS Kasir
        DB::table('sub_menu_navigations')
            ->where(function($q) {
                $q->where('url', 'like', '%order-item%')
                  ->orWhere('name', 'like', '%POS Kasir%')
                  ->orWhere('name', 'like', '%Kasir%');
            })
            ->update(['permission' => 'Manage Pos Kasir']);

        // Transaksi POS / Laporan POS
        DB::table('sub_menu_navigations')
            ->where(function($q) {
                $q->where('url', 'like', '%pos-transaction%')
                  ->orWhere('url', 'like', '%report-pos%')
                  ->orWhere('name', 'like', '%Transaksi POS%')
                  ->orWhere('name', 'like', '%Laporan POS%');
            })
            ->update(['permission' => 'Manage Laporan Pos Multi Outlet']);

        // Rugi Laba
        DB::table('sub_menu_navigations')
            ->where(function($q) {
                $q->where('url', 'like', '%report-profit-loss%')
                  ->orWhere('name', 'like', '%Rugi Laba%');
            })
            ->update(['permission' => 'Manage Laporan Rugi Laba']);

        // Barang & Inventory
        DB::table('sub_menu_navigations')
            ->where(function($q) {
                $q->where('url', 'like', '%/item%')
                  ->orWhere('name', 'like', '%Barang%')
                  ->orWhere('name', 'like', '%Inventory%')
                  ->orWhere('name', 'like', '%Stok%');
            })
            ->update(['permission' => 'Manage Barang']);

        // Data Outlet
        DB::table('sub_menu_navigations')
            ->where(function($q) {
                $q->where('url', 'like', '%/outlet%')
                  ->orWhere(function($sq) {
                      $sq->where('name', 'like', '%Outlet%')
                         ->where('name', 'not like', '%Laporan%')
                         ->where('name', 'not like', '%Rugi Laba%');
                  });
            })
            ->update(['permission' => 'Manage Outlet']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
