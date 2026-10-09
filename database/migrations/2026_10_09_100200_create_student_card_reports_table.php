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
        if (!Schema::hasTable('student_card_reports')) {
            Schema::create('student_card_reports', function (Blueprint $table) {
                $table->id();
                $table->string('student_id');
                $table->string('reported_by', 36)->nullable();
                $table->string('issue_type', 32); // 'rusak', 'tidak_bisa_transaksi', 'hilang'
                $table->text('notes')->nullable();
                $table->string('status', 32)->default('pending'); // 'pending', 'completed', 'rejected'
                $table->string('processed_by', 36)->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->index('student_id');
                $table->index('status');
                $table->index('issue_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_card_reports');
    }
};
