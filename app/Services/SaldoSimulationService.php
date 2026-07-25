<?php

namespace App\Services;

use App\Models\Student;
use App\Models\SaldoHistory;
use App\Models\SavingHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaldoSimulationService
{
    /**
     * Run full audit simulation across students without modifying students table.
     */
    public function runSimulation(?string $studentId = null): array
    {
        $query = Student::with('classroom.school')->whereNull('deleted_at');
        if ($studentId) {
            $query->where('id', $studentId);
        }
        $students = $query->get();

        $processed = 0;
        $mismatches = 0;
        $negatives = 0;

        foreach ($students as $student) {
            $currentSaldo = (int) round($student->saldo);
            $currentSaving = (int) round($student->saving);

            // 1. Recalculate Saldo in-memory from saldo_histories
            $saldoHistories = DB::table('saldo_histories')
                ->where('student_id', $student->id)
                ->where('status', SaldoHistory::STATUS_SUCCESS)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $simulatedSaldo = 0;
            if ($saldoHistories->isNotEmpty()) {
                $runningSaldo = (int) round($saldoHistories->first()->balance_before);
                foreach ($saldoHistories as $sh) {
                    $amount = (int) round($sh->amount);
                    if ($sh->type === SaldoHistory::TYPE_IN) {
                        $runningSaldo += $amount;
                    } else {
                        $runningSaldo -= $amount;
                    }
                }
                $simulatedSaldo = $runningSaldo;
            } else {
                $simulatedSaldo = $currentSaldo;
            }

            // 2. Recalculate Saving in-memory from saving_histories
            $savingHistories = DB::table('saving_histories')
                ->where('student_id', $student->id)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $simulatedSaving = 0;
            if ($savingHistories->isNotEmpty()) {
                foreach ($savingHistories as $svh) {
                    $amount = (int) round($svh->amount);
                    if ($svh->type === 'IN' || $svh->type === 'DEPOSIT') {
                        $simulatedSaving += $amount;
                    } else {
                        $simulatedSaving -= $amount;
                    }
                }
            } else {
                $simulatedSaving = $currentSaving;
            }

            $saldoDiff = $simulatedSaldo - $currentSaldo;
            $savingDiff = $simulatedSaving - $currentSaving;

            // 3. Diagnose Issue Type
            $issueType = 'OK';
            $issueDesc = 'Saldo seimbang dan konsisten.';

            if ($simulatedSaldo < 0 || $currentSaldo < 0) {
                $issueType = 'NEGATIVE_BALANCE';
                $issueDesc = 'Terdeteksi saldo negatif pada profil atau hasil kalkulasi riwayat.';
                $negatives++;
            } elseif ($saldoDiff !== 0) {
                $issueType = 'MISMATCH';
                $issueDesc = 'Terdapat selisih antara saldo profil DB (' . number_format($currentSaldo, 0, ',', '.') . ') dan hasil rekalkulasi riwayat (' . number_format($simulatedSaldo, 0, ',', '.') . ').';
                $mismatches++;
            }

            $classroomName = $student->classroom ? $student->classroom->name : '-';
            if ($student->classroom && $student->classroom->school) {
                $classroomName .= ' (' . $student->classroom->school->name . ')';
            }

            // 4. Save/Update to draft simulation table
            DB::table('saldo_audit_simulations')->updateOrInsert(
                ['student_id' => $student->id],
                [
                    'id' => Str::uuid()->toString(),
                    'student_nis' => $student->nis,
                    'student_name' => $student->name,
                    'classroom_name' => $classroomName,
                    'current_saldo' => $currentSaldo,
                    'simulated_saldo' => $simulatedSaldo,
                    'saldo_diff' => $saldoDiff,
                    'current_saving' => $currentSaving,
                    'simulated_saving' => $simulatedSaving,
                    'saving_diff' => $savingDiff,
                    'issue_type' => $issueType,
                    'issue_description' => $issueDesc,
                    'status' => 'DRAFT',
                    'simulated_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $processed++;
        }

        return [
            'total_processed' => $processed,
            'total_mismatches' => $mismatches,
            'total_negatives' => $negatives,
        ];
    }

    /**
     * Apply fix for a specific student after draft review.
     */
    public function applyFixForStudent(string $studentId, ?string $adminId = null): bool
    {
        $draft = DB::table('saldo_audit_simulations')
            ->where('student_id', $studentId)
            ->first();

        if (!$draft) {
            throw new \Exception('Draft simulasi tidak ditemukan untuk siswa ini.');
        }

        $student = Student::find($studentId);
        if (!$student) {
            throw new \Exception('Siswa tidak ditemukan.');
        }

        DB::beginTransaction();
        try {
            // 1. Create Snapshot Backup
            DB::table('student_saldo_backups')->insert([
                'id' => Str::uuid()->toString(),
                'student_id' => $student->id,
                'backup_saldo' => $student->saldo,
                'backup_saving' => $student->saving,
                'reason' => 'Perbaikan saldo dari Draft Simulasi Audit',
                'admin_id' => $adminId,
                'created_at' => now(),
            ]);

            // 2. Update Student Balance to Simulated Values
            $student->saldo = $draft->simulated_saldo;
            $student->saving = $draft->simulated_saving;
            $student->save();

            // 3. Update Draft Record Status
            DB::table('saldo_audit_simulations')
                ->where('student_id', $studentId)
                ->update([
                    'status' => 'APPLIED',
                    'applied_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Rollback fix for a specific student using snapshot backup.
     */
    public function rollbackFixForStudent(string $studentId): bool
    {
        $backup = DB::table('student_saldo_backups')
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$backup) {
            throw new \Exception('Tidak ada snapshot backup untuk siswa ini.');
        }

        $student = Student::find($studentId);
        if (!$student) {
            throw new \Exception('Siswa tidak ditemukan.');
        }

        DB::beginTransaction();
        try {
            $student->saldo = $backup->backup_saldo;
            $student->saving = $backup->backup_saving;
            $student->save();

            DB::table('saldo_audit_simulations')
                ->where('student_id', $studentId)
                ->update([
                    'status' => 'DRAFT',
                    'applied_at' => null,
                    'updated_at' => now(),
                ]);

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
