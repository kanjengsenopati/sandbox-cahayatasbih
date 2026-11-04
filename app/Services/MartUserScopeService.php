<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\AdminOutlet;
use App\Models\Outlet;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MartUserScopeService
{
    /**
     * Required permissions for Arus Kas Mart reporting and access.
     */
    public const PERMISSIONS = [
        'Manage Laporan Pos Multi Outlet',
        'Manage Laporan Transaksi',
        'POS Outlet',
        'Laporan',
        'Manage Pos Kasir',
        'Manage Barang',
        'Manage Saldo Santri',
        'View Nilai Profit POS',
    ];

    /**
     * Synchronize permissions, roles, and target users assignment.
     * Target assignments:
     * - Koordinator Pondok Mart: Maulana Syarif, Rani, dan Ambar
     * - Outlet: Gus Maulana Rifqi
     */
    public static function syncTargetUsers(): void
    {
        // 1. Reset Spatie permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Ensure all relevant permissions exist
        foreach (self::PERMISSIONS as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // 3. Ensure Roles exist
        $koordinatorRoles = [
            'Koordinator Pondok Mart',
            'KOORDINATOR PONDOK MART',
            'Koordinator Cahaya Mart',
            'KOORDINATOR CAHAYA MART',
        ];

        foreach ($koordinatorRoles as $rName) {
            $role = Role::firstOrCreate(['name' => $rName, 'guard_name' => 'web']);
            $role->givePermissionTo(self::PERMISSIONS);
        }

        $outletRoles = [
            'Outlet',
            'Kasir Karyawan Outlet',
        ];

        foreach ($outletRoles as $rName) {
            $role = Role::firstOrCreate(['name' => $rName, 'guard_name' => 'web']);
            $role->givePermissionTo([
                'Manage Laporan Pos Multi Outlet',
                'Manage Laporan Transaksi',
                'POS Outlet',
                'Laporan',
                'Manage Pos Kasir',
                'Manage Barang',
            ]);
        }

        // Ensure Super Admin has all permissions
        $superAdmin = Role::where('name', 'Super Admin')->where('guard_name', 'web')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo(self::PERMISSIONS);
        }

        // 4. Resolve Koperasi and default Outlet IDs
        $koperasiId = OutletContextService::getKoperasiOutletId();
        $outletModel = Outlet::where('name', 'OUTLET')->first();
        $defaultOutletId = $outletModel ? $outletModel->id : 'f5bd0a6f-4d59-48e3-ba33-b9c3571ba6f2';

        // 5. Configure Koordinator Pondok Mart: Maulana Syarif, Rani, and Ambar
        $koordinatorConfigs = [
            'maulanasyarif@gmail.com' => 'MAULANA SYARIF',
            'maulani@gmail.com' => 'MAULANI IZZA MAHARANI',
            'ambarsari@gmail.com' => 'AMBAR EKA WATI',
        ];

        foreach ($koordinatorConfigs as $email => $name) {
            $admin = Admin::where('email', $email)
                ->orWhere('name', 'LIKE', "%{$name}%")
                ->first();

            if ($admin) {
                // Assign Koordinator Pondok Mart role (and preserve KOORDINATOR CAHAYA MART)
                if (!$admin->hasRole('KOORDINATOR PONDOK MART')) {
                    $admin->assignRole('KOORDINATOR PONDOK MART');
                }
                if (!$admin->hasRole('Koordinator Pondok Mart')) {
                    $admin->assignRole('Koordinator Pondok Mart');
                }

                // Assign to Koperasi / Pondok Mart outlet
                $admin->outlet_id = $koperasiId;
                $admin->save();

                if ($koperasiId) {
                    $existsInPivot = AdminOutlet::where('admin_id', $admin->id)
                        ->where('outlet_id', $koperasiId)
                        ->exists();

                    if (!$existsInPivot) {
                        AdminOutlet::create([
                            'id' => (string) Str::uuid(),
                            'admin_id' => $admin->id,
                            'outlet_id' => $koperasiId,
                        ]);
                    }
                }
            }
        }

        // 6. Configure Outlet User: Gus Maulana Rifqi
        $rifqi = Admin::where('email', 'gusrifqi@email.com')
            ->orWhere('name', 'LIKE', '%GUS MAULANA RIFQI%')
            ->first();

        if ($rifqi) {
            // Remove Koordinator roles to strictly enforce Outlet scope
            foreach ($koordinatorRoles as $rName) {
                if ($rifqi->hasRole($rName)) {
                    $rifqi->removeRole($rName);
                }
            }

            // Assign Outlet roles
            if (!$rifqi->hasRole('Kasir Karyawan Outlet')) {
                $rifqi->assignRole('Kasir Karyawan Outlet');
            }
            if (!$rifqi->hasRole('Outlet')) {
                $rifqi->assignRole('Outlet');
            }

            // Direct assignment to OUTLET
            $rifqi->outlet_id = $defaultOutletId;
            $rifqi->save();

            // Clear Koperasi from pivot and assign OUTLET
            AdminOutlet::where('admin_id', $rifqi->id)
                ->where('outlet_id', $koperasiId)
                ->delete();

            $hasOutletPivot = AdminOutlet::where('admin_id', $rifqi->id)
                ->where('outlet_id', $defaultOutletId)
                ->exists();

            if (!$hasOutletPivot) {
                AdminOutlet::create([
                    'id' => (string) Str::uuid(),
                    'admin_id' => $rifqi->id,
                    'outlet_id' => $defaultOutletId,
                ]);
            }
        }

        // Clear menu and Spatie cache
        Cache::forget('global_db_menus');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Check if the authenticated user has restricted outlet scope.
     * Super Admin and Koordinator Pondok Mart oversee all outlets (Bank Saldo).
     * Staff assigned to specific outlet(s) are restricted to their assigned outlet(s).
     */
    public static function isUserOutletRestricted($user): bool
    {
        if (!$user) {
            return true;
        }

        if ($user->hasRole('Super Admin') || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())) {
            return false;
        }

        if (
            (method_exists($user, 'isKoordinatorPondokMart') && $user->isKoordinatorPondokMart()) ||
            (method_exists($user, 'isKoordinatorCahayaMart') && $user->isKoordinatorCahayaMart())
        ) {
            return false;
        }

        // Any user who is not Super Admin and not Koordinator Pondok Mart is outlet-restricted
        return true;
    }

    /**
     * Get allowed outlet IDs for the given user.
     * Returns empty array if user has unrestricted access (can view all).
     */
    public static function getAllowedOutletIds($user): array
    {
        if (!self::isUserOutletRestricted($user)) {
            return []; // Empty indicates unrestricted
        }

        return method_exists($user, 'getOutletIds') ? $user->getOutletIds() : [];
    }
}
