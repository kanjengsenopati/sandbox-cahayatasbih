<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ReportAppFeeController extends Controller
{
    public function index()
    {
        if (!Auth::user()->can('Manage Laporan Fee Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = Transaction::whereNotNull('app_fee')->where('status', Transaction::STATUS_PAID)
                ->where('app_fee', '>', 0)->latest()->get();
            return DataTables::of($data)
                ->addColumn('date', function ($data) {
                    return $data->created_at->format('d-m-Y H:i:s');
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
        // count app_fee on transaction table in today, week, month, year
        $today = Carbon::now()->format('Y-m-d');
        $week = Carbon::now()->subWeek()->format('Y-m-d');
        $month = Carbon::now()->subMonth()->format('Y-m-d');
        $year = Carbon::now()->subYear()->format('Y-m-d');

        $today_app_fee = Transaction::whereNotNull('app_fee')->whereDate('created_at', $today)->sum('app_fee');
        $week_app_fee = Transaction::whereNotNull('app_fee')->whereDate('created_at', '>=', $week)->sum('app_fee');
        $month_app_fee = Transaction::whereNotNull('app_fee')->whereDate('created_at', '>=', $month)->sum('app_fee');
        $year_app_fee = Transaction::whereNotNull('app_fee')->whereDate('created_at', '>=', $year)->sum('app_fee');
        $app_fee = [
            'today' => $today_app_fee,
            'week' => $week_app_fee,
            'month' => $month_app_fee,
            'year' => $year_app_fee
        ];
        return view('admins.report-app-fee.index', compact('app_fee'));
    }
}
