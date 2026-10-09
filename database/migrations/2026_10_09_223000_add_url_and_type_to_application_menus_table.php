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
            $table->string('type', 20)->default('internal')->after('flag');
            $table->text('url')->nullable()->after('type');
            $table->string('wa_number', 30)->nullable()->after('url');
            $table->text('wa_message')->nullable()->after('wa_number');
            $table->string('icon', 50)->nullable()->after('wa_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_menus', function (Blueprint $table) {
            $table->dropColumn(['type', 'url', 'wa_number', 'wa_message', 'icon']);
        });
    }
};
