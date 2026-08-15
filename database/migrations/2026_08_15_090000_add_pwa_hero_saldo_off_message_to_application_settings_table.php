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
            if (!Schema::hasColumn('application_settings', 'pwa_hero_saldo_off_message')) {
                $table->text('pwa_hero_saldo_off_message')->nullable()->after('pwa_saldo_payment_disabled_message');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_settings', function (Blueprint $table) {
            if (Schema::hasColumn('application_settings', 'pwa_hero_saldo_off_message')) {
                $table->dropColumn('pwa_hero_saldo_off_message');
            }
        });
    }
};
