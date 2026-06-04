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
        Schema::create('admin_outlets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('admin_id');
            $table->uuid('outlet_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->foreign('outlet_id')->references('id')->on('outlets')->onDelete('cascade');

            $table->unique(['admin_id', 'outlet_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_outlets');
    }
};
