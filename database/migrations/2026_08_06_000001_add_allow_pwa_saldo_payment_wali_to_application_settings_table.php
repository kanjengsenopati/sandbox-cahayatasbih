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
        Schema::table('application_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('application_settings', 'allow_pwa_saldo_payment_wali')) {
                $table->boolean('allow_pwa_saldo_payment_wali')->default(true)->after('pwa_login_disabled_message');
            }
            if (!Schema::hasColumn('application_settings', 'pwa_saldo_payment_disabled_message')) {
                $table->text('pwa_saldo_payment_disabled_message')->nullable()->after('allow_pwa_saldo_payment_wali');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_settings', function (Blueprint $table) {
            if (Schema::hasColumn('application_settings', 'allow_pwa_saldo_payment_wali')) {
                $table->dropColumn('allow_pwa_saldo_payment_wali');
            }
            if (Schema::hasColumn('application_settings', 'pwa_saldo_payment_disabled_message')) {
                $table->dropColumn('pwa_saldo_payment_disabled_message');
            }
        });
    }
};
