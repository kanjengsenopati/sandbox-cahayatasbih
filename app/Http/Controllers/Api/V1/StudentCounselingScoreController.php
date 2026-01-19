<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\StudentCounselingScore;

class StudentCounselingScoreController extends Controller
{
    public function index(Request $request)
    {
        $studentCounselingScores = StudentCounselingScore::with('student', 'academicYear', 'classroom', 'school')
            ->where('student_id', $request->student_id)
            ->when($request->search, function ($query) use ($request) {
                $searchTerm = strtolower($request->search);
                $query->whereRaw('LOWER(semester) LIKE ?', ['%' . $searchTerm . '%'])
                    ->orWhereRaw('LOWER(score) LIKE ?', ['%' . $searchTerm . '%'])
                    ->orWhereRaw('LOWER(violation) LIKE ?', ['%' . $searchTerm . '%'])
                    ->orWhereRaw('LOWER(action) LIKE ?', ['%' . $searchTerm . '%'])
                    ->orWhereRaw('LOWER(note) LIKE ?', ['%' . $searchTerm . '%']);
            })
            ->latest()
            ->paginate(10);

        return $this->postSuccessResponse("Berhasil mengambil data skor konseling siswa", $studentCounselingScores);
    }
}
