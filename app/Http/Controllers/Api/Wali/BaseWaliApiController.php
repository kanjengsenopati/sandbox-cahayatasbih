<?php

namespace App\Http\Controllers\Api\Wali;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class BaseWaliApiController extends Controller
{
    protected function resolveActiveStudent()
    {
        $user = Auth::guard('wali')->user();
        if (!$user) return null;
        
        $activeStudentId = session('active_student_id');
        
        $student = Student::with(['classroom', 'school', 'asramaHost'])
            ->where('user_id', $user->id)
            ->when($activeStudentId, function ($query) use ($activeStudentId) {
                return $query->where('id', $activeStudentId);
            })
            ->first() ?: Student::with(['classroom', 'school', 'asramaHost'])->where('user_id', $user->id)->first();

        if ($student) {
            $latestHistory = \App\Models\SaldoHistory::where('student_id', $student->id)
                ->whereNotIn('status', [\App\Models\SaldoHistory::STATUS_FAILED])
                ->latest()
                ->first();

            if ($latestHistory && $latestHistory->balance_after !== null) {
                $targetSaldo = (int)$latestHistory->balance_after;
                if ((int)$student->saldo !== $targetSaldo) {
                    $student->saldo = $targetSaldo;
                    $student->saveQuietly();
                }
            }
        }

        return $student;
    }
}
