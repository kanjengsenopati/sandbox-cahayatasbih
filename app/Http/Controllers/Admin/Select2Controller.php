<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class Select2Controller extends Controller
{
    public function index(Request $request)
    {
        $data = match ($request->data_type) {
            'USER' => $this->user($request),
            'STUDENT' => $this->student($request),
            'CLASSROOM_BY_SCHOOL' => $this->classroomBySchool($request),
        };
        return response()->json($data);
    }

    public function user($request)
    {
        return User::whereRaw('LOWER(name) like ?', ['%' . strtolower($request->search) . '%'])
            ->take(10)
            ->get();
    }

    public function student($request)
    {
        return Student::whereRaw('LOWER(name) like ?', ['%' . strtolower($request->search) . '%'])
            ->take(10)
            ->get();
    }

    public function classroomBySchool($request)
    {
        return Classroom::where('school_id', $request->school_id)->orderBy('name')->get();
    }
}
