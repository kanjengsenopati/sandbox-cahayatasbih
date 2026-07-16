<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Transaction;

class AutoExpiredClass extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-expired-class';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically expires pending transactions that have passed their expiry time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get transactions that are pending payment and past their expiry time
        $expiredTxIds = Transaction::where('status', Transaction::STATUS_PENDING_PAYMENT)
            ->where('expiry_time', '<=', Carbon::now())
            ->pluck('id');

        $count = $expiredTxIds->count();

        if ($count > 0) {
            // Delete related SaldoHistory and SavingHistory
            $details = \App\Models\TransactionDetail::whereIn('transaction_id', $expiredTxIds)->get();
            foreach ($details as $detail) {
                if ($detail->saldo_history_id) {
                    \App\Models\SaldoHistory::where('id', $detail->saldo_history_id)->delete();
                }
                if ($detail->saving_history_id) {
                    \App\Models\SavingHistory::where('id', $detail->saving_history_id)->delete();
                }
            }

            // Update status of transactions to EXPIRED
            Transaction::whereIn('id', $expiredTxIds)->update(['status' => Transaction::STATUS_EXPIRED]);
        }

        // Output the result
        $this->info("Expired $count transactions.");
    }
}
