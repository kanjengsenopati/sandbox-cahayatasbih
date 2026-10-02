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
        // Gunakan subquery di level database untuk menghindari limit memori PHP 
        // dan limit placeholder (65,535) MySQL ketika data transaksi sangat banyak.
        DB::table('transaction_details')
            ->whereNull('saldo_history_id')
            ->whereIn('transaction_id', function ($query) {
                $query->select('id')
                      ->from('transactions')
                      ->whereIn('type', [Transaction::TYPE_SALDO, Transaction::TYPE_SAVING]);
            })
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
