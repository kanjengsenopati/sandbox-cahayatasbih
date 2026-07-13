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
        // 1. Reset paid_amount to 0 for all bills
        DB::table('bills')->update(['paid_amount' => 0]);

        // 2. Fetch and aggregate all successful transaction details
        $details = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('bills', 'transaction_details.bill_id', '=', 'bills.id')
            ->whereIn('transactions.status', ['PAID', 'approved', 'SUCCESS'])
            ->whereNull('transaction_details.deleted_at')
            ->whereNull('transactions.deleted_at')
            ->select(
                'transaction_details.bill_id',
                'transaction_details.amount as detail_amount',
                'bills.amount as bill_amount'
            )
            ->orderBy('transaction_details.id')
            ->get();

        $billPayments = [];
        foreach ($details as $detail) {
            $paid = is_null($detail->detail_amount) ? $detail->bill_amount : $detail->detail_amount;
            if (!isset($billPayments[$detail->bill_id])) {
                $billPayments[$detail->bill_id] = 0;
            }
            $billPayments[$detail->bill_id] += $paid;
        }

        foreach ($billPayments as $billId => $totalPaid) {
            DB::table('bills')
                ->where('id', $billId)
                ->increment('paid_amount', $totalPaid);
        }

        // 3. For any bill where status is PAID but paid_amount is less than amount,
        // force paid_amount = amount to ensure legacy/manual consistency.
        DB::table('bills')
            ->where('status', 'PAID')
            ->whereRaw('paid_amount < amount')
            ->update(['paid_amount' => DB::raw('amount')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback action needed for recalculation
    }
};
