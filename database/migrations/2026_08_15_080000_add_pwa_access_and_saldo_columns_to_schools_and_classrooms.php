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
            if (!Schema::hasColumn('schools', 'allow_pwa_login')) {
                $table->boolean('allow_pwa_login')->default(true)->after('icon_name');
            }
            if (!Schema::hasColumn('schools', 'show_pwa_saldo')) {
                $table->boolean('show_pwa_saldo')->default(true)->after('allow_pwa_login');
            }
            if (!Schema::hasColumn('schools', 'allow_pwa_saldo_payment')) {
                $table->boolean('allow_pwa_saldo_payment')->default(true)->after('show_pwa_saldo');
            }
        });

        Schema::table('classrooms', function (Blueprint $table) {
            if (!Schema::hasColumn('classrooms', 'allow_pwa_login')) {
                $table->boolean('allow_pwa_login')->nullable()->default(null)->after('school_id');
            }
            if (!Schema::hasColumn('classrooms', 'show_pwa_saldo')) {
                $table->boolean('show_pwa_saldo')->nullable()->default(null)->after('allow_pwa_login');
            }
            if (!Schema::hasColumn('classrooms', 'allow_pwa_saldo_payment')) {
                $table->boolean('allow_pwa_saldo_payment')->nullable()->default(null)->after('show_pwa_saldo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            if (Schema::hasColumn('classrooms', 'allow_pwa_saldo_payment')) {
                $table->dropColumn('allow_pwa_saldo_payment');
            }
            if (Schema::hasColumn('classrooms', 'show_pwa_saldo')) {
                $table->dropColumn('show_pwa_saldo');
            }
            if (Schema::hasColumn('classrooms', 'allow_pwa_login')) {
                $table->dropColumn('allow_pwa_login');
            }
        });

        Schema::table('schools', function (Blueprint $table) {
            if (Schema::hasColumn('schools', 'allow_pwa_saldo_payment')) {
                $table->dropColumn('allow_pwa_saldo_payment');
            }
            if (Schema::hasColumn('schools', 'show_pwa_saldo')) {
                $table->dropColumn('show_pwa_saldo');
            }
            if (Schema::hasColumn('schools', 'allow_pwa_login')) {
                $table->dropColumn('allow_pwa_login');
            }
        });
    }
};
