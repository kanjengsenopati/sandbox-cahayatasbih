<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Support\Facades\Auth;

class ReportAppFeeController extends Controller
{
    public function index()
    {
        if (!Auth::user()->can('Manage Laporan Fee Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $start_date = request('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $end_date = request('end_date', Carbon::now()->format('Y-m-d'));
        if (request()->ajax() && request('type') == 'app_fee') {
            session()->save();
            $data = Transaction::whereNotNull('app_fee')
                ->where('status', Transaction::STATUS_PAID)
                ->where('app_fee', '>', 0)
                ->when($start_date, function ($query) use ($start_date, $end_date) {
                    $query->whereDate('created_at', '>=', $start_date)
                        ->whereDate('created_at', '<=', $end_date);
                })
                ->when(request('school'), function ($query) {
                    $studentIds = \App\Models\Student::join('classrooms', 'students.classroom_id', '=', 'classrooms.id')
                        ->where('classrooms.school_id', request('school'))
                        ->pluck('students.id');
                    $query->whereIn('student_id', $studentIds);
                })
                ->latest();
            return DataTables::of($data)
                ->addColumn('date', function ($data) {
                    Carbon::setLocale('id');
                    $createdAt = Carbon::parse($data->created_at);
                    return $createdAt->translatedFormat('d F Y H:i');
                })
                ->editColumn('app_fee', function ($data) {
                    return 'Rp ' . number_format($data->app_fee, 0, ',', '.');
                })
                ->editColumn('type', function ($data) {
                    if ($data->type == Transaction::TYPE_BILL) {
                        return '<span class="badge badge-success">Tagihan</span>';
                    } elseif ($data->type == Transaction::TYPE_SALDO) {
                        return '<span class="badge badge-primary">Saldo</span>';
                    } elseif ($data->type == Transaction::TYPE_SAVING) {
                        return '<span class="badge badge-info">Tabungan</span>';
                    } elseif ($data->type == Transaction::TYPE_PPDB) {
                        return '<span class="badge badge-warning">PPDB</span>';
                    }
                })
                ->rawColumns(['app_fee', 'date', 'type'])
                ->make(true);
        }

        if (request()->ajax() && request('type') == 'summary') {
            $data = Transaction::whereNotNull('app_fee')
                ->where('status', Transaction::STATUS_PAID)
                ->where('app_fee', '>', 0)
                ->when($start_date, function ($query) use ($start_date, $end_date) {
                    $query->whereDate('created_at', '>=', $start_date)
                        ->whereDate('created_at', '<=', $end_date);
                })
                ->when(request('school'), function ($query) {
                    $studentIds = \App\Models\Student::join('classrooms', 'students.classroom_id', '=', 'classrooms.id')
                        ->where('classrooms.school_id', request('school'))
                        ->pluck('students.id');
                    $query->whereIn('student_id', $studentIds);
                });

            // Calculate totals in a single fast SQL query
            $totals = $data->selectRaw("
                SUM(app_fee) as total_app_fee,
                SUM(CASE WHEN type = '" . Transaction::TYPE_BILL . "' THEN app_fee ELSE 0 END) as total_bill,
                SUM(CASE WHEN type = '" . Transaction::TYPE_SALDO . "' THEN app_fee ELSE 0 END) as total_saldo,
                SUM(CASE WHEN type = '" . Transaction::TYPE_SAVING . "' THEN app_fee ELSE 0 END) as total_saving
            ")->first();

            return response()->json([
                'total' => number_format($totals->total_app_fee ?? 0, 0, ',', '.'),
                'bill' => number_format($totals->total_bill ?? 0, 0, ',', '.'),
                'saldo' => number_format($totals->total_saldo ?? 0, 0, ',', '.'),
                'saving' => number_format($totals->total_saving ?? 0, 0, ',', '.')
            ]);
        }
        $schools = School::hasSchool()->orderBy('name')->get();
        return view('admins.report-app-fee.index', compact('schools'));
    }
}
