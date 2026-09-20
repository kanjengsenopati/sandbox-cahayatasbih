<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends BaseWaliApiController
{
    public function index()
    {
        $user = Auth::guard('wali')->user();
        $students = Student::with(['classroom', 'school', 'asramaHost'])
            ->where('user_id', $user->id)
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($s) {
                $hasPendingTopup = \App\Models\Transaction::where('student_id', $s->id)
                    ->where('type', \App\Models\Transaction::TYPE_SALDO)
                    ->where('status', \App\Models\Transaction::STATUS_PENDING_CONFIRMATION)
                    ->exists();
                $s->setAttribute('has_pending_topup', $hasPendingTopup);
                $s->setAttribute('show_pwa_saldo', $s->isPwaSaldoVisible());
                $s->setAttribute('allow_pwa_login', $s->isPwaLoginAllowed());
                $s->setAttribute('allow_pwa_saldo_payment', $s->isPwaSaldoPaymentAllowed());
                $s->setAttribute('effective_daily_limit', $s->getEffectiveDailyLimit());
                $s->setAttribute('is_custom_limit', $s->daily_limit > 0);
                return $s;
            });
            
        return response()->json($students);
    }

    public function active()
    {
        $activeStudent = $this->resolveActiveStudent();
        if ($activeStudent) {
            $hasPendingTopup = \App\Models\Transaction::where('student_id', $activeStudent->id)
                ->where('type', \App\Models\Transaction::TYPE_SALDO)
                ->where('status', \App\Models\Transaction::STATUS_PENDING_CONFIRMATION)
                ->exists();
            $activeStudent->setAttribute('has_pending_topup', $hasPendingTopup);
            $activeStudent->setAttribute('show_pwa_saldo', $activeStudent->isPwaSaldoVisible());
            $activeStudent->setAttribute('allow_pwa_login', $activeStudent->isPwaLoginAllowed());
            $activeStudent->setAttribute('allow_pwa_saldo_payment', $activeStudent->isPwaSaldoPaymentAllowed());
            $activeStudent->setAttribute('effective_daily_limit', $activeStudent->getEffectiveDailyLimit());
            $activeStudent->setAttribute('is_custom_limit', $activeStudent->daily_limit > 0);
        }
        return response()->json($activeStudent);
    }
}
