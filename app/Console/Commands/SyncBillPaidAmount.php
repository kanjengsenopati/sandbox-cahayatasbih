<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncBillPaidAmount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:sync-paid-amount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit and synchronize tb_bills paid_amount with actual TransactionDetails.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Memulai sinkronisasi paid_amount di tabel bills...");

        $bills = Bill::whereHas('transactionDetails')
                     ->orWhere('paid_amount', '>', 0)
                     ->get();

        $fixedCount = 0;
        $bar = $this->output->createProgressBar(count($bills));

        DB::beginTransaction();
        try {
            foreach ($bills as $bill) {
                // Ambil total valid dari transaction_details
                $actualPaid = DB::table('transaction_details')
                    ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
                    ->where('transaction_details.bill_id', $bill->id)
                    ->where('transactions.status', Transaction::STATUS_PAID)
                    ->whereNull('transaction_details.deleted_at')
                    ->whereNull('transactions.deleted_at')
                    ->sum('transaction_details.amount');

                $currentPaid = $bill->getRawOriginal('paid_amount');
                
                if ($actualPaid != $currentPaid) {
                    $newStatus = ($actualPaid >= $bill->amount) ? Bill::STATUS_PAID : ($actualPaid > 0 ? 'PARTIAL' : Bill::STATUS_UNPAID);
                    
                    // Kita update langsung ke database (menghindari accessor interference)
                    DB::table('bills')->where('id', $bill->id)->update([
                        'paid_amount' => $actualPaid,
                        'status'      => $newStatus
                    ]);

                    $fixedCount++;
                }

                $bar->advance();
            }

            DB::commit();
            $bar->finish();
            $this->newLine(2);
            $this->info("Sinkronisasi selesai! {$fixedCount} data tagihan berhasil diperbaiki.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->newLine();
            $this->error("Terjadi kesalahan: " . $e->getMessage());
        }
    }
}
