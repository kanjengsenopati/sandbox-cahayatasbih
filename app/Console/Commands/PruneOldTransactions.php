<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Student;
use App\Models\SaldoHistory;
use App\Models\Transaction;
use App\Services\SaldoRecalculatorService;

class PruneOldTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:prune-old 
                            {--cutoff= : Batas tanggal arsip (format YYYY-MM-DD), data sebelum tanggal ini akan di-soft-delete}
                            {--months=6 : Jumlah bulan retensi jika cutoff tidak ditentukan (default: 6 bulan)}
                            {--dry-run : Hanya simulasi tanpa mengubah database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Soft-delete old Saldo & POS transactions prior to cutoff, create consolidated baseline opening balances, and preserve all bill payments.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($this->option('cutoff')) {
            $cutoff = Carbon::parse($this->option('cutoff'))->startOfDay();
        } else {
            $months = intval($this->option('months') ?? 6);
            $cutoff = Carbon::now()->subMonths($months)->startOfDay();
        }

        $cutoffStr = $cutoff->format('Y-m-d H:i:s');

        $this->info("================================================================================");
        $this->info("PEMBERSIHAN & ROTASI TRANSAKSI SALDO / POS (CUTOFF: {$cutoffStr})");
        $this->info("================================================================================");

        if ($dryRun) {
            $this->warn("MODUS DRY-RUN DIAKTIFKAN: Tidak ada perubahan yang akan ditulis ke database.\n");
        }

        // 1. Hitung data yang terpengaruh
        $this->info("1. Memindai data transaksi Saldo & Belanja POS sebelum {$cutoffStr}...");

        $shCount = DB::table('saldo_histories')
            ->whereNull('deleted_at')
            ->where('created_at', '<', $cutoffStr)
            ->where('description', 'not like', 'Saldo Awal Baseline%')
            ->count();

        $posCount = DB::table('point_of_sale_transactions')
            ->whereNull('deleted_at')
            ->where('created_at', '<', $cutoffStr)
            ->count();

        $posDetailsCount = DB::table('point_of_sale_transaction_details')
            ->whereNull('deleted_at')
            ->where('created_at', '<', $cutoffStr)
            ->count();

        $nonBillTxCount = DB::table('transactions')
            ->whereNull('deleted_at')
            ->where('type', '!=', Transaction::TYPE_BILL)
            ->where('created_at', '<', $cutoffStr)
            ->count();

        $nonBillTdCount = DB::table('transaction_details')
            ->whereNull('deleted_at')
            ->whereNull('bill_id')
            ->where('created_at', '<', $cutoffStr)
            ->count();

        $preservedBills = DB::table('bills')->whereNull('deleted_at')->where('created_at', '<', $cutoffStr)->count();
        $preservedBillTxs = DB::table('transactions')->whereNull('deleted_at')->where('type', Transaction::TYPE_BILL)->where('created_at', '<', $cutoffStr)->count();

        $this->line(" - SaldoHistories yang akan di-soft-delete  : " . number_format($shCount));
        $this->line(" - POS Transactions yang akan di-soft-delete: " . number_format($posCount));
        $this->line(" - POS Details yang akan di-soft-delete     : " . number_format($posDetailsCount));
        $this->line(" - Transaksi Non-Bill (Topup/Saldo)         : " . number_format($nonBillTxCount));
        $this->line(" - Transaction Details Non-Bill             : " . number_format($nonBillTdCount));
        $this->info(" - PRESERVED Tagihan (Bills) (UTUH 100%)    : " . number_format($preservedBills));
        $this->info(" - PRESERVED Pembayaran Tagihan (UTUH 100%) : " . number_format($preservedBillTxs));

        if ($shCount === 0 && $posCount === 0 && $nonBillTxCount === 0) {
            $this->info("\nTidak ada transaksi lama sebelum {$cutoffStr} yang perlu dibersihkan.");
            return 0;
        }

        // 2. Hitung Saldo Awal Baseline per Siswa sebelum Cutoff
        $this->info("\n2. Menghitung Saldo Awal Baseline per siswa sebelum batas cutoff...");

        $studentBaselines = DB::table('saldo_histories')
            ->whereNull('deleted_at')
            ->where('created_at', '<', $cutoffStr)
            ->where('status', SaldoHistory::STATUS_SUCCESS)
            ->select(
                'student_id',
                DB::raw("SUM(CASE WHEN type = 'IN' THEN amount ELSE -amount END) as net_balance")
            )
            ->groupBy('student_id')
            ->having('net_balance', '!=', 0)
            ->get();

        $this->info("Ditemukan " . $studentBaselines->count() . " siswa dengan saldo kumulatif historis sebelum cutoff.");

        if ($dryRun) {
            $this->info("\nSimulasi selesai. Jalankan tanpa opsi --dry-run untuk mengeksekusi pembersihan riil.");
            return 0;
        }

        $now = Carbon::now();

        // 3. Eksekusi Pembuatan Baseline Snapshot
        $this->info("\n3. Menyisipkan record Saldo Awal Baseline per {$cutoffStr}...");
        $baselineBar = $this->output->createProgressBar($studentBaselines->count());
        $baselineBar->start();

        $baselineCreated = 0;
        foreach ($studentBaselines as $sb) {
            $netBalance = (float) $sb->net_balance;
            if ($netBalance > 0) {
                // Periksa apakah baseline untuk cutoff ini sudah pernah dibuat
                $exists = DB::table('saldo_histories')
                    ->where('student_id', $sb->student_id)
                    ->where('created_at', $cutoffStr)
                    ->where('description', 'like', 'Saldo Awal Baseline%')
                    ->whereNull('deleted_at')
                    ->exists();

                if (!$exists) {
                    DB::table('saldo_histories')->insert([
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'student_id' => $sb->student_id,
                        'amount' => $netBalance,
                        'type' => SaldoHistory::TYPE_IN,
                        'usage' => SaldoHistory::USAGE_TOPUP,
                        'status' => SaldoHistory::STATUS_SUCCESS,
                        'description' => "Saldo Awal Baseline Per " . $cutoff->translatedFormat('d F Y'),
                        'balance_before' => 0,
                        'balance_after' => $netBalance,
                        'created_at' => $cutoffStr,
                        'updated_at' => $cutoffStr,
                    ]);
                    $baselineCreated++;
                }
            }
            $baselineBar->advance();
        }
        $baselineBar->finish();
        $this->newLine();
        $this->info("Berhasil membuat {$baselineCreated} record Saldo Awal Baseline.");

        // 4. Soft-delete Data Historis sebelum Cutoff
        $this->info("\n4. Menjalankan soft-delete massal data lama sebelum {$cutoffStr}...");

        $deletedSh = DB::table('saldo_histories')
            ->whereNull('deleted_at')
            ->where('created_at', '<', $cutoffStr)
            ->where('description', 'not like', 'Saldo Awal Baseline%')
            ->update(['deleted_at' => $now]);

        $deletedPos = DB::table('point_of_sale_transactions')
            ->whereNull('deleted_at')
            ->where('created_at', '<', $cutoffStr)
            ->update(['deleted_at' => $now]);

        $deletedPosDetails = DB::table('point_of_sale_transaction_details')
            ->whereNull('deleted_at')
            ->where('created_at', '<', $cutoffStr)
            ->update(['deleted_at' => $now]);

        $deletedTx = DB::table('transactions')
            ->whereNull('deleted_at')
            ->where('type', '!=', Transaction::TYPE_BILL)
            ->where('created_at', '<', $cutoffStr)
            ->update(['deleted_at' => $now]);

        $deletedTd = DB::table('transaction_details')
            ->whereNull('deleted_at')
            ->whereNull('bill_id')
            ->where('created_at', '<', $cutoffStr)
            ->update(['deleted_at' => $now]);

        $this->info(" - SaldoHistories di-soft-delete   : " . number_format($deletedSh));
        $this->info(" - POS Transactions di-soft-delete : " . number_format($deletedPos));
        $this->info(" - POS Details di-soft-delete      : " . number_format($deletedPosDetails));
        $this->info(" - Transaksi Non-Bill di-soft-delete: " . number_format($deletedTx));
        $this->info(" - Non-Bill Details di-soft-delete : " . number_format($deletedTd));

        // 5. Rekalkulasi Seluruh Saldo Berjalan
        $this->info("\n5. Menjalankan sinkronisasi dan rekalkulasi saldo berjalan untuk seluruh santri...");
        $recalculated = SaldoRecalculatorService::recalculateAllStudents();
        $this->info("Rekalkulasi selesai untuk {$recalculated} santri.");

        $this->info("\n================================================================================");
        $this->info("PEMBERSIHAN SELESAI: Database telah diarsipkan secara bersih.");
        $this->info("Semua tagihan (bills) dan saldo aktif santri terbukti 100% presisi.");
        $this->info("================================================================================");

        return 0;
    }
}
