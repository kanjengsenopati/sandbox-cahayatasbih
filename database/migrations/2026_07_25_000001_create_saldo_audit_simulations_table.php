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
        if (!Schema::hasTable('saldo_audit_simulations')) {
            Schema::create('saldo_audit_simulations', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('student_id')->index();
                $table->string('student_nis')->nullable();
                $table->string('student_name');
                $table->string('classroom_name')->nullable();
                $table->bigInteger('current_saldo')->default(0);
                $table->bigInteger('simulated_saldo')->default(0);
                $table->bigInteger('saldo_diff')->default(0);
                $table->bigInteger('current_saving')->default(0);
                $table->bigInteger('simulated_saving')->default(0);
                $table->bigInteger('saving_diff')->default(0);
                $table->string('issue_type')->nullable(); // MISSING_TOPUP_30DAYS, DUPLICATE_IN, NEGATIVE_BALANCE, MISMATCH, OK
                $table->text('issue_description')->nullable();
                $table->enum('status', ['DRAFT', 'APPLIED', 'SKIPPED'])->default('DRAFT');
                $table->timestamp('simulated_at')->useCurrent();
                $table->timestamp('applied_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('student_saldo_backups')) {
            Schema::create('student_saldo_backups', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('student_id')->index();
                $table->bigInteger('backup_saldo')->default(0);
                $table->bigInteger('backup_saving')->default(0);
                $table->string('reason')->nullable();
                $table->uuid('admin_id')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saldo_audit_simulations');
        Schema::dropIfExists('student_saldo_backups');
    }
};
