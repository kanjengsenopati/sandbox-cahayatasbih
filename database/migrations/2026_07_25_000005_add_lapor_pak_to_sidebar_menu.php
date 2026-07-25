<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\MenuNavigation;
use App\Models\SubMenuNavigation;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Hapus atau update URL lama jika ada
        SubMenuNavigation::where('url', '/admin/laporpak')->update(['url' => '/report-laporpak']);

        $laporanMenu = MenuNavigation::where('name', 'Laporan')->first();

        if ($laporanMenu) {
            SubMenuNavigation::updateOrCreate(
                [
                    'menu_navigation_id' => $laporanMenu->id,
                    'name' => 'Lapor Pak (Aduan)',
                ],
                [
                    'url' => '/report-laporpak',
                    'permission' => 'Manage Laporan Santri',
                    'order' => 10,
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $laporanMenu = MenuNavigation::where('name', 'Laporan')->first();

        if ($laporanMenu) {
            SubMenuNavigation::where('menu_navigation_id', $laporanMenu->id)
                ->where('name', 'Lapor Pak (Aduan)')
                ->delete();
        }
    }
};
