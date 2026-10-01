<?php

use Illuminate\Database\Migrations\Migration;
use App\Services\BillingConsistencyAuditService;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Reconcile all bills.paid_amount and bills.status with transaction_details (Single Source of Truth).
     */
    public function up(): void
    {
        try {
            $updatedCount = BillingConsistencyAuditService::executeTransactionReconciliation(false);
            Log::info("[Migration:reconcile_bills_with_transaction_details] Successfully reconciled {$updatedCount} bills to match transaction_details SSoT.");
        } catch (\Throwable $e) {
            Log::error("[Migration:reconcile_bills_with_transaction_details] Error during reconciliation: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive data reconciliation cannot and should not be rolled back
    }
};
