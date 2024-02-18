<?php

namespace App\Http\Controllers\Admin;

use App\Models\School;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Models\StudentCounselingScore;
use App\Http\Requests\Admin\StudentCounselingScoreRequest;

class StudentCounselingScoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = StudentCounselingScore::with('student', 'academicYear', 'classroom', 'school')->latest()->get();
            return DataTables::of($data)
                ->addColumn('btnAction', function ($data) {
                    $actionEdit = route('student-counseling-score.edit', $data->id);
                    $actionDelete = route('student-counseling-score.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['btnAction', 'link'])
                ->make(true);
        }
        return view('admins.student-counseling-score.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $schools = School::OrderBy('name', 'asc')->get();
        return view('admins.student-counseling-score.create-edit', compact('schools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudentCounselingScoreRequest $request)
    {
        StudentCounselingScore::create($request->validated());
        return redirect()->route('student-counseling-score.index')
            ->with('success', 'Skor konseling siswa berhasil ditambahkan');
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
    public function edit(StudentCounselingScore $studentCounselingScore)
    {
        $schools = School::OrderBy('name', 'asc')->get();
        return view('admins.student-counseling-score.create-edit', compact('studentCounselingScore', 'schools'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentCounselingScoreRequest $request, StudentCounselingScore $studentCounselingScore)
    {
        $studentCounselingScore->update($request->validated());
        return redirect()->route('student-counseling-score.index')
            ->with('success', 'Skor konseling siswa berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StudentCounselingScore $studentCounselingScore)
    {
        $studentCounselingScore->delete();
        return redirect()->route('student-counseling-score.index')
            ->with('success', 'Skor konseling siswa berhasil dihapus');
    }
}
