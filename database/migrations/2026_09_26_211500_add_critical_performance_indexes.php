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
        // 1. point_of_sale_transaction_details (250k+ rows) - foreign keys were missing indexes
        if (Schema::hasTable('point_of_sale_transaction_details')) {
            Schema::table('point_of_sale_transaction_details', function (Blueprint $table) {
                $table->index('point_of_sale_transaction_id', 'idx_pos_td_transaction_id');
                $table->index('item_id', 'idx_pos_td_item_id');
            });
        }

        // 2. transaction_details (170k+ rows) - transaction_id lookup was scanning full table
        if (Schema::hasTable('transaction_details')) {
            Schema::table('transaction_details', function (Blueprint $table) {
                $table->index('transaction_id', 'idx_td_tx_id');
                $table->index('saldo_history_id', 'idx_td_saldo_hist_id');
            });
        }

        // 3. point_of_sale_transactions (145k+ rows) - student_id and admin_id lookups
        if (Schema::hasTable('point_of_sale_transactions')) {
            Schema::table('point_of_sale_transactions', function (Blueprint $table) {
                $table->index('student_id', 'idx_pos_student_id');
                $table->index('admin_id', 'idx_pos_admin_id');
                $table->index('saldo_history_id', 'idx_pos_sh_id');
            });
        }

        // 4. transactions (144k+ rows) - user_id and created_at
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index('user_id', 'idx_tx_user_id');
                $table->index('created_at', 'idx_tx_created_at');
            });
        }

        // 5. students - nis, classroom, school, user_id, status lookups
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                $table->index('nis', 'idx_students_nis');
                $table->index(['classroom_id', 'status'], 'idx_students_classroom_status');
                $table->index('school_id', 'idx_students_school_id');
                $table->index('user_id', 'idx_students_user_id');
                $table->index('status', 'idx_students_status');
            });
        }

        // 6. saving_histories
        if (Schema::hasTable('saving_histories')) {
            Schema::table('saving_histories', function (Blueprint $table) {
                $table->index('student_id', 'idx_saving_histories_student_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('point_of_sale_transaction_details')) {
            Schema::table('point_of_sale_transaction_details', function (Blueprint $table) {
                $table->dropIndex('idx_pos_td_transaction_id');
                $table->dropIndex('idx_pos_td_item_id');
            });
        }

        if (Schema::hasTable('transaction_details')) {
            Schema::table('transaction_details', function (Blueprint $table) {
                $table->dropIndex('idx_td_tx_id');
                $table->dropIndex('idx_td_saldo_hist_id');
            });
        }

        if (Schema::hasTable('point_of_sale_transactions')) {
            Schema::table('point_of_sale_transactions', function (Blueprint $table) {
                $table->dropIndex('idx_pos_student_id');
                $table->dropIndex('idx_pos_admin_id');
                $table->dropIndex('idx_pos_sh_id');
            });
        }

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex('idx_tx_user_id');
                $table->dropIndex('idx_tx_created_at');
            });
        }

        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropIndex('idx_students_nis');
                $table->dropIndex('idx_students_classroom_status');
                $table->dropIndex('idx_students_school_id');
                $table->dropIndex('idx_students_user_id');
                $table->dropIndex('idx_students_status');
            });
        }

        if (Schema::hasTable('saving_histories')) {
            Schema::table('saving_histories', function (Blueprint $table) {
                $table->dropIndex('idx_saving_histories_student_id');
            });
        }
    }
};
