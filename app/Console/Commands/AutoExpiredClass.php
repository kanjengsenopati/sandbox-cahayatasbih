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
        $expiredTransactions = Transaction::where('status', Transaction::STATUS_PENDING_PAYMENT)
            ->where('expiry_time', '<=', Carbon::now())
            ->update(['status' => Transaction::STATUS_EXPIRED]);

        // Output the result
        $this->info("Expired $expiredTransactions transactions.");
    }
}
