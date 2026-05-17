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
        
        return Student::with(['classroom', 'school', 'asramaHost'])
            ->where('user_id', $user->id)
            ->when($activeStudentId, function ($query) use ($activeStudentId) {
                return $query->where('id', $activeStudentId);
            })
            ->first() ?: Student::with(['classroom', 'school', 'asramaHost'])->where('user_id', $user->id)->first();
    }
}
