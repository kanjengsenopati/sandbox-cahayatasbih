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
        // 1. Index komposit pada saldo_histories (student_id, status, created_at)
        if (Schema::hasTable('saldo_histories')) {
            Schema::table('saldo_histories', function (Blueprint $table) {
                $table->index(['student_id', 'status', 'created_at'], 'idx_sh_student_status_created');
            });
        }

        // 2. Index komposit pada bills (student_id, status, bill_type_id)
        if (Schema::hasTable('bills')) {
            Schema::table('bills', function (Blueprint $table) {
                $table->index(['student_id', 'status', 'bill_type_id'], 'idx_bills_student_status_type');
            });
        }

        // 3. Index komposit pada point_of_sale_transactions (status, outlet_id, created_at)
        if (Schema::hasTable('point_of_sale_transactions')) {
            Schema::table('point_of_sale_transactions', function (Blueprint $table) {
                $table->index(['status', 'outlet_id', 'created_at'], 'idx_pos_status_outlet_created');
            });
        }

        // 4. Index komposit pada transactions (student_id, type, status)
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index(['student_id', 'type', 'status'], 'idx_tx_student_type_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('saldo_histories')) {
            Schema::table('saldo_histories', function (Blueprint $table) {
                $table->dropIndex('idx_sh_student_status_created');
            });
        }

        if (Schema::hasTable('bills')) {
            Schema::table('bills', function (Blueprint $table) {
                $table->dropIndex('idx_bills_student_status_type');
            });
        }

        if (Schema::hasTable('point_of_sale_transactions')) {
            Schema::table('point_of_sale_transactions', function (Blueprint $table) {
                $table->dropIndex('idx_pos_status_outlet_created');
            });
        }

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex('idx_tx_student_type_status');
            });
        }
    }
};
