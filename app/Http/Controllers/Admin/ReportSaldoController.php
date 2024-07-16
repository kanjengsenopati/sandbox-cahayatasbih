<?php

namespace App\Http\Controllers\Admin;

use App\Models\School;
use App\Models\AcademicYear;
use App\Models\SaldoHistory;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Exports\SaldoStudentExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReportSaldoController extends Controller
{
    public function index()
    {

        if (!Auth::user()->can('Manage Laporan Saldo Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = SaldoHistory::with('student.classroom.school')
                ->when(request()->filled('school_id'), function ($query) {
                    $query->whereHas('student.classroom', function ($query) {
                        $query->where('school_id', request()->school_id);
                    });
                })
                ->when(request()->filled('classroom_id'), function ($query) {
                    $query->whereHas('student', function ($query) {
                        $query->where('classroom_id', request()->classroom_id);
                    });
                })
                ->when(request()->filled('status'), function ($query) {
                    $query->where('status', request()->status);
                })
                ->when(request()->filled('start_date'), function ($query) {
                    $query->whereDate('created_at', '>=', request()->start_date);
                })
                ->when(request()->filled('end_date'), function ($query) {
                    $query->whereDate('created_at', '<=', request()->end_date);
                })
                ->latest()
                ->get();
            if (request()->type == 'total') {
                $total_topup = $data->where('type', 'IN')->sum('amount');
                $total_pengurangan = $data->where('type', 'OUT')->sum('amount');
                $saldo_tersedia = $total_topup - $total_pengurangan;

                return response()->json([
                    'total_topup' => number_format($total_topup, 0, ',', '.'), // number_format($total_topup, 0, ',', '.'
                    'total_pengurangan' => number_format($total_pengurangan, 0, ',', '.'),
                    'saldo_tersedia' => number_format($saldo_tersedia, 0, ',', '.'),
                ]);
            } elseif (request()->type == 'table') {
                return DataTables::of($data)
                    ->editColumn('amount', function ($data) {
                        if ($data->type === 'IN') {
                            return '<span class="badge bg-success">+' . number_format($data->amount, 0, ',', '.') . '</span>';
                        } else {
                            return '<span class="badge bg-danger">-' . number_format($data->amount, 0, ',', '.') . '</span>';
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
                    ->addColumn('date', function ($data) {
                        // Mengatur lokal bahasa Indonesia
                        Carbon::setLocale('id');

                        // Menggunakan translatedFormat untuk format tanggal dalam bahasa Indonesia
                        return $data->created_at->translatedFormat('d F Y' . ' <br>' . 'H:i:s');
                    })
                    ->rawColumns(['amount', 'status', 'date'])
                    ->make(true);
            }
        }

        $schools = School::orderBy('name')->get();
        return view('admins.report-saldo.index', compact('schools'));
    }


    public function export(Request $request)
    {
        return Excel::download(new SaldoStudentExport(), "Laporan Data Saldo Siswa." . $request->type);
    }
}
