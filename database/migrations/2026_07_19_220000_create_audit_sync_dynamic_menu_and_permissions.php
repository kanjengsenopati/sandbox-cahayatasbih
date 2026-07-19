<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Admin;
use App\Models\MenuNavigation;
use App\Models\SubMenuNavigation;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Buat permission baru
        $permissionName = 'Manage Audit dan Sinkron';
        Permission::firstOrCreate([
            'name' => $permissionName,
            'guard_name' => 'web'
        ]);

        // 3. Sinkronisasikan permission ke role 'Super Admin' dan 'Superadmin'
        $rolesToSync = ['Super Admin', 'Superadmin'];
        foreach ($rolesToSync as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->givePermissionTo($permissionName);
            }
        }

        // 4. Berikan akses default otomatis ke user Siswanto dan Arsito Ari K jika ada
        $usersToAssign = Admin::where('name', 'like', '%Siswanto%')
            ->orWhere('name', 'like', '%Arsito%')
            ->get();

        foreach ($usersToAssign as $user) {
            if (!$user->hasPermissionTo($permissionName)) {
                $user->givePermissionTo($permissionName);
            }
        }

        // 5. Tambahkan main sidebar menu baru 'Audit dan Sinkron'
        // Geser order menu yang lebih besar dari atau sama dengan 7
        MenuNavigation::where('order', '>=', 7)->increment('order');

        $mainMenu = MenuNavigation::create([
            'name' => 'Audit dan Sinkron',
            'icon' => 'fa-solid fa-arrows-spin',
            'url' => null,
            'order' => 7,
            'is_active' => true,
            'permission' => $permissionName,
        ]);

        // 6. Daftarkan sub-menu di bawah 'Audit dan Sinkron'
        $subMenus = [
            [
                'name' => 'Sinkronisasi Database',
                'url' => '/admin/audit/sync',
                'order' => 1,
            ],
            [
                'name' => 'Diagnostik Sistem',
                'url' => '/admin/audit/diagnostics',
                'order' => 2,
            ],
            [
                'name' => 'Siswa Duplikat',
                'url' => '/admin/audit/duplicate-students',
                'order' => 3,
            ]
        ];

        foreach ($subMenus as $sub) {
            $mainMenu->subMenuNavigation()->create([
                'name' => $sub['name'],
                'url' => $sub['url'],
                'order' => $sub['order'],
                'permission' => $permissionName,
                'is_active' => true,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissionName = 'Manage Audit dan Sinkron';

        // Hapus sub-menu & menu navigasi
        $mainMenu = MenuNavigation::where('name', 'Audit dan Sinkron')->first();
        if ($mainMenu) {
            $mainMenu->subMenuNavigation()->delete();
            $mainMenu->delete();
            // Kembalikan increment order
            MenuNavigation::where('order', '>', 7)->decrement('order');
        }

        // Hapus permission dari users & roles, lalu hapus permission itu sendiri
        $permission = Permission::where('name', $permissionName)->where('guard_name', 'web')->first();
        if ($permission) {
            $roles = Role::where('guard_name', 'web')->get();
            foreach ($roles as $role) {
                $role->revokePermissionTo($permissionName);
            }

            $users = Admin::all();
            foreach ($users as $user) {
                $user->revokePermissionTo($permissionName);
            }

            $permission->delete();
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
