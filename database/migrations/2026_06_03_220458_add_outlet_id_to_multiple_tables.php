<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'items', 
            'stock_histories', 
            'category_items', 
            'point_of_sale_transactions', 
            'point_of_sale_carts', 
            'cash_flows', 
            'admins'
        ];

        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->uuid('outlet_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'items', 
            'stock_histories', 
            'category_items', 
            'point_of_sale_transactions', 
            'point_of_sale_carts', 
            'cash_flows', 
            'admins'
        ];
        
        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->dropColumn('outlet_id');
            });
        }
    }
};
