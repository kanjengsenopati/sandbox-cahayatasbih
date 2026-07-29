<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Ensure permission 'Manage Laporan Saldo Santri' exists
        $permissionName = 'Manage Laporan Saldo Santri';
        $permission = Permission::firstOrCreate([
            'name' => $permissionName,
            'guard_name' => 'web'
        ]);

        // 3. Grant permission to 'Koordinator Cahaya Mart' role
        $role = Role::where('name', 'Koordinator Cahaya Mart')->first();
        if ($role) {
            $role->givePermissionTo($permissionName);
        }

        // 4. Grant permission directly to target users
        $targetAdmins = Admin::where('email', 'maulana@cahayatasbih.or.id')
            ->orWhere('name', 'like', '%Ambar%')
            ->orWhere('name', 'like', '%Maulani%')
            ->orWhere('name', 'like', '%Maulana%')
            ->get();

        foreach ($targetAdmins as $admin) {
            if ($role) {
                $admin->assignRole($role);
            }
            $admin->givePermissionTo($permissionName);
        }

        // 5. Update sub_menu_navigations name to 'Saldo Santri' for /report-saldo or 'Tabungan & Saldo'
        DB::table('sub_menu_navigations')
            ->where('url', 'like', '%report-saldo%')
            ->orWhere('name', 'like', '%Tabungan & Saldo%')
            ->orWhere('name', 'like', '%Tabungan dan Saldo%')
            ->update([
                'name' => 'Saldo Santri',
                'updated_at' => now(),
            ]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
