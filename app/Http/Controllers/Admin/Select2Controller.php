<?php

namespace App\Http\Controllers\Admin;

use App\Models\Item;
use App\Models\User;
use App\Models\Study;
use App\Models\Student;
use App\Models\BillItem;
use App\Models\Semester;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\CategoryItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Select2Controller extends Controller
{
    public function index(Request $request)
    {
        $data = match ($request->data_type) {
            'USER' => $this->user($request),
            'STUDENT' => $this->student($request),
            'CLASSROOM_BY_SCHOOL' => $this->classroomBySchool($request),
            'CATEGORY_ITEM' => $this->categoryItem($request),
            'ITEM' => $this->item($request),
            'BILL_ITEM' => $this->billItem($request),
            'ACADEMIC_YEAR' => $this->academicYear($request),
            'STUDY' => $this->study($request),
            'SEMESTER' => $this->semester($request),
        };
        return response()->json($data);
    }

    public function user($request)
    {
        return User::whereRaw('LOWER(name) like ?', ['%' . strtolower($request->search) . '%'])
            ->take(30)
            ->get();
    }

    public function student($request)
    {
        return Student::hasSchoolPlace()
            ->with('classroom.school')->whereRaw('LOWER(name) like ?', ['%' . strtolower($request->search) . '%'])
            ->take(30)
            ->get();
    }

    public function item($request)
    {
        return Item::whereRaw('LOWER(name) like ?', ['%' . strtolower($request->search) . '%'])
            ->orWhereRaw('LOWER(code) like ?', ['%' . strtolower($request->search) . '%'])
            ->whereIsActive(true)
            ->take(10)
            ->get();
    }

    public function categoryItem($request)
    {
        return CategoryItem::whereRaw('LOWER(name) like ?', ['%' . strtolower($request->search) . '%'])
            ->orWhereRaw('LOWER(code) like ?', ['%' . strtolower($request->search) . '%'])
            ->take(10)
            ->get();
    }

    public function billItem($request)
    {
        return BillItem::whereRaw('LOWER(name) like ?', ['%' . strtolower($request->search) . '%'])
            ->take(10)
            ->get();
    }

    public function classroomBySchool($request)
    {
        return Classroom::where('school_id', $request->school_id)->orderBy('name')->get();
    }

    public function academicYear($request)
    {
        return AcademicYear::whereRaw('LOWER(name) like ?', ['%' . strtolower($request->search) . '%'])
            ->take(10)
            ->get();
    }

    public function study($request)
    {
        return Study::whereRaw('LOWER(name) like ?', ['%' . strtolower($request->search) . '%'])
            ->take(10)
            ->get();
    }

    public function semester($request)
    {
        return Semester::whereRaw('LOWER(name) like ?', ['%' . strtolower($request->search) . '%'])
            ->orderBy('order')
            ->take(10)
            ->get();
    }
}
