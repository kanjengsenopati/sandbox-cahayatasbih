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
        $koperasi = DB::table('outlets')
            ->where('name', 'Koperasi')
            ->orWhere('code', 'KPR')
            ->first();
            
        $koperasiId = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';

        $tables = [
            'items',
            'category_items',
            'stock_histories',
            'point_of_sale_transactions',
            'point_of_sale_carts',
            'cash_flows',
            'saldo_histories'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'outlet_id')) {
                DB::table($table)
                    ->whereNull('outlet_id')
                    ->update(['outlet_id' => $koperasiId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data migration - no rollback needed
    }
};
