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
        Schema::table('point_of_sale_transactions', function (Blueprint $table) {
            $table->dropColumn('payment_method_id');
            $table->uuid('saldo_history_id')->nullable()->after('admin_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('point_of_sale_transactions', function (Blueprint $table) {
            $table->uuid('payment_method_id')->after('admin_id');
            $table->dropColumn('saldo_history_id');
        });
    }
};
