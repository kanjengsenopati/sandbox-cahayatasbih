<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('sub_menu_navigations')
            ->where('url', '/menu-navigation')
            ->update(['name' => 'Menu Backoffice']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('sub_menu_navigations')
            ->where('url', '/menu-navigation')
            ->update(['name' => 'Menu Navigasi']);
    }
};
