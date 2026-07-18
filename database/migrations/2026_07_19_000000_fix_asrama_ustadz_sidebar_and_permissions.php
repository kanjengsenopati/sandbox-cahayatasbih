<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\MenuNavigation;
use App\Models\SubMenuNavigation;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Seed missing permissions for Petugas and Asrama
        $permissions = [
            'Manage Petugas',
            'Create Petugas',
            'Edit Petugas',
            'Delete Petugas',
            'Manage Asrama',
            'Create Asrama',
            'Edit Asrama',
            'Delete Asrama',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Assign all permissions to Super Admin and Superadmin roles
        $allPermissions = Permission::where('guard_name', 'web')->get();
        $rolesToSync = ['Super Admin', 'Superadmin'];
        foreach ($rolesToSync as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->syncPermissions($allPermissions);
            }
        }

        // Also assign Asrama permissions to Penanggung Jawab Asrama if role exists
        $pjAsramaRole = Role::where('name', 'Penanggung Jawab Asrama')->where('guard_name', 'web')->first();
        if ($pjAsramaRole) {
            $pjAsramaRole->givePermissionTo([
                'Manage Asrama',
                'Create Asrama',
                'Edit Asrama',
                'Delete Asrama',
            ]);
        }

        // 2. Update Sidebar Menu Structure
        $masterDataMenu = MenuNavigation::where('name', 'Master Data')->first();
        if ($masterDataMenu) {
            // Update permissions on Master Data parent menu
            $masterDataMenu->permission = 'Manage Wali Santri,Manage Santri,Manage Asrama,Manage Petugas,Manage Outlet,Manage PPDB,Manage Kategori Arus Kas';
            $masterDataMenu->save();

            // Remove any officer submenu under Menu Pengaturan if duplicate
            $pengaturanMenu = MenuNavigation::where('name', 'Menu Pengaturan')->orWhere('name', 'Pengaturan')->first();
            if ($pengaturanMenu) {
                SubMenuNavigation::where('menu_navigation_id', $pengaturanMenu->id)
                    ->where('url', '/officer')
                    ->delete();
            }

            // Upsert Data Ustadz & Petugas under Master Data
            SubMenuNavigation::updateOrCreate(
                [
                    'menu_navigation_id' => $masterDataMenu->id,
                    'url' => '/officer',
                ],
                [
                    'name' => 'Data Ustadz & Petugas',
                    'permission' => 'Manage Petugas',
                    'order' => 3,
                    'is_active' => true,
                ]
            );

            // Upsert Data Kamar Santri under Master Data
            SubMenuNavigation::updateOrCreate(
                [
                    'menu_navigation_id' => $masterDataMenu->id,
                    'url' => '/asrama',
                ],
                [
                    'name' => 'Data Kamar Santri',
                    'permission' => 'Manage Asrama',
                    'order' => 4,
                    'is_active' => true,
                ]
            );

            // Update orders for other submenus under Master Data
            SubMenuNavigation::where('menu_navigation_id', $masterDataMenu->id)
                ->where('url', '/user')
                ->update(['order' => 1]);

            SubMenuNavigation::where('menu_navigation_id', $masterDataMenu->id)
                ->where('url', '/student')
                ->update(['order' => 2]);

            SubMenuNavigation::where('menu_navigation_id', $masterDataMenu->id)
                ->where('url', '/outlet')
                ->update(['order' => 5]);

            SubMenuNavigation::where('menu_navigation_id', $masterDataMenu->id)
                ->where('url', '/ppdb')
                ->update(['order' => 6]);

            SubMenuNavigation::where('menu_navigation_id', $masterDataMenu->id)
                ->where('url', '/cashflow-category')
                ->update(['order' => 7]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op to prevent data loss in rollback
    }
};
