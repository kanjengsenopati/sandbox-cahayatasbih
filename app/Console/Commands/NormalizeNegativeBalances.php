<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\SaldoHistory;
use Carbon\Carbon;

class NormalizeNegativeBalances extends Command
{
    protected $signature = 'saldo:normalize
        {--dry-run : Simulasi tanpa mengubah data}
        {--student= : UUID siswa spesifik}
        {--threshold=0 : Batas saldo minimum (default: 0, artinya semua yg < 0)}
        {--connection= : Koneksi database (default: default connection)}';

    protected $description = 'Normalisasi saldo negatif siswa ke Rp 0 dengan menyuntikkan record adjustment. Aturan bisnis: saldo tidak boleh minus.';

    public function handle()
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $dryRun = $this->option('dry-run');
        $studentId = $this->option('student');
        $threshold = (int) $this->option('threshold');
        $connection = $this->option('connection');
        $startTime = now();

        $this->info("╔══════════════════════════════════════════════════════╗");
        $this->info("║     NORMALISASI SALDO NEGATIF                       ║");
        $this->info("╚══════════════════════════════════════════════════════╝");
        $this->newLine();

        if ($dryRun) {
            $this->warn('🔍 MODE DRY-RUN — Tidak ada data yang akan diubah.');
        }

        $this->info("Threshold : < Rp " . number_format($threshold));
        $this->info("Target    : " . ($studentId ? "Siswa {$studentId}" : 'Semua siswa aktif'));
        $this->newLine();

        // Query siswa dengan saldo negatif
        $query = Student::where('saldo', '<', $threshold)
            ->whereNull('deleted_at');

        if ($studentId) {
            $query->where('id', $studentId);
        }

        $negativeStudents = $query->with(['classroom.school'])->get();

        if ($negativeStudents->isEmpty()) {
            $this->info('✅ Tidak ada siswa dengan saldo negatif. Sistem sudah bersih!');
            return 0;
        }

        $this->warn("⚠️  Ditemukan {$negativeStudents->count()} siswa dengan saldo < Rp " . number_format($threshold));
        $this->newLine();

        $report = [];
        $totalAdjustment = 0;

        foreach ($negativeStudents as $student) {
            $currentSaldo = (int) $student->saldo;
            $adjustmentAmount = abs($currentSaldo);
            $schoolName = $student->classroom?->school?->name ?? 'N/A';
            $classroomName = $student->classroom?->name ?? 'N/A';

            $this->line("  📋 {$student->name} (NIS: {$student->nis})");
            $this->line("     Kelas: {$classroomName} | UPT: {$schoolName}");
            $this->line("     Saldo: Rp " . number_format($currentSaldo) . " → Rp 0");
            $this->line("     Adjustment: + Rp " . number_format($adjustmentAmount));

            $reportEntry = [
                'student_id' => $student->id,
                'name' => $student->name,
                'nis' => $student->nis,
                'classroom' => $classroomName,
                'school' => $schoolName,
                'saldo_before' => $currentSaldo,
                'adjustment' => $adjustmentAmount,
                'saldo_after' => 0,
                'status' => 'pending',
            ];

            if (!$dryRun) {
                try {
                    DB::transaction(function () use ($student, $currentSaldo, $adjustmentAmount) {
                        // Lock student record
                        $lockedStudent = Student::lockForUpdate()->find($student->id);
                        if (!$lockedStudent || (int) $lockedStudent->saldo >= 0) {
                            return; // Skip jika sudah dinormalisasi oleh proses lain
                        }

                        // Inject adjustment record
                        SaldoHistory::create([
                            'student_id' => $student->id,
                            'type' => SaldoHistory::TYPE_IN,
                            'amount' => $adjustmentAmount,
                            'description' => "Normalisasi saldo negatif — automated adjustment (dari Rp " . number_format($currentSaldo) . " ke Rp 0)",
                            'status' => SaldoHistory::STATUS_SUCCESS,
                            'usage' => 'ADJUSTMENT',
                            'balance_before' => $currentSaldo,
                            'balance_after' => 0,
                        ]);

                        // Update student saldo to 0
                        DB::table('students')
                            ->where('id', $student->id)
                            ->update(['saldo' => 0, 'updated_at' => now()]);
                    });

                    $reportEntry['status'] = 'normalized';
                    $this->info("     ✅ Berhasil dinormalisasi");
                } catch (\Throwable $e) {
                    $reportEntry['status'] = 'error';
                    $reportEntry['error'] = $e->getMessage();
                    $this->error("     ❌ Gagal: " . $e->getMessage());
                }
            } else {
                $reportEntry['status'] = 'dry-run';
                $this->comment("     🔍 [DRY-RUN] Akan dinormalisasi");
            }

            $report[] = $reportEntry;
            $totalAdjustment += $adjustmentAmount;
            $this->newLine();
        }

        // Summary
        $this->info("╔══════════════════════════════════════════════════════╗");
        $this->info("║                    RINGKASAN                        ║");
        $this->info("╚══════════════════════════════════════════════════════╝");

        $headers = ['Nama', 'NIS', 'Kelas', 'Saldo Lama', 'Adjustment', 'Status'];
        $rows = array_map(fn($r) => [
            $r['name'],
            $r['nis'] ?? '-',
            $r['classroom'],
            'Rp ' . number_format($r['saldo_before']),
            '+ Rp ' . number_format($r['adjustment']),
            $r['status'],
        ], $report);
        $this->table($headers, $rows);

        $elapsed = $startTime->diffInSeconds(now());
        $this->newLine();
        $this->info("🏁 Total siswa   : " . count($report));
        $this->info("💰 Total adjustment: Rp " . number_format($totalAdjustment));
        $this->info("⏱️  Durasi         : {$elapsed} detik");

        // Save JSON report
        $logPath = storage_path('logs/normalize_balances_' . now()->format('Y-m-d_His') . '.json');
        file_put_contents($logPath, json_encode([
            'executed_at' => now()->toDateTimeString(),
            'dry_run' => $dryRun,
            'threshold' => $threshold,
            'total_students' => count($report),
            'total_adjustment' => $totalAdjustment,
            'duration_seconds' => $elapsed,
            'details' => $report,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("📄 Laporan disimpan: {$logPath}");

        if ($dryRun) {
            $this->newLine();
            $this->warn("⚠️  Ini hanya simulasi. Jalankan tanpa --dry-run untuk menormalisasi data.");
        }

        return 0;
    }
}
