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
        $student = Student::findOrfail($request->student_id);
        $informations = Information::whereIsActive(true)->latest()->limit(5)->get();

        return $this->postSuccessResponse("Berhasil Mengambil Data", [
            'student' => $student,
            'informations' => $informations
        ]);
    }
}
