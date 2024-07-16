<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\School;
use App\Models\Student;
use App\Models\Tahfidz;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Exports\TahfidzExport;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportTahfidzController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Laporan Tahfidz')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $schools = School::hasSchool()->orderBy('name', 'asc')->get();
        $academicYears = AcademicYear::orderBy('name', 'asc')->get();
        if (request()->ajax()) {
            $data = Tahfidz::with('student', 'student.classroom', 'student.school')
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
                ->hasSchool()
                ->latest();
            return DataTables::of($data)
                ->editColumn('deposit_date', function ($data) {
                    return Carbon::parse($data->deposit_date)->format('d M Y');
                })
                ->editColumn('link', function ($data) {
                    return "<a href='$data->link' target='_blank'><i class='fas fa-external-link-alt'></i></a>";
                })
                ->rawColumns(['deposit_date', 'link'])
                ->make(true);
        }
        return view('admins.report-tahfidz.index', compact('schools', 'academicYears'));
    }

    public function export(Request $request)
    {
        if (!Auth::user()->can('Manage Laporan Tahfidz')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return Excel::download(new TahfidzExport(), "Laporan Data Tahfidz." . $request->type);
    }
}
