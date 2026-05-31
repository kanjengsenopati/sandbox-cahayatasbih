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
        Schema::table('bill_types', function (Blueprint $table) {
            $table->boolean('use_wali_filter')->default(false)->after('type');
            $table->boolean('use_gender_filter')->default(false)->after('use_wali_filter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_types', function (Blueprint $table) {
            $table->dropColumn(['use_wali_filter', 'use_gender_filter']);
        });
    }
};
