<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Admin;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pastikan cache spatie ter-reset agar tidak ada stale cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Buat permission jika belum ada
        $permission = Permission::firstOrCreate([
            'name' => 'Manage Audit dan Sinkron',
            'guard_name' => 'web'
        ]);

        // 2. Assign ke Role Super Admin
        $superAdminRole = Role::where('name', 'Super Admin')->where('guard_name', 'web')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permission);
        }

        // 3. Assign ke Role KOORDINATOR CAHAYA MART
        $koordinatorRole = Role::where('name', 'KOORDINATOR CAHAYA MART')->where('guard_name', 'web')->first();
        if ($koordinatorRole) {
            $koordinatorRole->givePermissionTo($permission);
        }

        // 4. Secara spesifik assign ke 3 orang tersebut dan super admin by name
        // Meskipun mereka sudah punya Role di atas, memberikannya secara langsung memastikan akses mereka
        $admins = Admin::whereIn('name', [
            'Siswanto', 
            'Arsito Ari', 
            'AMBAR EKA WATI', 
            'MAULANI IZZA MAHARANI', 
            'MAULANA SYARIF'
        ])->get();

        foreach ($admins as $admin) {
            $admin->givePermissionTo($permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::where('name', 'Manage Audit dan Sinkron')->where('guard_name', 'web')->first();
        if ($permission) {
            // Remove assignment from roles
            $superAdminRole = Role::where('name', 'Super Admin')->where('guard_name', 'web')->first();
            if ($superAdminRole) {
                $superAdminRole->revokePermissionTo($permission);
            }

            $koordinatorRole = Role::where('name', 'KOORDINATOR CAHAYA MART')->where('guard_name', 'web')->first();
            if ($koordinatorRole) {
                $koordinatorRole->revokePermissionTo($permission);
            }

            // Remove assignment from specific admins
            $admins = Admin::whereIn('name', [
                'Siswanto', 
                'Arsito Ari', 
                'AMBAR EKA WATI', 
                'MAULANI IZZA MAHARANI', 
                'MAULANA SYARIF'
            ])->get();

            foreach ($admins as $admin) {
                $admin->revokePermissionTo($permission);
            }
        }
    }
};
