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
        if (!Schema::hasTable('student_barcode_histories')) {
            Schema::create('student_barcode_histories', function (Blueprint $table) {
                $table->id();
                $table->string('student_id');
                $table->string('old_barcode', 64)->nullable();
                $table->string('new_barcode', 64);
                $table->string('action_type', 32); // 'manual_edit', 'generated', 'rollback'
                $table->unsignedBigInteger('admin_id')->nullable();
                $table->timestamps();

                $table->index('student_id');
                $table->index('new_barcode');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_barcode_histories');
    }
};
