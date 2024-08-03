<?php

namespace App\Http\Controllers\Admin;

use App\Models\Study;
use App\Models\Student;
use App\Models\StudyGrade;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\StudyGradeRequest;

class StudyGradeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Nilai Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = StudyGrade::with(['study', 'student', 'academicYear', 'semester'])->latest();
            return DataTables::of($data)
                ->editColumn('grade', function ($data) {
                    return $data->grade . ' (' . $data->letter_grade . ')';
                })
                ->editColumn('academic_year_id', function ($data) {
                    return $data->academicYear->name ?? '';
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('study-grade.edit', $data->id);
                    $actionDelete = route('study-grade.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Nilai Santri']) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Nilai Santri']) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.study-grade.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->can('Create Nilai Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.study-grade.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudyGradeRequest $request)
    {
        if (!Auth::user()->can('Create Nilai Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        $data['kkm'] = Study::find($data['study_id'])->kkm;
        $data['classroom_id'] = Student::find($data['student_id'])->classroom_id ?? null;
        StudyGrade::create($data);
        return redirect()->route('study-grade.index')->with('success', 'Nilai berhasil ditambahkan');
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
    public function edit(StudyGrade $studyGrade)
    {
        if (!Auth::user()->can('Edit Nilai Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.study-grade.create-edit', compact('studyGrade'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudyGradeRequest $request, StudyGrade $studyGrade)
    {
        if (!Auth::user()->can('Edit Nilai Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        $studyGrade->update($data);
        return redirect()->route('study-grade.index')->with('success', 'Nilai berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StudyGrade $studyGrade)
    {
        if (!Auth::user()->can('Delete Nilai Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $studyGrade->delete();
        return redirect()->route('study-grade.index')->with('success', 'Nilai berhasil dihapus');
    }
}
