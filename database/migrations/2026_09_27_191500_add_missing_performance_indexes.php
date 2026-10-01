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
        if (Schema::hasTable('transaction_details')) {
            Schema::table('transaction_details', function (Blueprint $table) {
                $table->index('bill_id', 'idx_td_bill_id');
            });
        }

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index(['status', 'type', 'payment_method_id'], 'idx_tx_status_type_pm');
            });
        }

        if (Schema::hasTable('cash_flows')) {
            Schema::table('cash_flows', function (Blueprint $table) {
                $table->index(['type', 'status', 'date'], 'idx_cf_type_status_date');
                $table->index('outlet_id', 'idx_cf_outlet_id');
            });
        }

        if (Schema::hasTable('transaction_proofs')) {
            Schema::table('transaction_proofs', function (Blueprint $table) {
                $table->index(['transaction_id', 'is_active', 'status'], 'idx_tp_tx_active_status');
            });
        }

        if (Schema::hasTable('bills')) {
            Schema::table('bills', function (Blueprint $table) {
                $table->index('payment_rate_item_id', 'idx_bills_payment_rate_item_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('transaction_details')) {
            Schema::table('transaction_details', function (Blueprint $table) {
                $table->dropIndex('idx_td_bill_id');
            });
        }

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex('idx_tx_status_type_pm');
            });
        }

        if (Schema::hasTable('cash_flows')) {
            Schema::table('cash_flows', function (Blueprint $table) {
                $table->dropIndex('idx_cf_type_status_date');
                $table->dropIndex('idx_cf_outlet_id');
            });
        }

        if (Schema::hasTable('transaction_proofs')) {
            Schema::table('transaction_proofs', function (Blueprint $table) {
                $table->dropIndex('idx_tp_tx_active_status');
            });
        }

        if (Schema::hasTable('bills')) {
            Schema::table('bills', function (Blueprint $table) {
                $table->dropIndex('idx_bills_payment_rate_item_id');
            });
        }
    }
};
