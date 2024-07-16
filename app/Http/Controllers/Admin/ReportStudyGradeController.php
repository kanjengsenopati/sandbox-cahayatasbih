<?php

namespace App\Http\Controllers\Admin;

use App\Models\School;
use App\Models\StudyGrade;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Exports\StudyGradeExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\StudentCounselingScore;

class ReportStudyGradeController extends Controller
{
    public function index()
    {
        if (!Auth::user()->can('Manage Laporan Nilai Akademik')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $schools = School::orderBy('name', 'asc')->get();
        $academicYears = AcademicYear::orderBy('name', 'asc')->get();
        if (request()->ajax()) {
            $data = StudyGrade::with(['student', 'academicYear', 'semester', 'study', 'classroom'])
                ->when(request()->school_id, function ($query) {
                    $query->whereHas('student', function ($query) {
                        $query->whereHas('classroom', function ($query) {
                            $query->where('school_id', request()->school_id);
                        });
                    });
                })
                ->when(request()->classroom_id, function ($query) {
                    $query->whereHas('student', function ($query) {
                        $query->where('classroom_id', request()->classroom_id);
                    });
                })
                ->latest()
                ->get();
            return DataTables::of($data)
                ->editColumn('grade', function ($data) {
                    if ($data->grade >= $data->kkm) {
                        return '<span class="text-success">' . $data->grade . ' (' . $data->letter_grade . ')</span>';
                    } else {
                        return '<span class="text-danger">' . $data->grade . ' (' . $data->letter_grade . ')</span>';
                    }
                })
                ->editColumn('academic_year_id', function ($data) {
                    return $data->academicYear->name ?? '';
                })
                ->rawColumns(['grade', 'academic_year_id'])
                ->make(true);
        }
        return view('admins.report-study-grade.index', compact('schools', 'academicYears'));
    }

    public function export(Request $request)
    {
        if (!Auth::user()->can('Manage Laporan Nilai Akademik')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return Excel::download(new StudyGradeExport(), "Laporan Data Raport Nilai Akademik." . $request->type);
    }
}
