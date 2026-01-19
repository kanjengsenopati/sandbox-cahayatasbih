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
        Schema::table('ppdb_tracks', function (Blueprint $table) {
            // Link to BillType for Daftar Ulang billing
            $table->uuid('bill_type_id')->nullable()->after('link_whatsapp_group');

            // JSON field for custom installment schedule
            $table->json('installment_plan')->nullable()->after('bill_type_id');

            // Add name for Track (e.g., "Reguler", "Jamaah")
            $table->string('name')->nullable()->after('registration_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppdb_tracks', function (Blueprint $table) {
            $table->dropColumn(['bill_type_id', 'installment_plan', 'name']);
        });
    }
};
