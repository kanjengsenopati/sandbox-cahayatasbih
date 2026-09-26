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

            if ($histories->isEmpty()) {
                return true;
            }

            // Selesaikan potensi tie pada created_at jika ada >1 transaksi pada detik yang sama
            if ($histories->count() > 1) {
                $histories = self::orderHistoriesContinuity($histories);
            }

            $runningBalance = 0;

            // Deteksi siswa yang memiliki saldo awal di-set langsung tanpa riwayat TYPE_IN.
            // Jika record pertama memiliki balance_before > 0,
            // maka gunakan balance_before sebagai titik awal agar tidak menghasilkan saldo negatif.
            $firstHistory = $histories->first();
            if ($firstHistory) {
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

                // Guard: running balance tidak boleh negatif
                // Jika negatif, clamp ke 0 dan catat di log untuk investigasi
                if ($runningBalance < 0) {
                    Log::warning("[SaldoRecalculator] Running balance went negative for student {$studentId} at history #{$history->id}. Clamping to 0. (was: {$runningBalance})");
                    $runningBalance = 0;
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
            // Guard: saldo tidak boleh < 0
            $safeSaldo = max(0, $runningBalance);
            if ((float) $student->saldo !== $safeSaldo) {
                DB::table('students')->where('id', $student->id)->update(['saldo' => $safeSaldo]);
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

    /**
     * Selesaikan urutan rantai transaksi jika ada beberapa history pada detik/timestamp yang sama.
     * Mengurutkan kontinuitas saldo: balance_before record berikutnya == balance_after record sebelumnya.
     *
     * @param \Illuminate\Support\Collection $histories
     * @return \Illuminate\Support\Collection
     */
    public static function orderHistoriesContinuity($histories)
    {
        $grouped = $histories->groupBy(function ($h) {
            return $h->created_at ? $h->created_at->format('Y-m-d H:i:s') : '1970-01-01 00:00:00';
        });

        $sorted = collect();
        $runningTracker = null;

        foreach ($grouped as $timeStr => $group) {
            if ($group->count() === 1) {
                $item = $group->first();
                $sorted->push($item);
                $runningTracker = (float) $item->balance_after;
                continue;
            }

            // Ada multiple records dalam detik yang sama
            $remaining = $group->values();
            while ($remaining->isNotEmpty()) {
                $matchIdx = false;

                if ($runningTracker !== null) {
                    $matchIdx = $remaining->search(function ($item) use ($runningTracker) {
                        return (float) $item->balance_before === (float) $runningTracker;
                    });
                }

                if ($matchIdx === false) {
                    // Jika di awal (runningTracker masih null) atau gap:
                    // Prioritaskan transaksi TYPE_OUT terlebih dahulu jika ada pasangan OUT lalu IN (seperti bayar lalu refund)
                    $outIdx = $remaining->search(function ($item) {
                        return $item->type === SaldoHistory::TYPE_OUT;
                    });
                    $matchIdx = ($outIdx !== false) ? $outIdx : 0;
                }

                $chosen = $remaining->pull($matchIdx);
                $sorted->push($chosen);
                $runningTracker = (float) $chosen->balance_after;
                $remaining = $remaining->values();
            }
        }

        return $sorted;
    }
}

