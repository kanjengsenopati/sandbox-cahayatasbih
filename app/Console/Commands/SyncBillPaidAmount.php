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
        $this->info("Memulai sinkronisasi tagihan terbayar (desync heal) untuk seluruh santri...");

        $studentIds = DB::table('transactions')
            ->where('type', Transaction::TYPE_BILL)
            ->whereIn('status', [Transaction::STATUS_PAID, 'paid', 'PAID', 'approved', 'APPROVED', 'SUCCESS', 'success', 'LUNAS', 'lunas'])
            ->whereNotNull('student_id')
            ->distinct()
            ->pluck('student_id');

        $totalStudents = count($studentIds);
        $this->info("Ditemukan {$totalStudents} santri yang memiliki riwayat pembayaran transaksi.");

        $bar = $this->output->createProgressBar($totalStudents);
        $bar->start();

        $processed = 0;
        foreach ($studentIds as $studentId) {
            \App\Services\TransactionService::syncStudentBillsFromPaidTransactions($studentId);
            $processed++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Sinkronisasi selesai! {$processed} santri telah berhasil disinkronkan datanya.");
    }
}
