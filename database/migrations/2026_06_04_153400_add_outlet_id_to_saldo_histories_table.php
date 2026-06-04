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
        // 1. Create "Koperasi" outlet if not exists
        $koperasiId = DB::table('outlets')->where('name', 'Koperasi')->value('id');
        if (!$koperasiId) {
            $koperasiId = Str::uuid()->toString();
            DB::table('outlets')->insert([
                'id' => $koperasiId,
                'name' => 'Koperasi',
                'code' => 'KPR',
                'address' => '-',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Add outlet_id column to saldo_histories
        Schema::table('saldo_histories', function (Blueprint $table) {
            $table->uuid('outlet_id')->nullable()->after('student_id');
        });

        // 3. Assign all existing saldo_histories to Koperasi
        DB::table('saldo_histories')->whereNull('outlet_id')->update([
            'outlet_id' => $koperasiId,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saldo_histories', function (Blueprint $table) {
            $table->dropColumn('outlet_id');
        });
    }
};
