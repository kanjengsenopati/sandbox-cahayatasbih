<?php

namespace App\Console\Commands;

use App\Models\SaldoHistory;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Command untuk membersihkan SaldoHistory "Kode Unik" yang orphaned.
 *
 * Sebelumnya, proses ini dilakukan secara SYNCHRONOUS di dalam GET request
 * /api/ct-mobile/dashboard (DashboardController::index), yang:
 * 1. Melanggar prinsip HTTP idempotency (GET yang mengubah state)
 * 2. Berpotensi mengurangi saldo santri ganda jika dashboard di-refresh bersamaan
 * 3. Menambah latency pada setiap load dashboard
 *
 * Sekarang dijalankan via scheduler harian: php artisan saldo:cleanup-orphaned-kode-unik
 *
 * "Kode Unik" adalah tambahan nominal kecil yang ditambahkan ke jumlah transfer
 * agar pembayaran dapat diidentifikasi. Jika transaksi dibatalkan/expired,
 * SaldoHistory "kode unik" ini bisa tertinggal tanpa transaction_detail yang valid.
 */
class CleanupOrphanedKodeUnikCommand extends Command
{
    protected $signature = 'saldo:cleanup-orphaned-kode-unik
                            {--dry-run : Tampilkan apa yang akan dibersihkan tanpa melakukan perubahan}
                            {--student-id= : Proses hanya untuk student ID tertentu}';

    protected $description = 'Bersihkan SaldoHistory "Kode Unik" yang orphaned (tidak punya TransactionDetail valid) dan kembalikan saldo santri.';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $studentId = $this->option('student-id');

        if ($isDryRun) {
            $this->warn('=== DRY RUN MODE — Tidak ada perubahan yang dibuat ===');
        }

        $this->info('Memulai cleanup SaldoHistory Kode Unik orphaned...');

        // Query: cari semua SaldoHistory SUCCESS dengan description mengandung "Kode Unik"
        // yang tidak punya transaction_detail yang valid
        $query = SaldoHistory::with(['student', 'transaction_details'])
            ->where('status', SaldoHistory::STATUS_SUCCESS)
            ->whereRaw("LOWER(description) LIKE '%kode unik%'")
            ->whereNull('deleted_at');

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        $orphaned = $query->get()->filter(function ($item) {
            // Orphaned jika tidak punya transaction_details yang valid
            return $item->transaction_details->isEmpty();
        });

        $totalCleaned = 0;
        $totalAmountRestored = 0;

        foreach ($orphaned as $item) {
            $student = $item->student;
            if (!$student) {
                $this->warn("Skip: SaldoHistory {$item->id} tidak punya student.");
                continue;
            }

            $this->line(sprintf(
                '  > Santri: %s | Amount: Rp%s | Desc: %s | Created: %s',
                $student->name,
                number_format($item->amount, 0, ',', '.'),
                $item->description,
                $item->created_at
            ));

            if (!$isDryRun) {
                DB::transaction(function () use ($item, $student) {
                    // Kembalikan saldo santri yang sempat dikurangi untuk kode unik
                    $student->increment('saldo', $item->amount);
                    // Hapus permanent karena ini adalah data korup
                    $item->forceDelete();
                });

                $totalCleaned++;
                $totalAmountRestored += $item->amount;
                Log::info("[CleanupKodeUnik] Cleaned orphaned SaldoHistory ID {$item->id} (Rp{$item->amount}) for student {$student->name} ({$student->id}).");
            } else {
                $totalCleaned++;
                $totalAmountRestored += $item->amount;
            }
        }

        $action = $isDryRun ? 'akan dibersihkan' : 'dibersihkan';
        $this->info("\n✅ Selesai: {$totalCleaned} SaldoHistory Kode Unik {$action}.");
        $this->info("   Total saldo dikembalikan: Rp" . number_format($totalAmountRestored, 0, ',', '.'));

        return Command::SUCCESS;
    }
}
