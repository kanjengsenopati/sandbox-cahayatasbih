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
        Schema::create('asramas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->uuid('host_admin_id')->nullable();
            $table->timestamps();

            // Foreign key to admins table
            $table->foreign('host_admin_id')->references('id')->on('admins')->onDelete('set null');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->uuid('asrama_id')->nullable()->after('id');
            $table->foreign('asrama_id')->references('id')->on('asramas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['asrama_id']);
            $table->dropColumn('asrama_id');
        });

        Schema::dropIfExists('asramas');
    }
};
