<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\StudyGrade;
use App\Models\Semester;
use Illuminate\Http\Request;

class StudyGradeController extends BaseWaliApiController
{
    public function index(Request $request)
    {
        $student = $this->resolveActiveStudent();
        if (!$student) return response()->json(['message' => 'Student not found'], 404);
        
        $grades = StudyGrade::with(['study', 'academicYear', 'semester', 'classroom'])
            ->where('student_id', $student->id)
            ->when($request->semester_id, function ($query) use ($request) {
                $query->where('semester_id', $request->semester_id);
            })
            ->latest()
            ->get();
            
        return response()->json([
            'student_name' => $student->name,
            'data' => $grades
        ]);
    }

    public function semesters()
    {
        $semesters = Semester::orderBy('order', 'asc')->get();
        return response()->json([
            'data' => $semesters
        ]);
    }
}
