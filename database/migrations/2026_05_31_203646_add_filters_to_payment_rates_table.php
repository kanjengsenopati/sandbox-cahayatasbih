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
        Schema::table('payment_rates', function (Blueprint $table) {
            $table->string('gender', 10)->nullable()->after('type');
            $table->string('jamaah_status', 20)->nullable()->after('gender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_rates', function (Blueprint $table) {
            $table->dropColumn(['gender', 'jamaah_status']);
        });
    }
};
