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
        Schema::create('application_menu_scopes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('application_menu_id');
            $table->uuid('school_id');
            $table->string('class_level')->nullable(); // VII, VIII, IX, X, XI, XII — null = semua jenjang di unit tsb
            $table->timestamps();

            $table->foreign('application_menu_id')->references('id')->on('application_menus')->onDelete('cascade');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');

            // Prevent duplicate scope entries
            $table->unique(['application_menu_id', 'school_id', 'class_level'], 'menu_scope_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_menu_scopes');
    }
};
