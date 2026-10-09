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
        $masterData = MenuNavigation::where('name', 'Master Data')->first();
        if ($masterData) {
            SubMenuNavigation::firstOrCreate(
                ['url' => '/student-card-reports'],
                [
                    'menu_navigation_id' => $masterData->id,
                    'name' => 'Laporan Kendala Kartu',
                    'permission' => 'Manage Santri,Lapor Kartu Santri',
                    'order' => 8,
                    'is_active' => true,
                ]
            );

            // Clear cache of global db menus
            \Illuminate\Support\Facades\Cache::forget('global_db_menus');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SubMenuNavigation::where('url', '/student-card-reports')->delete();
        \Illuminate\Support\Facades\Cache::forget('global_db_menus');
    }
};
