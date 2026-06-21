<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('karyawans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('admin_id')->unique();
            $table->string('kamar')->nullable();
            $table->string('jabatan')->nullable();
            $table->uuid('outlet_id');
            $table->string('section')->nullable();
            $table->decimal('gaji_bulan', 12, 2)->default(0);
            $table->decimal('gaji_hari', 12, 2)->default(0);
            $table->integer('hari_kerja')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->foreign('outlet_id')->references('id')->on('outlets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
