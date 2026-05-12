<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\Information;
use App\Models\Student;
use App\Models\Tahfidz;
use App\Models\StudyGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends BaseWaliApiController
{
    public function index()
    {
        $user = Auth::guard('wali')->user();
        
        $informations = Information::with('informationCategory')
            ->where('is_active', true)
            ->latest()
            ->take(5)
            ->get();
            
        $students = Student::where('user_id', $user->id)
            ->orderBy('name', 'asc')
            ->get();
            
        $activeStudent = $this->resolveActiveStudent();

        $tahfidzCount = 0;
        $studyCount = 0;

        if ($activeStudent) {
            $tahfidzCount = Tahfidz::where('student_id', $activeStudent->id)->sum('number_of_pages');
            $studyCount = StudyGrade::where('student_id', $activeStudent->id)->distinct('study_id')->count();
        }

        return response()->json([
            'informations' => $informations,
            'students' => $students,
            'activeStudent' => $activeStudent,
            'tahfidzCount' => (int) $tahfidzCount,
            'studyCount' => (int) $studyCount,
        ]);
    }
}
