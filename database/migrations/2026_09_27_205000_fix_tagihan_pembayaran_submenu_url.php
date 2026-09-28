<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('sub_menu_navigations')
            ->where('name', 'Tagihan Pembayaran')
            ->update(['url' => '/admin/audit/tagihan-pembayaran']);

        // Clear global menus cache so sidebar updates immediately
        Cache::forget('global_db_menus');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('sub_menu_navigations')
            ->where('name', 'Tagihan Pembayaran')
            ->update(['url' => 'admin/audit/tagihan-pembayaran']);

        Cache::forget('global_db_menus');
    }
};
