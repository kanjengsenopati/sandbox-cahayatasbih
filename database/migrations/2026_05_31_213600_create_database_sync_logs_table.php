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
        Schema::create('database_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('status'); // 'running', 'success', 'failed'
            $table->dateTime('started_at');
            $table->dateTime('finished_at')->nullable();
            $table->string('duration')->nullable();
            $table->json('report')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('database_sync_logs');
    }
};
