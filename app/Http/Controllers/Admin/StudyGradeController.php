<?php

namespace App\Http\Controllers\Admin;

use App\Models\StudyGrade;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudyGradeRequest;
use App\Models\Student;
use App\Models\Study;

class StudyGradeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = StudyGrade::with(['study', 'student', 'academicYear', 'semester'])->latest()->get();
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
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
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
        return view('admins.study-grade.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudyGradeRequest $request)
    {
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
        return view('admins.study-grade.create-edit', compact('studyGrade'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudyGradeRequest $request, StudyGrade $studyGrade)
    {
        $data = $request->validated();
        $studyGrade->update($data);
        return redirect()->route('study-grade.index')->with('success', 'Nilai berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StudyGrade $studyGrade)
    {
        $studyGrade->delete();
        return redirect()->route('study-grade.index')->with('success', 'Nilai berhasil dihapus');
    }
}
