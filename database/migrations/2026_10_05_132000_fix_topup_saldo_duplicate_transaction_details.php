<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Dapatkan seluruh transaksi saldo & tabungan yang memiliki transaction_details
        $transactions = DB::table('transactions')
            ->whereIn('type', [Transaction::TYPE_SALDO, Transaction::TYPE_SAVING])
            ->select('id', 'type', 'pay_amount', 'unique_payment')
            ->get();

        foreach ($transactions as $tx) {
            $baseAmount = max(0, (int)$tx->pay_amount - (int)($tx->unique_payment ?? 0));

            $details = DB::table('transaction_details')
                ->where('transaction_id', $tx->id)
                ->whereNull('deleted_at')
                ->get();

            if ($details->isEmpty()) {
                continue;
            }

            // Jika ada lebih dari 1 detail (akibat bug duplikasi pembuatan)
            if ($details->count() > 1) {
                // Prioritaskan baris yang memiliki saldo_history_id atau saving_history_id
                $primaryDetail = $details->first(function ($d) {
                    return !empty($d->saldo_history_id) || !empty($d->saving_history_id);
                });

                if (!$primaryDetail) {
                    $primaryDetail = $details->first();
                }

                // Update amount di detail utama
                DB::table('transaction_details')
                    ->where('id', $primaryDetail->id)
                    ->update([
                        'amount' => $baseAmount,
                        'updated_at' => now(),
                    ]);

                // Hapus detail duplikat lainnya
                DB::table('transaction_details')
                    ->where('transaction_id', $tx->id)
                    ->where('id', '!=', $primaryDetail->id)
                    ->delete();
            } else {
                // Hanya ada 1 detail, pastikan amount terisi dengan benar (tidak null / 0)
                $first = $details->first();
                if (empty($first->amount) || (int)$first->amount === 0) {
                    DB::table('transaction_details')
                        ->where('id', $first->id)
                        ->update([
                            'amount' => $baseAmount,
                            'updated_at' => now(),
                        ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data cleanup cannot be reverted
    }
};
