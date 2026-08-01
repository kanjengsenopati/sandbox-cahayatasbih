<?php

namespace App\Jobs;

use App\Models\Student;
use App\Services\TransactionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job untuk mensinkronisasi data tagihan santri di background.
 *
 * Sebelumnya, 3 operasi berat ini dijalankan secara SYNCHRONOUS di setiap
 * request HTTP (di BillController::index dan Wali/BillController::index),
 * menyebabkan response time 10-30 detik.
 *
 * Sekarang dipindahkan ke queue agar request HTTP dapat selesai segera,
 * dan sinkronisasi berjalan di background worker.
 *
 * Operasi yang dilakukan (berurutan):
 * 1. cleanupGhostBillsForStudent  - Hapus bill lintas UPT / sebelum tahun masuk
 * 2. syncStudentBillsFromPaidTransactions - Pastikan bill yg sudah dibayar tercatat
 * 3. ensureStudentBillsSyncedFromRate - Generate bill yang belum ada dari PaymentRate
 */
class SyncStudentBillsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan maksimal jika job gagal.
     */
    public int $tries = 3;

    /**
     * Timeout job dalam detik (90 detik untuk sinkronisasi lengkap).
     */
    public int $timeout = 90;

    public function __construct(
        public readonly string $studentId,
        public readonly ?string $academicYearId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (empty($this->studentId)) {
            return;
        }

        $student = Student::find($this->studentId);
        if (!$student) {
            Log::warning("[SyncStudentBillsJob] Student not found: {$this->studentId}");
            return;
        }

        Log::info("[SyncStudentBillsJob] Starting sync for student: {$student->name} ({$this->studentId})");

        try {
            // 1. Bersihkan ghost bills (cross-UPT, sebelum tahun masuk)
            TransactionService::cleanupGhostBillsForStudent($this->studentId);

            // 2. Sinkronisasi bill dari transaksi yang sudah dibayar
            TransactionService::syncStudentBillsFromPaidTransactions($this->studentId);

            // 3. Generate bill yang belum ada berdasarkan PaymentRate
            TransactionService::ensureStudentBillsSyncedFromRate($this->studentId, $this->academicYearId);

            Log::info("[SyncStudentBillsJob] Sync completed for student: {$this->studentId}");
        } catch (\Throwable $e) {
            Log::error("[SyncStudentBillsJob] Error syncing student {$this->studentId}: " . $e->getMessage());
            throw $e; // Re-throw agar queue bisa retry
        }
    }

    /**
     * Handle job failure setelah semua percobaan habis.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("[SyncStudentBillsJob] Job permanently failed for student {$this->studentId}: " . $exception->getMessage());
    }
}
