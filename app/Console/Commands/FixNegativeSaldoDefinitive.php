<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\SaldoHistory;
use App\Models\Student;

class FixNegativeSaldoDefinitive extends Command
{
    protected $signature = 'saldo:fix-negative 
                            {--dry-run : Jalankan simulasi tanpa mengubah database}
                            {--student= : Filter berdasarkan NIS atau Student ID tertentu}';

    protected $description = 'Perbaikan definitif saldo negatif: hapus record Alokasi Kelebihan Bayar (jika masih ada), recalculate dari histories murni, dan force-update students.saldo';

    public function handle()
    {
        ini_set('memory_limit', '1024M');
        $isDryRun = $this->option('dry-run');
        $filterStudent = $this->option('student');

        $this->info("=========================================================================================");
        $this->info("     PERBAIKAN DEFINITIF SALDO NEGATIF SANTRI (FORCE RECALCULATE FROM HISTORIES)         ");
        $this->info("=========================================================================================\n");

        if ($isDryRun) {
            $this->warn(">>> MODE SIMULASI (DRY-RUN) AKTIF: Tidak ada perubahan <<<\n");
        }

        // STEP 0: Delete ALL remaining Alokasi Kelebihan Bayar records (if any still exist)
        $remainingAlokasi = DB::table('saldo_histories')
            ->where('description', 'like', '%Alokasi Kelebihan Bayar%')
            ->count();
        
        if ($remainingAlokasi > 0) {
            $this->warn("Ditemukan {$remainingAlokasi} record 'Alokasi Kelebihan Bayar' yang masih tersisa di database.");
            if (!$isDryRun) {
                $deleted = DB::table('saldo_histories')
                    ->where('description', 'like', '%Alokasi Kelebihan Bayar%')
                    ->delete();
                $this->info("  → Berhasil menghapus {$deleted} record Alokasi Kelebihan Bayar.\n");
            } else {
                $this->info("  → Akan dihapus saat eksekusi live.\n");
            }
        } else {
            $this->info("✓ Tidak ada record Alokasi Kelebihan Bayar tersisa (sudah bersih).\n");
        }

        // STEP 1: Find target students
        $query = Student::with('classroom');

        if ($filterStudent) {
            $query->where(function ($q) use ($filterStudent) {
                $q->where('id', $filterStudent)
                  ->orWhere('nis', $filterStudent)
                  ->orWhere('nisn', $filterStudent);
            });
        } else {
            $query->where('saldo', '<', 0);
        }

        $students = $query->get();
        $totalStudents = $students->count();

        if ($totalStudents === 0) {
            $this->info("✓ Tidak ditemukan santri dengan saldo negatif. Semua data sudah normal!");
            return 0;
        }

        $this->info("Ditemukan {$totalStudents} santri dengan saldo negatif. Memulai perbaikan...\n");

        $discrepancyRows = [];
        $repairedCount = 0;

        $bar = $this->output->createProgressBar($totalStudents);
        $bar->start();

        foreach ($students as $student) {
            $currentSaldo = (float) $student->saldo;

            // Fetch ALL SUCCESS saldo_histories (Alokasi already deleted above)
            $histories = DB::table('saldo_histories')
                ->where('student_id', $student->id)
                ->where('status', 'SUCCESS')
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->select('id', 'type', 'amount', 'balance_before', 'balance_after')
                ->get();

            // Calculate running balance from absolute zero
            $runningBalance = 0.0;

            foreach ($histories as $history) {
                $amount = (float) $history->amount;
                if ($history->type === 'IN') {
                    $runningBalance += $amount;
                } else {
                    $runningBalance -= $amount;
                }
            }

            $trueSaldo = $runningBalance;

            $diff = $trueSaldo - $currentSaldo;

            $discrepancyRows[] = [
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
                        // Reconstruct running balance in saldo_histories
                        $running = 0.0;
                        foreach ($histories as $h) {
                            $amount = (float) $h->amount;
                            $before = $running;
                            if ($h->type === 'IN') {
                                $running += $amount;
                            } else {
                                $running -= $amount;
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
            ['NIS', 'Nama Santri', 'Kelas', 'Saldo DB Saat Ini', 'Saldo Riil Asli', 'Koreksi Pemulihan'],
            $discrepancyRows
        );

        $this->line('');
        if ($isDryRun) {
            $this->warn("Ditemukan {$totalStudents} santri dengan saldo negatif yang siap diperbaiki.");
            $this->info("Untuk eksekusi perbaikan, jalankan tanpa --dry-run:");
            $this->comment("php artisan saldo:fix-negative");
        } else {
            $this->info("✓ BERHASIL MEMPERBAIKI {$repairedCount} dari {$totalStudents} SANTRI KE SALDO RIIL ASLINYA!");
        }

        return 0;
    }
}
