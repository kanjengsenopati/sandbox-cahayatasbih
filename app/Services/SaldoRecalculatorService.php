<?php

namespace App\Services;

use App\Models\Student;
use App\Models\SaldoHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaldoRecalculatorService
{
    /**
     * Recalculate and repair running balance (balance_before and balance_after)
     * for a given student ID based on chronologically ordered SUCCESS records.
     *
     * @param string|int $studentId
     * @return bool
     */
    public static function recalculateForStudent($studentId): bool
    {
        return DB::transaction(function () use ($studentId) {
            $student = Student::lockForUpdate()->find($studentId);
            if (!$student) return false;

            // Fetch all SUCCESS saldo histories in chronological order
            $histories = SaldoHistory::where('student_id', $studentId)
                ->where('status', SaldoHistory::STATUS_SUCCESS)
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $runningBalance = 0;

            // Deteksi siswa yang memiliki saldo awal di-set langsung tanpa riwayat TYPE_IN.
            // Jika record pertama bukan TYPE_IN dan memiliki balance_before > 0,
            // maka gunakan balance_before sebagai titik awal agar tidak menghasilkan saldo negatif.
            $firstHistory = $histories->first();
            if ($firstHistory && $firstHistory->type !== SaldoHistory::TYPE_IN) {
                $initialBalance = (float) $firstHistory->balance_before;
                if ($initialBalance > 0) {
                    $runningBalance = $initialBalance;
                    Log::info("[SaldoRecalculator] Initial balance gap detected for student {$studentId}, starting from: {$runningBalance}");
                }
            }

            foreach ($histories as $history) {
                $amount = (float) $history->amount;
                $balanceBefore = $runningBalance;

                if ($history->type === SaldoHistory::TYPE_IN) {
                    $runningBalance += $amount;
                } else {
                    $runningBalance -= $amount;
                }

                $balanceAfter = $runningBalance;

                // Update row if values differ
                if ((float) $history->balance_before !== $balanceBefore || (float) $history->balance_after !== $balanceAfter) {
                    DB::table('saldo_histories')->where('id', $history->id)->update([
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceAfter
                    ]);
                }
            }

            // Sync current student's total saldo to match latest running balance
            if ((float) $student->saldo !== $runningBalance) {
                DB::table('students')->where('id', $student->id)->update(['saldo' => $runningBalance]);
            }

            return true;
        });
    }

    /**
     * Recalculate for all students across the system.
     *
     * @return int Count of recalculated students
     */
    public static function recalculateAllStudents(): int
    {
        $studentIds = Student::pluck('id');
        $count = 0;

        foreach ($studentIds as $id) {
            self::recalculateForStudent($id);
            $count++;
        }

        return $count;
    }
}
