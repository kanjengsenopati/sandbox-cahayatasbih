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
        $user = Auth::user();
        if (!$user || !method_exists($user, 'isSuperAdmin') || !$user->isSuperAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Maaf, halaman Laporan Audit Log hanya dapat diakses oleh Super Admin.');
        }

        if (request()->ajax()) {
            session()->save();
            $startDate = request()->input('start_date');
            $endDate = request()->input('end_date');

            $data = Activity::with('causer.roles')
                ->latest()
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
                    $humanized = \App\Traits\LogActivityTrait::humanizeActivity($row);
                    return $humanized['description'] ?? $row->description;
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
        $user = Auth::user();
        if (!$user || !method_exists($user, 'isSuperAdmin') || !$user->isSuperAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Maaf, hanya Super Admin yang memiliki akses untuk mengekspor Laporan Audit Log.');
        }

        $type = $request->input('type', 'xlsx');
        return Excel::download(new AuditLogExport(), "Laporan Audit Log." . $type);
    }
}
