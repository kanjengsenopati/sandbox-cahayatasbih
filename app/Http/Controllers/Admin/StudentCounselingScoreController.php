<?php

namespace App\Http\Controllers\Admin;

use App\Models\School;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentCounselingScore;
use App\Http\Requests\Admin\StudentCounselingScoreRequest;

class StudentCounselingScoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Perilaku Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = StudentCounselingScore::with('student', 'academicYear', 'classroom', 'school')->latest();
            return DataTables::of($data)
                ->addColumn('btnAction', function ($data) {
                    $actionEdit = route('student-counseling-score.edit', $data->id);
                    $actionDelete = route('student-counseling-score.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Perilaku Santri']) . '&nbsp;' .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Perilaku Santri']) .
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
        if (!Auth::user()->can('Create Perilaku Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $schools = School::OrderBy('name', 'asc')->get();
        return view('admins.student-counseling-score.create-edit', compact('schools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudentCounselingScoreRequest $request)
    {
        if (!Auth::user()->can('Create Perilaku Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        StudentCounselingScore::create($request->validated());
        return redirect()->route('student-counseling-score.index')
            ->with('success', 'Skor konseling Santri berhasil ditambahkan');
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
        if (!Auth::user()->can('Edit Perilaku Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $schools = School::OrderBy('name', 'asc')->get();
        return view('admins.student-counseling-score.create-edit', compact('studentCounselingScore', 'schools'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentCounselingScoreRequest $request, StudentCounselingScore $studentCounselingScore)
    {
        if (!Auth::user()->can('Edit Perilaku Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $studentCounselingScore->update($request->validated());
        return redirect()->route('student-counseling-score.index')
            ->with('success', 'Skor konseling Santri berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StudentCounselingScore $studentCounselingScore)
    {
        if (!Auth::user()->can('Delete Perilaku Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $studentCounselingScore->delete();
        return redirect()->route('student-counseling-score.index')
            ->with('success', 'Skor konseling Santri berhasil dihapus');
    }
}
