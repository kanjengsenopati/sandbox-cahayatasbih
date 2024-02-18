<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\StudentAchievement;
use App\Http\Controllers\Controller;

class StudentAchievementController extends Controller
{
    public function index(Request $request)
    {
        $achievements = StudentAchievement::with('student', 'academicYear', 'classroom', 'school')
            ->where('student_id', $request->student_id)
            ->when($request->title, function ($query, $title) {
                return $query->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($title) . '%']);
            })
            ->paginate(10);

        return $this->postSuccessResponse("Berhasil mengambil data prestasi siswa", $achievements);
    }
}
