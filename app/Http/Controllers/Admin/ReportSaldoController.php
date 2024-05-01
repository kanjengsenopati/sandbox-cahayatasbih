<?php

namespace App\Http\Controllers\Admin;

use App\Models\School;
use App\Models\AcademicYear;
use App\Models\SaldoHistory;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Exports\SaldoStudentExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ReportSaldoController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $data = SaldoHistory::with('student.classroom.school')
                ->when(request()->school_id, function ($query) {
                    $query->whereHas('student', function ($query) {
                        $query->whereHas('classroom', function ($query) {
                            $query->where('school_id', request()->school_id);
                        });
                    })
                        ->when(request()->classroom_id, function ($query) {
                            $query->whereHas('student', function ($query) {
                                $query->where('classroom_id', request()->classroom_id);
                            });
                        });
                })
                ->when(request()->status, function ($query) {
                    $query->where('status', request()->status);
                })
                ->latest()
                ->get();
            return DataTables::of($data)
                ->editColumn('amount', function ($data) {
                    if ($data->type === 'IN') {
                        return '<span class="badge bg-success">+' . $data->amount . '</span>';
                    } else {
                        return '<span class="badge bg-danger">-' . $data->amount . '</span>';
                    }
                })
                ->editColumn('status', function ($data) {
                    if ($data->status === SaldoHistory::STATUS_SUCCESS) {
                        return '<span class="badge bg-success">' . $data->status . '</span>';
                    } elseif ($data->status === SaldoHistory::STATUS_PENDING) {
                        return '<span class="badge bg-warning">' . $data->status . '</span>';
                    } else {
                        return '<span class="badge bg-danger">' . $data->status . '</span>';
                    }
                })
                ->rawColumns(['amount', 'status'])
                ->make(true);
        }
        $schools = School::orderBy('name', 'asc')->get();
        return view('admins.report-saldo.index', compact('schools'));
    }

    public function export(Request $request)
    {
        return Excel::download(new SaldoStudentExport(), "Laporan Data Saldo Siswa." . $request->type);
    }
}
