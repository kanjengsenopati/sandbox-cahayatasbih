<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RestoreNegativeBalances extends Command
{
    protected $signature = 'saldo:restore-negative 
        {--connection=default : Database connection to use (default, mysql_master, mysql_aplikasi)}
        {--dry-run : Preview affected students without making changes}
        {--threshold=0 : Only restore balances below this threshold (e.g. -100000)}
        {--student= : Restore specific student by ID (comma-separated for multiple)}
        {--admin-name=System : Admin name for audit trail description}
        {--skip-recalculate : Skip chain recalculation after restore}';

    protected $description = 'Restore all negative student balances to Rp 0 with proper SaldoHistory audit trail';

    public function handle()
    {
        $connectionName = $this->option('connection');
        $dryRun = $this->option('dry-run');
        $threshold = (int) $this->option('threshold');
        $studentFilter = $this->option('student');
        $adminName = $this->option('admin-name');
        $skipRecalculate = $this->option('skip-recalculate');

        // Resolve connection
        $conn = $this->resolveConnection($connectionName);
        $dbName = $conn->getDatabaseName();

        $this->info("═══════════════════════════════════════════════════════");
        $this->info(" RESTORE NEGATIVE BALANCES — {$dbName}");
        $this->info("═══════════════════════════════════════════════════════");

        if ($dryRun) {
            $this->warn("🔍 MODE DRY-RUN: Tidak ada perubahan database");
        }

        // 1. Find all students with negative balance
        $query = $conn->table('students')
            ->whereNull('deleted_at')
            ->where('saldo', '<', $threshold);

        if ($studentFilter) {
            $studentIds = array_map('trim', explode(',', $studentFilter));
            $query->whereIn('id', $studentIds);
        }

        $negativeStudents = $query
            ->select('id', 'name', 'nis', 'saldo', 'classroom_id')
            ->orderBy('saldo', 'asc')
            ->get();

        if ($negativeStudents->isEmpty()) {
            $this->info("✅ Tidak ada santri dengan saldo negatif di database {$dbName}.");
            return 0;
        }

        // 2. Display preview
        $this->newLine();
        $this->info("📊 Ditemukan {$negativeStudents->count()} santri dengan saldo negatif:");
        $this->newLine();

        // Get classroom names for display
        $classroomIds = $negativeStudents->pluck('classroom_id')->unique()->filter()->toArray();
        $classrooms = [];
        if (!empty($classroomIds)) {
            $classrooms = $conn->table('classrooms')
                ->whereIn('id', $classroomIds)
                ->pluck('name', 'id')
                ->toArray();
        }

        $tableData = [];
        $totalDeficit = 0;

        foreach ($negativeStudents as $i => $student) {
            $deficit = abs($student->saldo);
            $totalDeficit += $deficit;
            $className = $classrooms[$student->classroom_id] ?? '-';

            $tableData[] = [
                $i + 1,
                $student->nis ?? Str::limit($student->id, 8, ''),
                Str::limit($student->name, 25),
                $className,
                'Rp ' . number_format($student->saldo, 0, ',', '.'),
                'Rp ' . number_format($deficit, 0, ',', '.'),
            ];
        }

        $this->table(
            ['#', 'NIS', 'Nama', 'Kelas', 'Saldo Saat Ini', 'Adjustment (+)'],
            $tableData
        );

        $this->newLine();
        $this->info("📋 Total Santri     : {$negativeStudents->count()}");
        $this->info("💰 Total Deficit    : Rp " . number_format($totalDeficit, 0, ',', '.'));
        $this->info("🎯 Target Saldo     : Rp 0 (nol) untuk semua");
        $this->newLine();

        if ($dryRun) {
            $this->warn("═══ DRY-RUN SELESAI — Tidak ada perubahan database ═══");
            return 0;
        }

        // 3. Confirmation
        if (!$this->confirm("Lanjutkan restore {$negativeStudents->count()} santri ke saldo Rp 0?", false)) {
            $this->info("Dibatalkan oleh pengguna.");
            return 0;
        }

        // 4. Execute restore
        $this->info("🔄 Memulai restore...");
        $bar = $this->output->createProgressBar($negativeStudents->count());
        $bar->start();

        $restoredCount = 0;
        $failedCount = 0;
        $errors = [];
        $restoredLog = [];

        foreach ($negativeStudents as $student) {
            try {
                $conn->transaction(function () use ($conn, $student, $adminName, &$restoredCount, &$restoredLog) {
                    // Lock student row
                    $lockedStudent = $conn->table('students')
                        ->where('id', $student->id)
                        ->lockForUpdate()
                        ->first();

                    if (!$lockedStudent || (int) $lockedStudent->saldo >= 0) {
                        return; // Skip if already non-negative (race condition safety)
                    }

                    $currentSaldo = (int) $lockedStudent->saldo;
                    $adjustAmount = abs($currentSaldo);
                    $now = Carbon::now();
                    $historyId = (string) Str::uuid();

                    // Create SaldoHistory adjustment record (TYPE_IN to bring to 0)
                    $conn->table('saldo_histories')->insert([
                        'id' => $historyId,
                        'student_id' => $student->id,
                        'amount' => $adjustAmount,
                        'type' => 'IN',
                        'usage' => 'TOPUP',
                        'description' => "Restore saldo negatif: Rp " . number_format($currentSaldo, 0, ',', '.') 
                            . " → Rp 0 (Penyesuaian oleh {$adminName})",
                        'status' => 'SUCCESS',
                        'balance_before' => $currentSaldo,
                        'balance_after' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    // Update student saldo to 0
                    $conn->table('students')
                        ->where('id', $student->id)
                        ->update([
                            'saldo' => 0,
                            'updated_at' => $now,
                        ]);

                    $restoredCount++;
                    $restoredLog[] = [
                        'student_id' => $student->id,
                        'name' => $student->name,
                        'nis' => $student->nis,
                        'saldo_before' => $currentSaldo,
                        'adjustment' => $adjustAmount,
                        'saldo_after' => 0,
                        'history_id' => $historyId,
                        'timestamp' => $now->toDateTimeString(),
                    ];
                });
            } catch (\Throwable $e) {
                $failedCount++;
                $errors[] = [
                    'student_id' => $student->id,
                    'name' => $student->name,
                    'error' => $e->getMessage(),
                ];
                Log::error("[RestoreNegativeBalances] Failed for {$student->name} ({$student->id}): " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // 5. Run recalculation if not skipped
        if (!$skipRecalculate && $restoredCount > 0) {
            $this->info("🔄 Menjalankan recalculate chain integrity untuk {$restoredCount} santri...");
            $recalcBar = $this->output->createProgressBar($restoredCount);
            $recalcBar->start();

            foreach ($restoredLog as $entry) {
                try {
                    \App\Services\SaldoRecalculatorService::recalculateForStudent($entry['student_id']);
                } catch (\Throwable $e) {
                    Log::warning("[RestoreNegativeBalances] Recalculate failed for {$entry['student_id']}: " . $e->getMessage());
                }
                $recalcBar->advance();
            }

            $recalcBar->finish();
            $this->newLine(2);
        }

        // 6. Summary
        $this->info("═══════════════════════════════════════════════════════");
        $this->info(" HASIL RESTORE");
        $this->info("═══════════════════════════════════════════════════════");
        $this->info("✅ Berhasil   : {$restoredCount} santri");

        if ($failedCount > 0) {
            $this->error("❌ Gagal      : {$failedCount} santri");
            foreach ($errors as $err) {
                $this->error("   - {$err['name']}: {$err['error']}");
            }
        }

        $this->info("💰 Total Adj  : Rp " . number_format($totalDeficit, 0, ',', '.'));
        $this->info("📅 Waktu      : " . now()->toDateTimeString());

        // 7. Write audit log file
        $logPath = storage_path('logs/restore_negative_balance_' . now()->format('Y-m-d_His') . '.json');
        file_put_contents($logPath, json_encode([
            'executed_at' => now()->toDateTimeString(),
            'database' => $dbName,
            'connection' => $connectionName,
            'admin_name' => $adminName,
            'total_students' => $negativeStudents->count(),
            'restored_count' => $restoredCount,
            'failed_count' => $failedCount,
            'total_adjustment' => $totalDeficit,
            'restored' => $restoredLog,
            'errors' => $errors,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("📝 Log audit  : {$logPath}");
        $this->info("═══════════════════════════════════════════════════════");

        return $failedCount > 0 ? 1 : 0;
    }

    private function resolveConnection(string $name)
    {
        if ($name === 'default') {
            return DB::connection();
        }

        try {
            $conn = DB::connection($name);
            $conn->getPdo(); // Test connection
            return $conn;
        } catch (\Throwable $e) {
            $this->error("Gagal koneksi ke '{$name}': " . $e->getMessage());
            $this->info("Koneksi tersedia: default, mysql_master, mysql_aplikasi, mysql_ponpes");
            exit(1);
        }
    }
}
