<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleSheetsBackupService;
use App\Jobs\BackupFinancialReportToGoogleSheetsJob;

class BackupFinancialReportToGoogleSheetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backup:google-sheets
                            {--sync : Eksekusi langsung secara synchronous tanpa background queue}
                            {--tab= : Filter hanya untuk sheet tab tertentu (misal: "SYAHRIAH SMP")}';

    /**
     * The console command description.
     */
    protected $description = 'Auto-backup laporan keuangan dan tagihan santri ke Google Sheets (dijadwalkan tanggal 1 & 16 per bulan)';

    /**
     * Execute the console command.
     */
    public function handle(GoogleSheetsBackupService $service): int
    {
        $isSync = $this->option('sync');
        $tab = $this->option('tab');

        $this->info("==================================================");
        $this->info(" Ponpes Cahaya Tasbih - Google Sheets Auto-Backup ");
        $this->info("==================================================");

        if ($tab) {
            $this->comment("Target Tab : {$tab}");
        } else {
            $this->comment("Target Tab : Seluruh 12 Sheet Tabs");
        }

        if ($isSync) {
            $this->warn("Mode       : Synchronous (Langsung di console)");
            $this->line("Memproses penyusunan data dan pengiriman ke Google Apps Script...");

            $result = $service->runBackup($tab);

            if ($result['success']) {
                $this->info("✓ Selesai dengan sukses dalam {$result['duration_seconds']} detik.");
                $this->line("Total baris tersinkron: {$result['total_rows_synced']}");

                $tableRows = [];
                foreach ($result['results'] as $tName => $info) {
                    $tableRows[] = [
                        $tName,
                        $info['status'],
                        $info['rows'] ?? ($info['reason'] ?? '-'),
                    ];
                }
                $this->table(['Sheet Tab', 'Status', 'Detail'], $tableRows);
                return Command::SUCCESS;
            } else {
                $this->error("✗ Terjadi kesalahan saat proses backup:");
                if (!empty($result['message'])) {
                    $this->error("  " . $result['message']);
                }
                foreach ($result['errors'] as $tName => $err) {
                    $this->error("  - [{$tName}]: {$err}");
                }
                return Command::FAILURE;
            }
        }

        $this->info("Mode       : Asynchronous (Dispatched ke Background Queue)");
        BackupFinancialReportToGoogleSheetsJob::dispatch($tab);
        $this->info("✓ Job berhasil dimasukkan ke antrean queue default.");
        $this->line("  Proses akan dieksekusi oleh native queue worker di latar belakang.");
        $this->line("  Pantau log di storage/logs/google_sheets_backup.log.");

        return Command::SUCCESS;
    }
}
