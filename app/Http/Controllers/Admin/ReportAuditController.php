<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\AuditLogExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Spatie\Activitylog\Models\Activity;
use Maatwebsite\Excel\Facades\Excel;

class ReportAuditController extends Controller
{
    public function index()
    {
        if (!Auth::user()->can('Manage Laporan Audit Log')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            $startDate = request()->input('start_date');
            $endDate = request()->input('end_date');

            $data = Activity::latest()
                ->whereNotNull('causer_id')
                ->when($startDate, function ($query) use ($startDate) {
                    $query->whereDate('created_at', '>=', $startDate);
                })
                ->when($endDate, function ($query) use ($endDate) {
                    $query->whereDate('created_at', '<=', $endDate);
                });

            return DataTables::of($data)
                ->addColumn('name', function ($row) {
                    return $row->causer->name ?? '<i class="text-danger">User Terhapus</i>';
                })
                ->addColumn('role', function ($row) {
                    return $row->causer ? ($row->causer->roles->first()->name ?? '-') : '-';
                })
                ->editColumn('description', function ($row) {
                    return $row->description;
                })
                ->addColumn('date', function ($row) {
                    return $row->created_at->translatedFormat('d F Y H:i:s');
                })
                ->addColumn('device', function ($row) {
                    $device = $row->getExtraProperty('device') ?? 'Desktop';
                    $ip = $row->getExtraProperty('ip') ?? '-';
                    return "<span class=\"badge bg-secondary\">{$device}</span> <span class=\"text-muted d-block fs-8\">IP: {$ip}</span>";
                })
                ->rawColumns(['name', 'device'])
                ->make(true);
        }

        return view('admins.report-audit.index');
    }

    public function export(Request $request)
    {
        if (!Auth::user()->can('Manage Laporan Audit Log')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk mengekspor halaman tersebut');
        }

        $type = $request->input('type', 'xlsx');
        return Excel::download(new AuditLogExport(), "Laporan Audit Log." . $type);
    }
}
