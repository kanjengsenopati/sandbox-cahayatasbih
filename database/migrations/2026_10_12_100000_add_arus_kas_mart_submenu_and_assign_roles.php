<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\MenuNavigation;
use App\Models\SubMenuNavigation;
use App\Services\MartUserScopeService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Run role and user scope synchronization
        MartUserScopeService::syncTargetUsers();

        // 2. Locate parent menu 'Laporan'
        $mainMenu = MenuNavigation::where('name', 'Laporan')->first();
        if ($mainMenu) {
            // Ensure Menu Laporan has permissions for Mart and Outlet
            $perms = array_filter(array_map('trim', explode(',', $mainMenu->permission ?? '')));
            $requiredPerms = ['Manage Laporan Pos Multi Outlet', 'POS Outlet', 'Laporan'];
            foreach ($requiredPerms as $rp) {
                if (!in_array($rp, $perms)) {
                    $perms[] = $rp;
                }
            }
            $mainMenu->permission = implode(',', $perms);
            $mainMenu->save();

            // 3. Add or update SubMenuNavigation 'Arus Kas Mart'
            $existing = SubMenuNavigation::where('menu_navigation_id', $mainMenu->id)
                ->where(function ($q) {
                    $q->where('url', '/report-mart-cashflow')
                      ->orWhere('name', 'Arus Kas Mart');
                })
                ->first();

            if (!$existing) {
                SubMenuNavigation::create([
                    'id' => (string) Str::uuid(),
                    'menu_navigation_id' => $mainMenu->id,
                    'name' => 'Arus Kas Mart',
                    'url' => '/report-mart-cashflow',
                    'permission' => 'Manage Laporan Pos Multi Outlet,POS Outlet',
                    'order' => 3,
                    'is_active' => true,
                ]);
            } else {
                $existing->update([
                    'name' => 'Arus Kas Mart',
                    'url' => '/report-mart-cashflow',
                    'permission' => 'Manage Laporan Pos Multi Outlet,POS Outlet',
                    'is_active' => true,
                ]);
            }
        }

        Cache::forget('global_db_menus');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SubMenuNavigation::where('url', '/report-mart-cashflow')
            ->orWhere('name', 'Arus Kas Mart')
            ->delete();

        Cache::forget('global_db_menus');
    }
};
