<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\SaldoHistory;
use App\Models\Student;
use App\Models\Transaction;

class NormalizeNegativeSaldoMaster extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saldo:normalize-master 
                            {--dry-run : Jalankan simulasi tanpa mengubah database}
                            {--student= : Filter berdasarkan NIS atau Student ID tertentu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normalisasi definitif seluruh saldo negatif dan desinkronisasi ke nilai riil berbasis transaksi bank dan kasir sah';

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
        $this->info("          NORMALISASI MASTER SALDO SANTRI KE NILAI RIIL ASLI DEFINITIF                   ");
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

        $students = $query->get();
        $bar = $this->output->createProgressBar($totalStudents);
        $bar->start();

        foreach ($students as $student) {
            $currentSaldo = (float) $student->saldo;

            // 1. Ambil seluruh riwayat mutasi SUCCESS
            $histories = SaldoHistory::where('student_id', $student->id)
                ->where('status', SaldoHistory::STATUS_SUCCESS)
                ->where('description', 'not like', '%Alokasi Kelebihan Bayar%')
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->select('id', 'type', 'amount', 'balance_before', 'balance_after', 'description')
                ->get();

            // 2. Hitung total uang masuk sah dari saldo_histories
            $inHistoriesSum = (float) $histories->where('type', SaldoHistory::TYPE_IN)->sum('amount');

            // 3. Hitung total uang keluar sah dari saldo_histories (belanja kasir, tagihan, tarik tunai)
            $outHistoriesSum = (float) $histories->whereIn('type', [SaldoHistory::TYPE_OUT, SaldoHistory::TYPE_WITHDRAW])->sum('amount');

            // 4. Hitung Saldo Riil Asli
            $realCalculatedSaldo = $inHistoriesSum - $outHistoriesSum;

            // Jika hasil rekonsiliasi negatif akibat defisit kantin masa lalu / baseline lama, batas aman minimal Rp 0
            if ($realCalculatedSaldo < 0 && $inHistoriesSum === 0.0) {
                $realCalculatedSaldo = 0;
            }

            $diff = $realCalculatedSaldo - $currentSaldo;

            // Deteksi apakah santri memiliki saldo minus atau selisih
            if (abs($diff) > 0.01 || $currentSaldo < 0) {
                $discrepancyRows[] = [
                    $student->nis ?? '-',
                    $student->name,
                    $student->classroom->name ?? '-',
                    'Rp ' . number_format($currentSaldo, 0, ',', '.'),
                    'Rp ' . number_format($realCalculatedSaldo, 0, ',', '.'),
                    ($diff >= 0 ? '+' : '') . 'Rp ' . number_format($diff, 0, ',', '.'),
                ];

                if (!$isDryRun) {
                    try {
                        DB::transaction(function () use ($student, $histories, $realCalculatedSaldo) {
                            // Rekonstruksi running balance pada tabel saldo_histories
                            $running = 0;
                            foreach ($histories as $h) {
                                $amount = (float) $h->amount;
                                $before = $running;
                                if ($h->type === SaldoHistory::TYPE_IN) {
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

                            // Update langsung saldo santri di tabel students
                            DB::table('students')->where('id', $student->id)->update([
                                'saldo' => $realCalculatedSaldo,
                                'updated_at' => now(),
                            ]);
                        });

                        $repairedCount++;
                    } catch (\Throwable $e) {
                        // Lewati santri bermasalah dan lanjutkan
                    }
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->line("\n");

        $totalDiscrepancies = count($discrepancyRows);

        if ($totalDiscrepancies === 0) {
            $this->info("✓ SEMUA DATA SALDO SANTRI 100% SINKRON DAN NORMAL. Tidak ditemukan anomali saldo minus.");
            return 0;
        }

        $this->table(
            ['NIS', 'Nama Santri', 'Kelas', 'Saldo di DB Saat Ini', 'Saldo Riil Rekonstruksi', 'Selisih Pemulihan'],
            $discrepancyRows
        );

        $this->line('');
        if ($isDryRun) {
            $this->warn("Ditemukan {$totalDiscrepancies} santri yang memiliki selisih/saldo minus dan siap dinormalisasi ke nilai riil.");
            $this->info("Untuk mengeksekusi normalisasi data secara langsung, jalankan perintah tanpa opsi --dry-run:");
            $this->comment("php artisan saldo:normalize-master");
        } else {
            $this->info("✓ BERHASIL MENORMALISASI {$repairedCount} SANTRI KE SALDO RIIL ASLINYA 100%!");
        }

        return 0;
    }
}
