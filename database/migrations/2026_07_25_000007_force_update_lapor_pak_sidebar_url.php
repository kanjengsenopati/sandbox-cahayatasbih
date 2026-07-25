<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('sub_menu_navigations')) {
            DB::table('sub_menu_navigations')
                ->where('url', '/admin/laporpak')
                ->orWhere('name', 'LIKE', '%Lapor Pak%')
                ->update(['url' => '/report-laporpak']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
