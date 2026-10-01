<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BillingConsistencyAuditService;

class ReconcileBillTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bill:reconcile-transactions {--dry-run : Run in simulation mode without updating the database} {--student-id= : Reconcile for a specific student only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile bills paid_amount and status 100% against transaction_details (Single Source of Truth)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $studentId = $this->option('student-id');

        $this->info("=== SINKRONISASI REKONSILIASI TAGIHAN VS RIWAYAT TRANSAKSI (SSoT) ===");
        if ($isDryRun) {
            $this->warn("MODE: Simulasi (Dry-Run) - Data tidak akan diubah di database.");
        } else {
            $this->warn("MODE: Live Update - Memperbarui kolom paid_amount dan status di tabel bills.");
        }

        if ($studentId) {
            $this->line("Filter Siswa ID: {$studentId}");
        }

        $this->line("Memindai dan menghitung total pembayaran riil dari transaction_details...");

        $count = BillingConsistencyAuditService::executeTransactionReconciliation($isDryRun, $studentId);

        $this->newLine();
        if ($isDryRun) {
            $this->info("Simulasi selesai: {$count} tagihan terdeteksi memiliki selisih dan siap diselaraskan.");
        } else {
            $this->info("Sukses! {$count} tagihan berhasil diselaraskan mengikuti riwayat transaksi riil.");
        }

        return 0;
    }
}
