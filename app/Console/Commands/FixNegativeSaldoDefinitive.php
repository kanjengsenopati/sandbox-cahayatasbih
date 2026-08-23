<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\SaldoHistory;
use App\Models\Student;

class FixNegativeSaldoDefinitive extends Command
{
    protected $signature = 'saldo:fix-negative 
                            {--dry-run : Jalankan simulasi tanpa mengubah database}';

    protected $description = 'Perbaikan definitif: hapus TARIK PENYESUAIAN fiktif (koreksi ganda), recalculate saldo dari histories bersih';

    public function handle()
    {
        ini_set('memory_limit', '1024M');
        $isDryRun = $this->option('dry-run');

        $this->info("=========================================================================================");
        $this->info("     PERBAIKAN DEFINITIF SALDO NEGATIF (HAPUS TARIK PENYESUAIAN KOREKSI GANDA)           ");
        $this->info("=========================================================================================\n");

        if ($isDryRun) {
            $this->warn(">>> MODE SIMULASI (DRY-RUN) AKTIF: Tidak ada perubahan <<<\n");
        }

        // =====================================================================
        // STEP 1: Find and delete phantom "TARIK PENYESUAI" records
        // These were manually inserted as corrections, but rollback command
        // ALREADY deleted the Alokasi records → double correction → massive negative
        // =====================================================================
        $phantomRecords = DB::table('saldo_histories')
            ->where('type', 'WITHDRAW')
            ->where('status', 'SUCCESS')
            ->where(function ($q) {
                $q->where('description', 'like', '%TARIK PENYESUAI%')
                  ->orWhere('description', 'like', '%TARIK PENYESUAIAN%');
            })
            ->where('amount', '>=', 500000) // Only large amounts (the phantom corrections)
            ->get();

        $this->info("STEP 1: Deteksi record 'TARIK PENYESUAIAN' fiktif (koreksi ganda)");
        $this->info("Ditemukan: {$phantomRecords->count()} record phantom\n");

        if ($phantomRecords->count() > 0) {
            $phantomRows = [];
            foreach ($phantomRecords as $p) {
                $student = DB::table('students')
                    ->leftJoin('classrooms', 'students.classroom_id', '=', 'classrooms.id')
                    ->where('students.id', $p->student_id)
                    ->select('students.nis', 'students.name', 'classrooms.name as classroom')
                    ->first();

                $phantomRows[] = [
                    $student->nis ?? '-',
                    $student->name ?? '-',
                    $student->classroom ?? '-',
                    'Rp ' . number_format($p->amount, 0, ',', '.'),
                    $p->description,
                    $p->created_at,
                ];
            }

            $this->table(
                ['NIS', 'Nama', 'Kelas', 'Nominal Phantom', 'Deskripsi', 'Tanggal'],
                $phantomRows
            );

            if (!$isDryRun) {
                $phantomIds = $phantomRecords->pluck('id')->toArray();
                $deletedCount = DB::table('saldo_histories')
                    ->whereIn('id', $phantomIds)
                    ->delete();
                $this->info("\n  → Berhasil MENGHAPUS {$deletedCount} record phantom TARIK PENYESUAIAN.\n");
            } else {
                $this->warn("\n  → " . $phantomRecords->count() . " record akan dihapus saat eksekusi live.\n");
            }
        }

        // =====================================================================
        // STEP 2: Also delete any remaining Alokasi Kelebihan Bayar records
        // =====================================================================
        $remainingAlokasi = DB::table('saldo_histories')
            ->where('description', 'like', '%Alokasi Kelebihan Bayar%')
            ->count();

        if ($remainingAlokasi > 0) {
            $this->info("STEP 2: Ditemukan {$remainingAlokasi} record Alokasi Kelebihan Bayar tersisa.");
            if (!$isDryRun) {
                $deleted = DB::table('saldo_histories')
                    ->where('description', 'like', '%Alokasi Kelebihan Bayar%')
                    ->delete();
                $this->info("  → Berhasil menghapus {$deleted} record Alokasi.\n");
            } else {
                $this->info("  → Akan dihapus saat eksekusi live.\n");
            }
        } else {
            $this->info("STEP 2: ✓ Tidak ada record Alokasi Kelebihan Bayar tersisa.\n");
        }

        // =====================================================================
        // STEP 3: Recalculate ALL students with saldo < 0
        // =====================================================================
        $negativeStudents = Student::with('classroom')
            ->where('saldo', '<', 0)
            ->orderBy('saldo', 'asc')
            ->get();

        $totalNegative = $negativeStudents->count();
        $this->info("STEP 3: Merecalculate {$totalNegative} santri dengan saldo negatif...\n");

        if ($totalNegative === 0) {
            $this->info("✓ Tidak ada santri dengan saldo negatif. Semua sudah normal!");
            return 0;
        }

        $resultRows = [];
        $repairedCount = 0;

        $bar = $this->output->createProgressBar($totalNegative);
        $bar->start();

        foreach ($negativeStudents as $student) {
            $currentSaldo = (float) $student->saldo;

            // Fetch remaining clean histories (phantom records already deleted)
            $histories = DB::table('saldo_histories')
                ->where('student_id', $student->id)
                ->where('status', 'SUCCESS')
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->select('id', 'type', 'amount', 'balance_before', 'balance_after')
                ->get();

            // Calculate from zero
            $runningBalance = 0.0;
            foreach ($histories as $h) {
                if ($h->type === 'IN') {
                    $runningBalance += (float) $h->amount;
                } else {
                    $runningBalance -= (float) $h->amount;
                }
            }

            $trueSaldo = $runningBalance;
            $diff = $trueSaldo - $currentSaldo;

            $resultRows[] = [
                $student->nis ?? '-',
                $student->name,
                $student->classroom->name ?? '-',
                'Rp ' . number_format($currentSaldo, 0, ',', '.'),
                'Rp ' . number_format($trueSaldo, 0, ',', '.'),
                ($diff >= 0 ? '+' : '') . 'Rp ' . number_format($diff, 0, ',', '.'),
            ];

            if (!$isDryRun && abs($diff) > 0.01) {
                try {
                    DB::transaction(function () use ($student, $histories, $trueSaldo) {
                        // Rebuild running balance in saldo_histories
                        $running = 0.0;
                        foreach ($histories as $h) {
                            $before = $running;
                            if ($h->type === 'IN') {
                                $running += (float) $h->amount;
                            } else {
                                $running -= (float) $h->amount;
                            }
                            $after = $running;

                            if ((float)$h->balance_before !== $before || (float)$h->balance_after !== $after) {
                                DB::table('saldo_histories')->where('id', $h->id)->update([
                                    'balance_before' => $before,
                                    'balance_after' => $after,
                                ]);
                            }
                        }

                        // Force-update students.saldo
                        DB::table('students')->where('id', $student->id)->update([
                            'saldo' => $trueSaldo,
                            'updated_at' => now(),
                        ]);
                    });

                    $repairedCount++;
                } catch (\Throwable $e) {
                    $this->warn("  ⚠ Gagal: {$student->name}: {$e->getMessage()}");
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->line("\n");

        $this->table(
            ['NIS', 'Nama Santri', 'Kelas', 'Saldo Sebelum', 'Saldo Riil Asli', 'Koreksi Pemulihan'],
            $resultRows
        );

        $this->line('');
        if ($isDryRun) {
            $this->warn("Ditemukan {$totalNegative} santri yang siap dinormalisasi.");
            $this->info("Untuk eksekusi, jalankan tanpa --dry-run:");
            $this->comment("php artisan saldo:fix-negative");
        } else {
            $this->info("✓ BERHASIL MEMPERBAIKI {$repairedCount} SANTRI KE SALDO RIIL ASLINYA!");
        }

        return 0;
    }
}
