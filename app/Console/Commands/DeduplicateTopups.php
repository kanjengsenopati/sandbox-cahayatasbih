<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SaldoHistory;
use App\Models\Student;
use App\Services\SaldoRecalculatorService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeduplicateTopups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saldo:deduplicate-topups {--dry-run : Only preview duplicates without deleting} {--student_id= : Target specific student ID} {--nis= : Target specific student NIS}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mendeteksi dan membersihkan record SaldoHistory IN (Topup) yang terduplikasi secara ganda dalam selang waktu berdekatan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $studentId = $this->option('student_id');
        $nis = $this->option('nis');

        if ($nis) {
            $student = Student::where('nis', $nis)->first();
            if (!$student) {
                $this->error("Siswa dengan NIS {$nis} tidak ditemukan.");
                return 1;
            }
            $studentId = $student->id;
        }

        $this->info("================================================================================");
        $this->info("DEDUKPLIKASI RIWAYAT TOPUP SALDO (SALDO HISTORIES IN)");
        $this->info("Mode: " . ($isDryRun ? "DRY-RUN (Simulasi Saja)" : "LIVE EXECUTION"));
        $this->info("================================================================================");

        // Fetch all active IN records
        $query = SaldoHistory::where('type', SaldoHistory::TYPE_IN)
            ->where('status', SaldoHistory::STATUS_SUCCESS)
            ->where('description', 'not like', '%Saldo Awal Baseline%')
            ->orderBy('student_id')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc');

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        $records = $query->get();
        $this->info("Total record IN aktif yang dipindai: " . number_format($records->count(), 0, ',', '.'));

        $duplicatesToSoftDelete = [];
        $affectedStudentIds = [];

        // Group by student
        $groupedByStudent = $records->groupBy('student_id');

        foreach ($groupedByStudent as $sId => $studentRecords) {
            $count = $studentRecords->count();
            for ($i = 0; $i < $count; $i++) {
                $current = $studentRecords[$i];
                if (in_array($current->id, $duplicatesToSoftDelete)) {
                    continue;
                }

                for ($j = $i + 1; $j < $count; $j++) {
                    $next = $studentRecords[$j];
                    if (in_array($next->id, $duplicatesToSoftDelete)) {
                        continue;
                    }

                    // Check time difference in seconds
                    $timeCurrent = Carbon::parse($current->created_at);
                    $timeNext = Carbon::parse($next->created_at);
                    $diffSeconds = abs($timeNext->diffInSeconds($timeCurrent));

                    // If more than 120 seconds apart, break inner loop since sorted chronologically
                    if ($diffSeconds > 120) {
                        break;
                    }

                    // Check if duplicate criteria met: same amount
                    if ((float)$current->amount == (float)$next->amount) {
                        // Determine which one to keep
                        // Prefer keeping the one that has transaction_id or shorter/formal description
                        $toDelete = null;
                        if ($current->transaction_id && !$next->transaction_id) {
                            $toDelete = $next;
                        } elseif (!$current->transaction_id && $next->transaction_id) {
                            $toDelete = $current;
                        } else {
                            // If both or neither have tx id, delete the second one ($next)
                            $toDelete = $next;
                        }

                        $duplicatesToSoftDelete[] = $toDelete->id;
                        $affectedStudentIds[$sId] = true;

                        $this->line("  [DUPLIKAT] Siswa {$sId} | Nominal: Rp " . number_format($current->amount, 0, ',', '.') . " | Selisih: {$diffSeconds}s | ID Dihapus: {$toDelete->id} ({$toDelete->created_at})");
                    }
                }
            }
        }

        $totalDuplicates = count($duplicatesToSoftDelete);
        $this->info("\nDitemukan {$totalDuplicates} record duplikat dari " . count($affectedStudentIds) . " siswa.");

        if ($totalDuplicates === 0) {
            $this->info("Tidak ada duplikasi yang perlu dibersihkan.");
            return 0;
        }

        if ($isDryRun) {
            $this->warn("\n[DRY RUN] Tidak ada data yang diubah. Jalankan tanpa --dry-run untuk eksekusi nyata.");
            return 0;
        }

        // Perform Soft Delete
        $this->info("\nMenjalankan soft delete {$totalDuplicates} record duplikat...");
        $now = Carbon::now();
        foreach (array_chunk($duplicatesToSoftDelete, 500) as $chunk) {
            SaldoHistory::whereIn('id', $chunk)->update(['deleted_at' => $now]);
        }

        $this->info("Menjalankan rekalkulasi saldo untuk " . count($affectedStudentIds) . " siswa yang terdampak...");
        $bar = $this->output->createProgressBar(count($affectedStudentIds));
        $bar->start();

        foreach (array_keys($affectedStudentIds) as $sId) {
            SaldoRecalculatorService::recalculateForStudent($sId);
            $bar->advance();
        }
        $bar->finish();

        $this->info("\n\n================================================================================");
        $this->info("DEDUPLIKASI SELESAI: {$totalDuplicates} record duplikat berhasil dinonaktifkan.");
        $this->info("================================================================================");

        return 0;
    }
}
