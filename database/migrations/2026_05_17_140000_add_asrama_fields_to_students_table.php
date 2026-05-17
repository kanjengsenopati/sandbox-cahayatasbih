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
        Schema::table('students', function (Blueprint $table) {
            $table->uuid('asrama_host_id')->nullable()->after('user_id');
            $table->string('asrama_name')->nullable()->after('asrama_host_id');

            $table->foreign('asrama_host_id')->references('id')->on('admins')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['asrama_host_id']);
            $table->dropColumn(['asrama_host_id', 'asrama_name']);
        });
    }
};
