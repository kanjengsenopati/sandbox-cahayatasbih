<?php

namespace App\Console\Commands;

use App\Models\SaldoHistory;
use App\Models\SaldoMigrationBatch;
use App\Models\SaldoMigrationItem;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApplyPendingSaldoMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migration:apply-all-pending 
                            {--batch= : ID Batch spesifik yang ingin diterapkan} 
                            {--force-reapply : Terapkan ulang meskipun batch bukan berstatus PENDING}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis terima dan terapkan (apply) seluruh paket migrasi saldo yang berstatus PENDING ke saldo santri di Aplikasi Baru.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("================================================================================");
        $this->info("   PENERAPAN SALDO MIGRASI KE DATABASE APLIKASI BARU");
        $this->info("================================================================================");

        $specificBatchId = $this->option('batch');
        $forceReapply = $this->option('force-reapply');

        $query = SaldoMigrationBatch::query();
        if ($specificBatchId) {
            $query->where('id', $specificBatchId);
        } elseif (!$forceReapply) {
            $query->where('status', 'PENDING');
        }

        $batches = $query->latest()->get();

        if ($batches->isEmpty()) {
            $this->warn("\nTidak ada batch migrasi berstatus PENDING yang ditemukan.");

            // Tampilkan daftar batch terakhir untuk pengecekan
            $allBatches = SaldoMigrationBatch::latest()->take(10)->get();
            if ($allBatches->isNotEmpty()) {
                $this->info("\nDaftar 10 Paket Migrasi Terakhir di Database:");
                $table = $allBatches->map(function ($b) {
                    return [
                        'Batch ID' => substr($b->id, 0, 8) . '...',
                        'Judul / Kelas' => $b->notes ?: '-',
                        'Santri' => $b->total_students,
                        'Total Saldo' => 'Rp ' . number_format($b->total_saldo, 0, ',', '.'),
                        'Status' => $b->status,
                        'Waktu Kirim' => $b->sent_at,
                    ];
                });
                $this->table(['Batch ID', 'Judul / Kelas', 'Santri', 'Total Saldo', 'Status', 'Waktu Kirim'], $table);
            }
            return 0;
        }

        $this->info("\nDitemukan " . $batches->count() . " paket migrasi yang akan diterapkan:");
        foreach ($batches as $batch) {
            $this->line(" - Batch [{$batch->id}]: {$batch->notes} ({$batch->total_students} santri, Rp " . number_format($batch->total_saldo, 0, ',', '.') . ") [Status: {$batch->status}]");
        }

        $now = now();
        $adminName = 'System CLI / Auto Apply';

        $totalAppliedStudents = 0;
        $totalAppliedSaldo = 0;

        foreach ($batches as $batch) {
            $this->newLine();
            $this->info("Sedang menerapkan Batch: {$batch->notes} (ID: {$batch->id})...");

            DB::transaction(function () use ($batch, $now, $adminName, &$totalAppliedStudents, &$totalAppliedSaldo) {
                $items = $batch->items()->get();

                foreach ($items as $item) {
                    $student = null;
                    if ($item->student_id) {
                        $student = Student::find($item->student_id);
                    }
                    if (!$student && $item->nis) {
                        $student = Student::where('nis', $item->nis)->first();
                    }
                    if (!$student && $item->name) {
                        $student = Student::where('name', $item->name)->first();
                    }

                    if ($student) {
                        $oldBalance = (int) $student->saldo;
                        $newBalance = (int) $item->old_saldo;

                        // Update saldo dan saving santri
                        $student->update([
                            'saldo' => $newBalance,
                            'saving' => (int) $item->old_saving,
                            'updated_at' => $now,
                        ]);

                        // Catat riwayat audit di saldo_histories
                        SaldoHistory::create([
                            'id' => (string) Str::uuid(),
                            'student_id' => $student->id,
                            'type' => 'IN',
                            'usage' => 'MIGRATION',
                            'amount' => abs($newBalance - $oldBalance) ?: $newBalance,
                            'description' => "Migrasi Saldo Awal dari Aplikasi Lama (Batch Ref: {$batch->id})",
                            'balance_before' => $oldBalance,
                            'balance_after' => $newBalance,
                            'status' => 'SUCCESS',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);

                        $item->update(['status' => 'APPLIED']);
                        $totalAppliedStudents++;
                        $totalAppliedSaldo += $newBalance;
                    }
                }

                $batch->update([
                    'status' => 'APPLIED',
                    'applied_at' => $now,
                    'applied_by' => $adminName,
                    'notes' => "Diterima dan diterapkan otomatis oleh {$adminName} pada {$now->toDateTimeString()}",
                ]);
            });

            $this->info("✓ Batch {$batch->id} BERHASIL DITERAPKAN!");
        }

        $this->newLine();
        $this->info("================================================================================");
        $this->info("✓ SELESAI! Sebanyak {$totalAppliedStudents} data santri telah berhasil disinkronkan saldonya!");
        $this->info("================================================================================");

        return 0;
    }
}
