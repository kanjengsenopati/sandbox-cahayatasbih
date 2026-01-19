<?php

namespace App\Http\Controllers\Admin;

use App\Models\School;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\StudentAchievement;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\StudentAchievementRequest;

class StudentAchievementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Prestasi Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            $data = StudentAchievement::with('student', 'academicYear', 'classroom')->latest()->get();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('student-achievement.edit', $data->id);
                    $actionDelete = route('student-achievement.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Prestasi Santri']) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Prestasi Santri']) .
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
        if (!Auth::user()->can('Create Prestasi Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $schools = School::hasSchool()->orderBy('name')->get();
        return view('admins.student-achievement.create-edit', compact('schools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudentAchievementRequest $request)
    {
        if (!Auth::user()->can('Create Prestasi Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
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
        if (!Auth::user()->can('Edit Prestasi Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $schools = School::hasSchool()->orderBy('name')->get();
        return view('admins.student-achievement.create-edit', compact('studentAchievement', 'schools'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentAchievementRequest $request, StudentAchievement $studentAchievement)
    {
        if (!Auth::user()->can('Edit Prestasi Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $studentAchievement->update($request->validated());
        return redirect()->route('student-achievement.index')->with('success', 'Prestasi siswa berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StudentAchievement $studentAchievement)
    {
        if (!Auth::user()->can('Delete Prestasi Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $studentAchievement->delete();
        return redirect()->route('student-achievement.index')->with('success', 'Prestasi siswa berhasil dihapus');
    }

    public function getClassroom(Request $request)
    {
        $classrooms = Classroom::where('school_id', $request->school_id)->get();
        return $this->postSuccessResponse("berhasil Mengambil data kelas", $classrooms);
    }
}
