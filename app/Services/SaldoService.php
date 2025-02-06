<?php

namespace App\Services;

use App\Models\SaldoHistory;

class SaldoService
{
    public static function addHistory($student, $amount, $type, $usage, $status, $description)
    {
        SaldoHistory::create([
            'student_id' => $student->id,
            'type' => $type,
            'amount' => $amount,
            'description' => $description,
            'usage' => $usage,
            'status' => $status
        ]);
    }
}
