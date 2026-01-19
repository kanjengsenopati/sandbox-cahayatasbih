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
        Schema::table('saldo_histories', function (Blueprint $table) {
            $table->bigInteger('balance_before')->nullable();
            $table->bigInteger('balance_after')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saldo_histories', function (Blueprint $table) {
            $table->dropColumn('balance_before');
            $table->dropColumn('balance_after');
        });
    }
};
