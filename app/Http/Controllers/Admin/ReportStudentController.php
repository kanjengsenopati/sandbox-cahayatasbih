<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StudentExport;
use App\Models\School;
use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ReportStudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schools = School::orderBy('name', 'asc')->get();
        $academicYears = AcademicYear::orderBy('name', 'asc')->get();
        if (request()->ajax() && request()->has('school_id')) {
            $data = Student::with('classroom', 'school')
                ->when(request()->school_id, function ($query) {
                    $query->where('school_id', request()->school_id);
                })
                ->when(request()->classroom_id, function ($query) {
                    $query->where('classroom_id', request()->classroom_id);
                })
                ->orderBy('name', 'asc')
                ->get();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionShow = route('report-bill.show', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.show', [
                            'action' => $actionShow, 'label' => 'Cetak',
                            'icon' => 'fa fa-print'
                        ]) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.report-student.index', compact('schools', 'academicYears'));
    }


    public function export(Request $request)
    {
        return Excel::download(new StudentExport(), "Laporan Data User." . $request->type);
    }
}
