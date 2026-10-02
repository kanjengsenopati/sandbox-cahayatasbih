<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Temukan semua transaksi TYPE_SALDO dan TYPE_SAVING
        $transactions = DB::table('transactions')
            ->whereIn('type', [Transaction::TYPE_SALDO, Transaction::TYPE_SAVING])
            ->pluck('id');

        // Hapus TransactionDetail yang saldo_history_id nya NULL tapi punya kembaran
        // Hapus SEMUA transaction_details yang saldo_history_id nya NULL khusus transaksi ini,
        // KARENA transaksi saldo/saving PASTI di-link dengan saldo_history_id jika sudah komplit.
        // Orphan detail ini adalah sisa bug pembuatan ganda.
        DB::table('transaction_details')
            ->whereIn('transaction_id', $transactions)
            ->whereNull('saldo_history_id')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse data deletion
    }
};
