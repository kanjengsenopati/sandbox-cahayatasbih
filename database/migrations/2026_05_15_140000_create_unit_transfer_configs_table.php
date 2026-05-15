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
        Schema::create('unit_transfer_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('from_school_id');
            $table->uuid('to_school_id');
            $table->uuid('to_classroom_id');
            $table->uuid('bill_type_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('from_school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('to_school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('to_classroom_id')->references('id')->on('classrooms')->onDelete('cascade');
            $table->foreign('bill_type_id')->references('id')->on('bill_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_transfer_configs');
    }
};
