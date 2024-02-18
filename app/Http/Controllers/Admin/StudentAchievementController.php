<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\StudentAchievement;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentAchievementRequest;
use App\Models\Classroom;
use App\Models\School;

class StudentAchievementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = StudentAchievement::with('student', 'academicYear', 'classroom', 'school')->latest()->get();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('student-achievement.edit', $data->id);
                    $actionDelete = route('student-achievement.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action', 'link'])
                ->make(true);
        }
        return view('admins.student-achievement.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $schools = School::latest()->get();
        return view('admins.student-achievement.create-edit', compact('schools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudentAchievementRequest $request)
    {
        StudentAchievement::create($request->validated());
        return redirect()->route('student-achievement.index')->with('success', 'Prestasi siswa berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StudentAchievement $studentAchievement)
    {
        return view('admins.student-achievement.create-edit', compact('studentAchievement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentAchievementRequest $request, StudentAchievement $studentAchievement)
    {
        $studentAchievement->update($request->validated());
        return redirect()->route('student-achievement.index')->with('success', 'Prestasi siswa berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StudentAchievement $studentAchievement)
    {
        $studentAchievement->delete();
        return redirect()->route('student-achievement.index')->with('success', 'Prestasi siswa berhasil dihapus');
    }

    public function getClassroom(Request $request)
    {
        $classrooms = Classroom::where('school_id', $request->school_id)->get();
        return $this->postSuccessResponse("berhasil Mengambil data kelas", $classrooms);
    }
}
