<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SaldoHistory;
use App\Models\Student;
use App\Services\SaldoRecalculatorService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ForceAdjustStudentBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saldo:force-adjust 
                            {--nis= : NIS siswa yang akan disesuaikan}
                            {--target-saldo= : Target saldo akhir yang diinginkan}
                            {--baseline= : Nilai Saldo Awal Baseline per 1 Jan 2026 yang ingin diatur}
                            {--cap-all-baselines= : Batasi semua baseline santri yang melebihi batas nominal ini (misal: 500000)}
                            {--reason=Koreksi Saldo Riil Santri : Keterangan penyesuaian}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Melakukan Hard Force Update dan penyesuaian saldo riil/baseline santri secara presisi & terdokumentasi';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $nis = $this->option('nis');
        $targetSaldo = $this->option('target-saldo');
        $baseline = $this->option('baseline');
        $capAllBaselines = $this->option('cap-all-baselines');
        $reason = $this->option('reason') ?? 'Koreksi Saldo Riil Santri';

        $this->info("================================================================================");
        $this->info("HARD FORCE UPDATE & ADJUSTMENT SALDO SANTRI");
        $this->info("================================================================================");

        // Scenario 1: Cap all baselines exceeding threshold
        if ($capAllBaselines !== null) {
            $capAmount = (float)$capAllBaselines;
            $this->info("Memindai record Baseline Saldo Awal (> Rp " . number_format($capAmount, 0, ',', '.') . ")...");

            $baselines = SaldoHistory::where('description', 'like', '%Saldo Awal Baseline%')
                ->where('amount', '>', $capAmount)
                ->get();

            $this->info("Ditemukan {$baselines->count()} santri dengan baseline melebihi batas Rp " . number_format($capAmount, 0, ',', '.'));

            if ($baselines->count() === 0) {
                $this->info("Tidak ada baseline yang perlu dibatasi.");
                return 0;
            }

            $affectedStudentIds = [];
            foreach ($baselines as $b) {
                $oldAmount = $b->amount;
                $b->amount = $capAmount;
                $b->description = "Saldo Awal Baseline Per 01 Januari 2026 (Capped: Rp " . number_format($capAmount, 0, ',', '.') . ")";
                $b->save();

                $affectedStudentIds[$b->student_id] = true;
                $this->line("  - Siswa {$b->student_id}: Baseline Rp " . number_format($oldAmount, 0, ',', '.') . " -> Rp " . number_format($capAmount, 0, ',', '.'));
            }

            $this->info("\nMenjalankan rekalkulasi saldo untuk " . count($affectedStudentIds) . " siswa...");
            $bar = $this->output->createProgressBar(count($affectedStudentIds));
            $bar->start();

            foreach (array_keys($affectedStudentIds) as $sId) {
                SaldoRecalculatorService::recalculateForStudent($sId);
                $bar->advance();
            }
            $bar->finish();

            $this->info("\n\nSelesai membatasi baseline santri.");
            return 0;
        }

        // Scenario 2: Adjust specific student by NIS
        if (!$nis) {
            $this->error("Parameter --nis atau --cap-all-baselines wajib diisi.");
            return 1;
        }

        $student = Student::where('nis', $nis)->first();
        if (!$student) {
            $this->error("Siswa dengan NIS {$nis} tidak ditemukan.");
            return 1;
        }

        $this->info("Siswa: {$student->nis} - {$student->name} (Kelas: " . ($student->studentClass->name ?? '-') . ")");
        $this->info("Saldo Saat Ini di Database: Rp " . number_format($student->saldo, 0, ',', '.'));

        // If adjusting baseline directly
        if ($baseline !== null) {
            $baselineAmount = (float)$baseline;
            $baselineHistory = SaldoHistory::where('student_id', $student->id)
                ->where('description', 'like', '%Saldo Awal Baseline%')
                ->first();

            if ($baselineHistory) {
                $oldBaseline = $baselineHistory->amount;
                $baselineHistory->amount = $baselineAmount;
                $baselineHistory->description = "Saldo Awal Baseline Per 01 Januari 2026 (Adjusted: Rp " . number_format($baselineAmount, 0, ',', '.') . ")";
                $baselineHistory->save();
                $this->info("Baseline berhasil diupdate dari Rp " . number_format($oldBaseline, 0, ',', '.') . " -> Rp " . number_format($baselineAmount, 0, ',', '.'));
            } else {
                // Create baseline if not exists
                SaldoHistory::create([
                    'student_id' => $student->id,
                    'amount' => $baselineAmount,
                    'type' => SaldoHistory::TYPE_IN,
                    'status' => SaldoHistory::STATUS_SUCCESS,
                    'usage' => SaldoHistory::USAGE_TOPUP,
                    'description' => "Saldo Awal Baseline Per 01 Januari 2026",
                    'balance_before' => 0,
                    'balance_after' => $baselineAmount,
                    'created_at' => Carbon::parse('2026-01-01 00:00:00'),
                    'updated_at' => Carbon::parse('2026-01-01 00:00:00'),
                ]);
                $this->info("Baseline berhasil dibuat: Rp " . number_format($baselineAmount, 0, ',', '.'));
            }

            // Recalculate
            SaldoRecalculatorService::recalculateForStudent($student->id);
            $student->refresh();
            $this->info("Saldo Akhir Siswa Pasca Update Baseline: Rp " . number_format($student->saldo, 0, ',', '.'));
        }

        // If adjusting target saldo directly
        if ($targetSaldo !== null) {
            $target = (float)$targetSaldo;
            // First ensure student history is freshly calculated
            SaldoRecalculatorService::recalculateForStudent($student->id);
            $student->refresh();

            $currentSaldo = (float)$student->saldo;
            $diff = $target - $currentSaldo;

            if (abs($diff) < 0.01) {
                $this->info("Saldo siswa sudah tepat Rp " . number_format($target, 0, ',', '.') . ", tidak diperlukan adjustment.");
            } else {
                $now = Carbon::now();
                if ($diff > 0) {
                    // Need to add IN adjustment
                    SaldoHistory::create([
                        'student_id' => $student->id,
                        'amount' => $diff,
                        'type' => SaldoHistory::TYPE_IN,
                        'status' => SaldoHistory::STATUS_SUCCESS,
                        'usage' => SaldoHistory::USAGE_TOPUP,
                        'description' => "{$reason} (+Rp " . number_format($diff, 0, ',', '.') . ")",
                        'balance_before' => $currentSaldo,
                        'balance_after' => $target,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                } else {
                    // Need to add OUT adjustment
                    $absDiff = abs($diff);
                    SaldoHistory::create([
                        'student_id' => $student->id,
                        'amount' => $absDiff,
                        'type' => SaldoHistory::TYPE_OUT,
                        'status' => SaldoHistory::STATUS_SUCCESS,
                        'usage' => SaldoHistory::USAGE_PAYMENT,
                        'description' => "{$reason} (-Rp " . number_format($absDiff, 0, ',', '.') . ")",
                        'balance_before' => $currentSaldo,
                        'balance_after' => $target,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                // Final recalculate
                SaldoRecalculatorService::recalculateForStudent($student->id);
                $student->refresh();
                $this->info("Berhasil mengoreksi saldo siswa ke: Rp " . number_format($student->saldo, 0, ',', '.'));
            }
        }

        $this->info("\n================================================================================");
        $this->info("SELESAI: Data saldo siswa {$student->nis} ({$student->name}) telah presisi 100%.");
        $this->info("================================================================================");

        return 0;
    }
}
