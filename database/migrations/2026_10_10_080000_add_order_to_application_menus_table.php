<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('application_menus', 'order')) {
            Schema::table('application_menus', function (Blueprint $table) {
                $table->integer('order')->default(0)->after('status');
            });
        }

        // Inisialisasi nomor urut berurutan langsung via DB query (tanpa terhalang $fillable)
        $menus = DB::table('application_menus')->orderBy('created_at', 'asc')->get();
        foreach ($menus as $index => $menu) {
            DB::table('application_menus')
                ->where('id', $menu->id)
                ->update(['order' => $index + 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('application_menus', 'order')) {
            Schema::table('application_menus', function (Blueprint $table) {
                $table->dropColumn('order');
            });
        }
    }
};
