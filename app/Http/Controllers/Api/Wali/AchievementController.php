<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\StudentAchievement;
use Illuminate\Http\Request;

class AchievementController extends BaseWaliApiController
{
    public function index()
    {
        $student = $this->resolveActiveStudent();
        if (!$student) return response()->json(['message' => 'Student not found'], 404);
        
        $achievements = StudentAchievement::with('academicYear', 'classroom')
            ->where('student_id', $student->id)
            ->latest()
            ->get();
            
        return response()->json([
            'student_name' => $student->name,
            'total_achievements' => $achievements->count(),
            'data' => $achievements
        ]);
    }
}
