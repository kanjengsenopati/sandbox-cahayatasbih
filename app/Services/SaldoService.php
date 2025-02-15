<?php

namespace App\Services;

use App\Models\SaldoHistory;
use InvalidArgumentException;

class SaldoService
{
    public static function addHistory($student, $amount, $type, $usage, $status, $description)
    {
        // Validate input
        if (!$student || !is_numeric($amount)) {
            throw new InvalidArgumentException('Invalid student or amount.');
        }

        // Calculate saldo before and after
        $saldoBefore = $student->saldo ?? 0;
        $saldoAfter = $saldoBefore + $amount;

        // Create saldo history record
        SaldoHistory::create([
            'student_id' => $student->id,
            'type' => $type,
            'amount' => $amount,
            'description' => $description,
            'usage' => $usage,
            'status' => $status,
            'balance_before' => $saldoBefore ?? 0,
            'balance_after' => $saldoAfter ?? 0,
        ]);
    }
}
