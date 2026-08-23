<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\SaldoHistory;
use App\Models\Student;
use App\Services\SaldoRecalculatorService;

class RollbackOverpaymentInjections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saldo:rollback-overpayments {--dry-run : Jalankan simulasi tanpa mengubah data di database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback dan hapus mutasi fiktif Alokasi Kelebihan Bayar serta pulihkan saldo santri terdampak ke nilai riil';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        $this->info("=========================================================================================");
        $this->info("       ROLLBACK & HAPUS INJEKSI OVERPAYMENT TAGIHAN (ALOKASI KELEBIHAN BAYAR)            ");
        $this->info("=========================================================================================\n");

        if ($isDryRun) {
            $this->warn(">>> MODE SIMULASI (DRY-RUN) AKTIF: Tidak ada data yang akan diubah di database <<<\n");
        }

        $overpaymentHistories = SaldoHistory::with(['student.classroom'])
            ->where('description', 'like', '%Alokasi Kelebihan Bayar%')
            ->get();

        $totalRecords = $overpaymentHistories->count();
        $totalNominal = $overpaymentHistories->sum('amount');
        $affectedStudentIds = $overpaymentHistories->pluck('student_id')->unique()->toArray();
        $totalStudents = count($affectedStudentIds);

        if ($totalRecords === 0) {
            $this->info("✓ Tidak ditemukan record 'Alokasi Kelebihan Bayar' di database. Data sudah bersih!");
            return 0;
        }

        $this->info("Ditemukan {$totalRecords} record injeksi pada {$totalStudents} santri.");
        $this->info("Total nominal injeksi yang akan di-rollback: Rp " . number_format($totalNominal, 0, ',', '.') . "\n");

        $rows = [];

        if (!$isDryRun) {
            DB::beginTransaction();
            try {
                // 1. Delete overpayment history records
                $deletedCount = SaldoHistory::where('description', 'like', '%Alokasi Kelebihan Bayar%')->delete();
                $this->info("1. Berhasil menghapus {$deletedCount} record mutasi 'Alokasi Kelebihan Bayar'.");

                // 2. Recalculate each affected student
                $this->info("2. Merekonstruksi running balance dan memulihkan saldo {$totalStudents} santri...");

                foreach ($affectedStudentIds as $sId) {
                    $student = Student::with('classroom')->find($sId);
                    if (!$student) continue;

                    $saldoBefore = (float) $student->saldo;

                    // Recalculate
                    SaldoRecalculatorService::recalculateForStudent($sId);

                    $student->refresh();
                    $saldoAfter = (float) $student->saldo;

                    $rows[] = [
                        $student->nis,
                        $student->name,
                        $student->classroom->name ?? '-',
                        'Rp ' . number_format($saldoBefore, 0, ',', '.'),
                        'Rp ' . number_format($saldoAfter, 0, ',', '.'),
                        '-Rp ' . number_format($saldoBefore - $saldoAfter, 0, ',', '.'),
                    ];
                }

                DB::commit();
                $this->info("3. Transaksi database BERHASIL DI-COMMIT 100%!\n");
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error("TERJADI KESALAHAN! Transaksi di-rollback: " . $e->getMessage());
                return 1;
            }
        } else {
            // Dry Run Preview
            foreach ($affectedStudentIds as $sId) {
                $student = Student::with('classroom')->find($sId);
                if (!$student) continue;

                $studentOverpaymentSum = $overpaymentHistories->where('student_id', $sId)->sum('amount');
                $saldoBefore = (float) $student->saldo;
                $projectedSaldoAfter = $saldoBefore - $studentOverpaymentSum;

                $rows[] = [
                    $student->nis,
                    $student->name,
                    $student->classroom->name ?? '-',
                    'Rp ' . number_format($saldoBefore, 0, ',', '.'),
                    'Rp ' . number_format($projectedSaldoAfter, 0, ',', '.'),
                    '-Rp ' . number_format($studentOverpaymentSum, 0, ',', '.'),
                ];
            }
        }

        $this->table(
            ['NIS', 'Nama Santri', 'Kelas', 'Saldo Sebelum', 'Saldo Sesudah', 'Koreksi Rollback'],
            $rows
        );

        $this->info("\n✓ Pemulihan saldo {$totalStudents} santri selesai dengan sukses!");
        return 0;
    }
}
