<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Recalculate paid_amount for all bills using successful transaction details
        DB::statement("
            UPDATE bills 
            LEFT JOIN (
                SELECT 
                    td.bill_id, 
                    SUM(COALESCE(td.amount, b.amount)) AS total_paid
                FROM transaction_details td
                JOIN transactions t ON td.transaction_id = t.id
                JOIN bills b ON td.bill_id = b.id
                WHERE t.status IN ('PAID', 'approved', 'SUCCESS')
                  AND td.deleted_at IS NULL
                  AND t.deleted_at IS NULL
                GROUP BY td.bill_id
            ) AS payments ON bills.id = payments.bill_id
            SET bills.paid_amount = COALESCE(payments.total_paid, 0)
        ");

        // 2. For any bill where status is PAID but paid_amount is less than amount,
        // force paid_amount = amount to ensure legacy/manual consistency.
        DB::statement("
            UPDATE bills
            SET paid_amount = amount
            WHERE status = 'PAID' AND paid_amount < amount
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback action needed for recalculation
    }
};
