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
            ->get();
            
        return response()->json($students);
    }

    public function active()
    {
        $activeStudent = $this->resolveActiveStudent();
        return response()->json($activeStudent);
    }
}
