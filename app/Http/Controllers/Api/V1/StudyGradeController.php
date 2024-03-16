<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\StudyGrade;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Semester;

class StudyGradeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $student_id = request()->student_id;

        $studyGrades = StudyGrade::with(['study', 'academicYear', 'semester', 'classroom'])
            ->where('student_id', $student_id)
            ->latest()
            ->when(request()->academic_year_id, function ($query) {
                $query->where('academic_year_id', request()->academic_year_id);
            })
            ->when(request()->semester_id, function ($query) {
                $query->where('semester_id', request()->semester_id);
            })
            ->when(request()->study_id, function ($query) {
                $query->where('study_id', request()->study_id);
            })
            ->get();
        return $this->getSuccessResponse($studyGrades);
    }

    public function listClassroom()
    {
        $student_id = request()->student_id;
        // search student classroom in study grade
        $classroom = Classroom::with('school')->whereHas('studyGrades', function ($query) use ($student_id) {
            $query->where('student_id', $student_id);
        })->get();
        return $this->getSuccessResponse($classroom);
    }

    public function listSemester()
    {
        $semester = Semester::orderBy('order', 'asc')->get();
        return $this->getSuccessResponse($semester);
    }
}
