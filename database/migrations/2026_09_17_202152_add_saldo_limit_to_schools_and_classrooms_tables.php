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
        Schema::table('schools', function (Blueprint $table) {
            $table->boolean('is_saldo_limit_active')->default(false)->after('allow_pwa_saldo_payment');
            $table->integer('saldo_limit')->nullable()->after('is_saldo_limit_active');
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->boolean('is_saldo_limit_active')->default(false)->after('allow_pwa_saldo_payment');
            $table->integer('saldo_limit')->nullable()->after('is_saldo_limit_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['is_saldo_limit_active', 'saldo_limit']);
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropColumn(['is_saldo_limit_active', 'saldo_limit']);
        });
    }
};
