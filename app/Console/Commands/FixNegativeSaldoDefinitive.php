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
                            {--zero-deficits : Nol-kan defisit/utang kecil masa lalu (< 0 menjadi Rp 0)}
                            {--student= : Filter berdasarkan NIS, Nama, atau ID tertentu}';

    protected $description = 'Perbaikan definitif saldo negatif: hapus seluruh record koreksi ganda/phantom, recalculate saldo dari riwayat sah, force-update students.saldo';

    public function handle()
    {
        ini_set('memory_limit', '1024M');
        $isDryRun = $this->option('dry-run');
        $zeroDeficits = $this->option('zero-deficits');
        $filterStudent = $this->option('student');

        $this->info("=========================================================================================");
        $this->info("     PERBAIKAN DEFINITIF SALDO NEGATIF SANTRI (PEMBERSIHAN TOTAL PHANTOM & RECALCULATE)  ");
        $this->info("=========================================================================================\n");

        if ($isDryRun) {
            $this->warn(">>> MODE SIMULASI (DRY-RUN) AKTIF: Tidak ada data yang diubah di database <<<\n");
        }

        // =====================================================================
        // STEP 1: DETEKSI & HAPUS SELURUH MUTASI PENGURANGAN PHANTOM / KOREKSI GANDA
        // Meliputi: TARIK PENYESUAIAN, TARIK MANUAL, KOREKSI SALDO, atau OUT/WITHDRAW >= 500k anomali
        // =====================================================================
        $phantomRecords = DB::table('saldo_histories')
            ->whereIn('type', ['OUT', 'WITHDRAW'])
            ->where('status', 'SUCCESS')
            ->where(function ($q) {
                $q->where('description', 'like', '%PENYESUAI%')
                  ->orWhere('description', 'like', '%KOREKSI%')
                  ->orWhere('description', 'like', '%ROLLBACK%')
                  ->orWhere('description', 'like', '%KELEBIHAN%')
                  ->orWhere('description', 'like', '%ALOKASI%')
                  ->orWhere(function ($sub) {
                      $sub->where('description', 'like', '%TARIK%')
                          ->where('amount', '>=', 500000);
                  });
            })
            ->get();

        $this->info("STEP 1: Deteksi record mutasi pengurangan phantom / koreksi ganda...");
        $this->info("Ditemukan: {$phantomRecords->count()} record phantom.\n");

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
                    $p->type,
                    'Rp ' . number_format($p->amount, 0, ',', '.'),
                    $p->description,
                    $p->created_at,
                ];
            }

            $this->table(
                ['NIS', 'Nama', 'Kelas', 'Tipe', 'Nominal Phantom', 'Deskripsi', 'Tanggal'],
                $phantomRows
            );

            if (!$isDryRun) {
                $phantomIds = $phantomRecords->pluck('id')->toArray();
                $deletedCount = DB::table('saldo_histories')
                    ->whereIn('id', $phantomIds)
                    ->delete();
                $this->info("\n  → Berhasil MENGHAPUS {$deletedCount} record mutasi phantom.\n");
            } else {
                $this->warn("\n  → " . $phantomRecords->count() . " record akan dihapus saat eksekusi live.\n");
            }
        }

        // =====================================================================
        // STEP 2: HAPUS JUGA RECORD ALOKASI KELEBIHAN BAYAR (TYPE IN) JIKA ADA
        // =====================================================================
        $remainingAlokasi = DB::table('saldo_histories')
            ->where('description', 'like', '%Alokasi Kelebihan Bayar%')
            ->count();

        if ($remainingAlokasi > 0) {
            $this->info("STEP 2: Ditemukan {$remainingAlokasi} record 'Alokasi Kelebihan Bayar' (IN).");
            if (!$isDryRun) {
                $deleted = DB::table('saldo_histories')
                    ->where('description', 'like', '%Alokasi Kelebihan Bayar%')
                    ->delete();
                $this->info("  → Berhasil menghapus {$deleted} record Alokasi.\n");
            } else {
                $this->info("  → Akan dihapus saat eksekusi live.\n");
            }
        } else {
            $this->info("STEP 2: ✓ Tidak ada record Alokasi Kelebihan Bayar (IN) tersisa.\n");
        }

        // =====================================================================
        // STEP 3: REKALKULASI & SINKRONISASI SALDO SELURUH SANTRI NEGATIF
        // =====================================================================
        $query = Student::with('classroom');

        if ($filterStudent) {
            $query->where(function ($q) use ($filterStudent) {
                $q->where('id', $filterStudent)
                  ->orWhere('nis', $filterStudent)
                  ->orWhere('nisn', $filterStudent)
                  ->orWhere('name', 'like', "%{$filterStudent}%");
            });
        } else {
            $query->where('saldo', '<', 0);
        }

        $negativeStudents = $query->orderBy('saldo', 'asc')->get();
        $totalTarget = $negativeStudents->count();

        $this->info("STEP 3: Memeriksa dan merekonstruksi {$totalTarget} santri target...\n");

        if ($totalTarget === 0) {
            $this->info("✓ Tidak ada santri dengan saldo negatif yang perlu diperbaiki.");
            return 0;
        }

        $resultRows = [];
        $repairedCount = 0;

        $bar = $this->output->createProgressBar($totalTarget);
        $bar->start();

        foreach ($negativeStudents as $student) {
            $currentSaldo = (float) $student->saldo;

            // Ambil seluruh riwayat mutasi sah yang tersisa
            $histories = DB::table('saldo_histories')
                ->where('student_id', $student->id)
                ->where('status', 'SUCCESS')
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->select('id', 'type', 'amount', 'balance_before', 'balance_after')
                ->get();

            // Hitung running balance murni dari nol
            $runningBalance = 0.0;
            foreach ($histories as $h) {
                if ($h->type === 'IN') {
                    $runningBalance += (float) $h->amount;
                } else {
                    $runningBalance -= (float) $h->amount;
                }
            }

            $trueSaldo = $runningBalance;

            // Jika opsi --zero-deficits aktif atau saldo minus karena defisit masa lalu
            if ($zeroDeficits && $trueSaldo < 0) {
                $trueSaldo = 0.0;
            }

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
            $this->warn("Ditemukan {$totalTarget} santri yang siap diproses.");
            $this->info("Untuk mengeksekusi langsung di database, jalankan tanpa opsi --dry-run:");
            $this->comment("php artisan saldo:fix-negative");
        } else {
            $this->info("✓ BERHASIL MEMPERBAIKI {$repairedCount} dari {$totalTarget} SANTRI KE SALDO RIIL ASLINYA 100%!");
        }

        return 0;
    }
}
