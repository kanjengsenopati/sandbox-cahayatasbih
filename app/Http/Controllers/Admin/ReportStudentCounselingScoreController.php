<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\School;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\StudentCounselingScore;
use App\Exports\StudentCounselingScoreExport;

class ReportStudentCounselingScoreController extends Controller
{
    public function index()
    {
        if (!Auth::user()->can('Manage Laporan Perilaku Siswa')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $schools = School::orderBy('name', 'asc')->get();
        $academicYears = AcademicYear::orderBy('name', 'asc')->get();
        if (request()->ajax()) {
            $data = StudentCounselingScore::with('academicYear', 'student', 'student.classroom', 'student.school')
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
                ->make(true);
        }
        return view('admins.report-student-counseling-score.index', compact('schools', 'academicYears'));
    }

    public function export(Request $request)
    {
        if (!Auth::user()->can('Manage Laporan Perilaku Siswa')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return Excel::download(new StudentCounselingScoreExport(), "Laporan Data Perilaku Siswa." . $request->type);
    }
}
