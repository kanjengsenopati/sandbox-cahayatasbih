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
        $exists = DB::table('application_menus')
            ->where('flag', 'riwayat')
            ->orWhere('name', 'Riwayat')
            ->exists();

        if (!$exists) {
            // Geser menu yang memiliki order >= 4 agar ada ruang untuk Riwayat di posisi 4
            DB::table('application_menus')
                ->where('order', '>=', 4)
                ->increment('order');

            DB::table('application_menus')->insert([
                'id' => (string) Str::uuid(),
                'name' => 'Riwayat',
                'flag' => 'riwayat',
                'status' => 1,
                'order' => 4,
                'type' => 'internal',
                'icon' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Normalisasi seluruh urutan secara sekuensial (1, 2, 3, ...)
            $menus = DB::table('application_menus')->orderBy('order', 'asc')->orderBy('created_at', 'asc')->get();
            foreach ($menus as $index => $m) {
                DB::table('application_menus')
                    ->where('id', $m->id)
                    ->update(['order' => $index + 1]);
            }

            \Illuminate\Support\Facades\Cache::forever('wali_menus_version', time());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('application_menus')->where('flag', 'riwayat')->delete();

        $menus = DB::table('application_menus')->orderBy('order', 'asc')->orderBy('created_at', 'asc')->get();
        foreach ($menus as $index => $m) {
            DB::table('application_menus')
                ->where('id', $m->id)
                ->update(['order' => $index + 1]);
        }

        \Illuminate\Support\Facades\Cache::forever('wali_menus_version', time());
    }
};
