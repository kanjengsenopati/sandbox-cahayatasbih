<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\GoogleSheetsBackupService;

class BackupFinancialReportToGoogleSheetsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Timeout job dalam detik (10 menit)
     */
    public $timeout = 600;

    /**
     * Jumlah percobaan jika gagal
     */
    public $tries = 3;

    /**
     * Tab spesifik yang akan dibackup (opsional)
     */
    protected ?string $specificTab;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $specificTab = null)
    {
        $this->specificTab = $specificTab;
        $this->afterCommit = true;
    }

    /**
     * Execute the job.
     */
    public function handle(GoogleSheetsBackupService $service): void
    {
        Log::channel('single')->info('[BackupFinancialReportToGoogleSheetsJob] Memulai eksekusi job auto backup...', [
            'tab' => $this->specificTab,
            'attempt' => $this->attempts(),
        ]);

        $result = $service->runBackup($this->specificTab);

        if (!$result['success']) {
            Log::channel('single')->warning('[BackupFinancialReportToGoogleSheetsJob] Selesai dengan error pada sebagian tab.', [
                'errors' => $result['errors'] ?? [],
            ]);
        } else {
            Log::channel('single')->info('[BackupFinancialReportToGoogleSheetsJob] Berhasil menyelesaikan auto backup Google Sheets.', [
                'total_rows' => $result['total_rows_synced'] ?? 0,
                'duration' => $result['duration_seconds'] ?? 0,
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('single')->error('[BackupFinancialReportToGoogleSheetsJob] Job gagal total setelah ' . $this->tries . ' percobaan: ' . $exception->getMessage(), [
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
