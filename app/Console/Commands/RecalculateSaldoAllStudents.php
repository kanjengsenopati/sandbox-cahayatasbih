<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\SaldoHistory;
use App\Models\Student;
use App\Services\SaldoRecalculatorService;

class RecalculateSaldoAllStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saldo:recalculate-all 
                            {--dry-run : Jalankan simulasi tanpa mengubah database}
                            {--student= : Filter berdasarkan NIS atau Student ID tertentu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rekalkulasi dan pulihkan saldo seluruh santri ke nilai matematis riil berdasarkan riwayat mutasi murni';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ini_set('memory_limit', '1024M');
        $isDryRun = $this->option('dry-run');
        $filterStudent = $this->option('student');

        $this->info("=========================================================================================");
        $this->info("       REKALKULASI & PEMULIHAN SALDO SANTRI KOMPREHENSIF (SALDO RECALCULATOR)            ");
        $this->info("=========================================================================================\n");

        if ($isDryRun) {
            $this->warn(">>> MODE SIMULASI (DRY-RUN) AKTIF: Tidak ada perubahan yang akan disimpan ke database <<<\n");
        }

        $query = Student::with('classroom.school');

        if ($filterStudent) {
            $query->where(function ($q) use ($filterStudent) {
                $q->where('id', $filterStudent)
                  ->orWhere('nis', $filterStudent)
                  ->orWhere('nisn', $filterStudent);
            });
        }

        $totalStudents = $query->count();
        $this->info("Memeriksa {$totalStudents} santri dalam sistem...");

        $discrepancyRows = [];
        $repairedCount = 0;

        if (!$isDryRun) {
            DB::beginTransaction();
        }

        try {
            $students = $query->get();
            $bar = $this->output->createProgressBar($totalStudents);
            $bar->start();

            foreach ($students as $student) {
                $currentSaldo = (float) $student->saldo;

                $histories = SaldoHistory::where('student_id', $student->id)
                    ->where('status', SaldoHistory::STATUS_SUCCESS)
                    ->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc')
                    ->select('id', 'type', 'amount', 'balance_before', 'balance_after')
                    ->get();

                $runningBalance = 0;
                $firstHistory = $histories->first();
                if ($firstHistory && $firstHistory->type !== SaldoHistory::TYPE_IN) {
                    $initialBalance = (float) $firstHistory->balance_before;
                    if ($initialBalance > 0) {
                        $runningBalance = $initialBalance;
                    }
                }

                foreach ($histories as $history) {
                    $amount = (float) $history->amount;
                    if ($history->type === SaldoHistory::TYPE_IN) {
                        $runningBalance += $amount;
                    } else {
                        $runningBalance -= $amount;
                    }
                }

                $projectedSaldo = $runningBalance;
                $diff = $projectedSaldo - $currentSaldo;

                if (abs($diff) > 0.01 || $currentSaldo < 0) {
                    $discrepancyRows[] = [
                        $student->nis ?? '-',
                        $student->name,
                        $student->classroom->name ?? '-',
                        'Rp ' . number_format($currentSaldo, 0, ',', '.'),
                        'Rp ' . number_format($projectedSaldo, 0, ',', '.'),
                        ($diff >= 0 ? '+' : '') . 'Rp ' . number_format($diff, 0, ',', '.'),
                    ];

                    if (!$isDryRun) {
                        SaldoRecalculatorService::recalculateForStudent($student->id);
                        $repairedCount++;
                    }
                }

                $bar->advance();
            }

            $bar->finish();
            $this->line("\n");

            if (!$isDryRun) {
                DB::commit();
            }
        } catch (\Throwable $e) {
            if (!$isDryRun) {
                DB::rollBack();
            }
            $this->error("TERJADI KESALAHAN! Transaksi di-rollback: " . $e->getMessage());
            return 1;
        }

        // Clear cache safely without affecting DB transaction if redis is missing
        if (!$isDryRun) {
            try {
                \Illuminate\Support\Facades\Cache::flush();
            } catch (\Throwable $ignored) {
                // Redis / Cache driver not configured, safe to ignore
            }
        }

        $totalDiscrepancies = count($discrepancyRows);

        if ($totalDiscrepancies === 0) {
            $this->info("✓ SEMUA DATA SALDO SANTRI 100% SINKRON DAN VALID. Tidak ada saldo minus atau anomali.");
            return 0;
        }

        $this->table(
            ['NIS', 'Nama Santri', 'Kelas', 'Saldo Saat Ini (DB)', 'Saldo Riil Rekonstruksi', 'Selisih Pemulihan'],
            $discrepancyRows
        );

        $this->line('');
        if ($isDryRun) {
            $this->warn("Ditemukan {$totalDiscrepancies} santri yang memiliki selisih/saldo minus dan siap dipulihkan.");
            $this->info("Untuk mengeksekusi pemulihan data secara langsung, jalankan perintah tanpa opsi --dry-run:");
            $this->comment("php artisan saldo:recalculate-all");
        } else {
            $this->info("✓ BERHASIL MEMULIHKAN {$repairedCount} SANTRI KE SALDO RIIL ASLINYA 100%!");
        }

        return 0;
    }
}
