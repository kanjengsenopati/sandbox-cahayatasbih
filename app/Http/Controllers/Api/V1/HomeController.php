<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Information;
use App\Models\Student;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $student = Student::with('classroom', 'school')->findOrfail($request->student_id);
        $informations = Information::whereIsActive(true)->latest()->limit(5)->get();

        return $this->postSuccessResponse("Berhasil Mengambil Data", [
            'student' => $student,
            'informations' => $informations
        ]);
    }

    public function information()
    {
        $informations = Information::whereIsActive(true)->latest()->paginate(10);

        return $this->postSuccessResponse("Berhasil Mengambil Data", $informations);
    }

    public function listStudent()
    {
        $students = Student::with('classroom', 'school')->where('user_id', auth()->user()->id)->latest()->get();

        return $this->postSuccessResponse("Berhasil Mengambil Data", $students);
    }
}
