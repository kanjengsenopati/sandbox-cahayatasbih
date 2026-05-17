<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\StudentCounselingScore;
use Illuminate\Http\Request;

class CounselingController extends BaseWaliApiController
{
    public function index(Request $request)
    {
        $student = $this->resolveActiveStudent();
        if (!$student) return response()->json(['message' => 'Student not found'], 404);
        
        $scores = StudentCounselingScore::with('academicYear', 'classroom', 'school')
            ->where('student_id', $student->id)
            ->latest()
            ->get();
            
        // Calculate dynamic summary status based on scores (e.g. average counseling score)
        $averageScore = $scores->avg('score') ?? 100; // Default good score is 100
        
        return response()->json([
            'student_name' => $student->name,
            'average_score' => (int) $averageScore,
            'data' => $scores
        ]);
    }
}
