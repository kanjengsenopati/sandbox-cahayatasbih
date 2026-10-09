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
        Schema::table('application_menus', function (Blueprint $table) {
            $table->foreignUuid('officer_id')
                ->nullable()
                ->after('type')
                ->constrained('officers')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_menus', function (Blueprint $table) {
            $table->dropForeign(['officer_id']);
            $table->dropColumn('officer_id');
        });
    }
};
