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
        Schema::table('outlet_handovers', function (Blueprint $table) {
            $table->uuid('recipient_outlet_id')->nullable()->after('outlet_id');
            $table->uuid('recipient_id')->nullable()->after('recipient_outlet_id');

            $table->foreign('recipient_outlet_id')->references('id')->on('outlets')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('admins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outlet_handovers', function (Blueprint $table) {
            $table->dropForeign(['recipient_outlet_id']);
            $table->dropForeign(['recipient_id']);
            $table->dropColumn(['recipient_outlet_id', 'recipient_id']);
        });
    }
};
