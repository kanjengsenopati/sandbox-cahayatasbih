<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $auditMenu = DB::table('menu_navigations')->where('name', 'Audit dan Sinkron')->first();

        if ($auditMenu) {
            $exists = DB::table('sub_menu_navigations')->where('name', 'Advanced Sync Saldo')->exists();

            if (!$exists) {
                DB::table('sub_menu_navigations')->insert([
                    'id' => Str::uuid()->toString(),
                    'menu_navigation_id' => $auditMenu->id,
                    'name' => 'Advanced Sync Saldo',
                    'url' => '/admin/audit/advanced-sync',
                    'permission' => 'Manage Audit dan Sinkron',
                    'is_active' => true,
                    'order' => 6,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('sub_menu_navigations')->where('name', 'Advanced Sync Saldo')->delete();
    }
};
