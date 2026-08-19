<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\SaldoRecalculatorService;

class SyncDisplacedSaldoTimestamps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saldo:sync-displaced-timestamps {--dry-run : Only simulate changes without writing to database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore correct historical created_at timestamps for SaldoHistory records displaced on 2026-08-04 and recalculate running balances.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info("================================================================================");
        $this->info("RESTITUSI TIMESTAMP SALDO HISTORIS (RESOLUSI LONJAKAN 04 AGUSTUS 2026)");
        $this->info("================================================================================");

        if ($dryRun) {
            $this->warn("MODUS DRY-RUN DIAKTIFKAN: Tidak ada perubahan yang akan disimpan ke database.");
        }

        // 1. Identifikasi data yang displaced
        $this->info("1. Memindai data saldo_histories yang tergeser pada 2026-08-04...");

        $displacedRecords = DB::table('saldo_histories as sh')
            ->join('transaction_details as td', 'td.saldo_history_id', '=', 'sh.id')
            ->join('transactions as tx', 'tx.id', '=', 'td.transaction_id')
            ->whereDate('sh.created_at', '2026-08-04')
            ->where('sh.type', 'IN')
            ->whereRaw("DATE(sh.created_at) != DATE(tx.created_at)")
            ->select(
                'sh.id as saldo_history_id',
                'sh.student_id',
                'sh.amount',
                'sh.created_at as current_sh_created_at',
                'td.id as transaction_detail_id',
                'tx.id as transaction_id',
                'tx.created_at as real_tx_created_at'
            )
            ->get();

        $totalRecords = $displacedRecords->count();
        $totalNominal = $displacedRecords->sum('amount');
        $uniqueStudents = $displacedRecords->pluck('student_id')->unique();

        $this->info("Ditemukan {$totalRecords} record tergeser pada {$uniqueStudents->count()} siswa (Total: Rp " . number_format($totalNominal, 0, ',', '.') . ").");

        if ($totalRecords === 0) {
            $this->info("Tidak ada data tergeser yang perlu disinkronkan. Semua timestamp sudah sesuai.");
            return 0;
        }

        if ($dryRun) {
            $this->info("Simulasi selesai. Jalankan tanpa opsi --dry-run untuk mengeksekusi perbaikan database.");
            return 0;
        }

        // 2. Eksekusi perbaikan timestamp secara batch
        $this->info("\n2. Memperbarui timestamp created_at dan updated_at sesuai transaksi induk aslinya...");
        $bar = $this->output->createProgressBar($totalRecords);
        $bar->start();

        $updatedCount = 0;

        foreach ($displacedRecords->chunk(500) as $chunk) {
            DB::transaction(function () use ($chunk, &$updatedCount, $bar) {
                foreach ($chunk as $item) {
                    // Update SaldoHistory
                    DB::table('saldo_histories')
                        ->where('id', $item->saldo_history_id)
                        ->update([
                            'created_at' => $item->real_tx_created_at,
                            'updated_at' => $item->real_tx_created_at
                        ]);

                    // Update TransactionDetail
                    if ($item->transaction_detail_id) {
                        DB::table('transaction_details')
                            ->where('id', $item->transaction_detail_id)
                            ->update([
                                'created_at' => $item->real_tx_created_at,
                                'updated_at' => $item->real_tx_created_at
                            ]);
                    }

                    $updatedCount++;
                    $bar->advance();
                }
            });
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Berhasil memperbarui {$updatedCount} timestamp transaksi.");

        // 3. Rekalkulasi running balance untuk seluruh siswa terdampak
        $this->info("\n3. Menjalankan rekalkulasi saldo berjalan (running balance) kronologis untuk {$uniqueStudents->count()} siswa...");
        $recalcBar = $this->output->createProgressBar($uniqueStudents->count());
        $recalcBar->start();

        foreach ($uniqueStudents as $studentId) {
            SaldoRecalculatorService::recalculateForStudent($studentId);
            $recalcBar->advance();
        }

        $recalcBar->finish();
        $this->newLine(2);

        $this->info("================================================================================");
        $this->info("SUKSES: Seluruh transaksi telah dikembalikan ke tanggal aslinya.");
        $this->info("Lonjakan anomali 4 Agustus 2026 telah dinormalisasi secara presisi.");
        $this->info("================================================================================");

        return 0;
    }
}
