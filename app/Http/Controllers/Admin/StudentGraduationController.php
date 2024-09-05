<?php

namespace App\Http\Controllers\Admin;

use App\Models\School;
use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentGraduationRequest;
use Illuminate\Support\Facades\Auth;

class StudentGraduationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Kelulusan Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = Student::with('user', 'classroom.school')
                ->when(request('school_id'), function ($query) {
                    $query->whereHas('classroom', function ($query) {
                        $query->where('school_id', request('school_id'));
                    });
                })
                ->when(request('classroom_id'), function ($query) {
                    $query->where('classroom_id', request('classroom_id'));
                })
                ->when(request('status'), function ($query) {
                    $query->where('status', request('status'));
                })
                ->whereNot('status', Student::STATUS_GRADUATED)
                ->hasSchool()
                ->latest();
            return DataTables::of($data)
                ->editColumn('saldo', function ($data) {
                    return '<span class="badge bg-success">Rp ' . number_format($data->saldo, 0, ',', '.') . '</span>';
                })
                ->addColumn('classroom', function ($data) {
                    return $data->classroom->name ?? 'Belum ada kelas';
                })
                ->addColumn('school', function ($data) {
                    return $data->classroom->school->name ?? 'Belum ada sekolah';
                })
                ->addColumn('status', function ($data) {
                    switch ($data->status) {
                        case 'ACTIVE':
                            return '<span class="badge bg-success">Aktif</span>';
                        case 'INACTIVE':
                            return '<span class="badge bg-danger">Tidak Aktif</span>';
                        case 'GRADUATED':
                            return '<span class="badge bg-warning">Lulus</span>';
                        case 'TRANSFERRED':
                            return '<span class="badge bg-info">Pindah</span>';
                        case 'DROPPED_OUT':
                            return '<span class="badge bg-secondary">Keluar</span>';
                        default:
                            return '<span class="badge bg-secondary">Tidak Diketahui</span>';
                    }
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('student.edit', $data->id);
                    $actionDelete = route('student.destroy', $data->id);
                    $actionPrint = route('student.generate-student-card', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Kenaikan Kelas']) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Kenaikan Kelas']) .
                        view('components.action.qr-code', ['action' => $actionPrint, 'label' => 'Cetak Kartu']) .
                        "</div>";
                })
                ->rawColumns(['action', 'saldo', 'classroom', 'school', 'status'])
                ->make(true);
        }

        $schools = School::hasSchool()->orderBy('name', 'asc')->get();
        $academicYears = AcademicYear::orderBy('name', 'asc')->get();
        return view('admins.student-graduation.index', compact('schools', 'academicYears'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudentGraduationRequest $request)
    {
        $data = $request->validated();
        $students = Student::whereIn('id', $data['student_ids'])->get();
        foreach ($students as $student) {
            $student->update([
                'status' => Student::STATUS_GRADUATED,
            ]);
        }
        return redirect()->back()->with('success', 'Berhasil Memproses Kelulusan Siswa');
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
