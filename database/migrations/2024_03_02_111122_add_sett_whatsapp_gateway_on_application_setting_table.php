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
            $table->string('payment_expire_time')->default('00:00');
            $table->text('link_whatsapp')->nullable();
            $table->string('number_whatsapp')->nullable();
            $table->string('device_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_settings', function (Blueprint $table) {
            $table->dropColumn('payment_expire_time');
            $table->dropColumn('link_whatsapp');
            $table->dropColumn('number_whatsapp');
            $table->dropColumn('device_id');
        });
    }
};
