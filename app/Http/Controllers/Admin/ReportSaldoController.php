<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\School;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\SaldoHistory;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Exports\SaldoStudentExport;
use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportSaldoController extends Controller
{

    public function index()
    {
        if (!Auth::user()->can('Manage Laporan Saldo Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            return $this->handleAjaxRequest();
        }

        $outlets = Outlet::orderBy('name', 'asc')->get();
        $schools = School::orderBy('name', 'asc')->get();
        $classrooms = Classroom::orderBy('name', 'asc')->get();
        return view('admins.report-saldo.index', compact('outlets', 'schools', 'classrooms'));
    }

    protected function handleAjaxRequest()
    {
        $data = $this->querySaldoHistory();

        if (request()->type == 'total') {
            return $this->calculateTotals($data);
        } elseif (request()->type == 'table') {
            return $this->formatDataTable($data);
        }

        return response()->json(['error' => 'Invalid request type'], 400);
    }

    protected function querySaldoHistory()
    {
        return SaldoHistory::with(['student.classroom.school', 'outlet'])
            ->where('status', SaldoHistory::STATUS_SUCCESS)
            ->when(request()->filled('outlet_id'), function ($query) {
                $query->where('outlet_id', request()->outlet_id);
            })
            ->when(request()->filled('school_id'), function ($query) {
                $query->whereHas('student.classroom', function ($q) {
                    $q->where('school_id', request()->school_id);
                });
            })
            ->when(request()->filled('classroom_id'), function ($query) {
                $query->whereHas('student', function ($q) {
                    $q->where('classroom_id', request()->classroom_id);
                });
            })
            ->when(request()->filled('start_date'), function ($query) {
                $query->whereDate('created_at', '>=', request()->start_date);
            })
            ->when(request()->filled('end_date'), function ($query) {
                $query->whereDate('created_at', '<=', request()->end_date);
            });
    }

    protected function calculateTotals($data)
    {
        $totals = (clone $data)->selectRaw("
            SUM(CASE WHEN type = ? AND status = ? THEN amount ELSE 0 END) as total_topup,
            SUM(CASE WHEN type IN (?, ?) AND status = ? THEN amount ELSE 0 END) as total_pengurangan
        ", [
            SaldoHistory::TYPE_IN, SaldoHistory::STATUS_SUCCESS,
            SaldoHistory::TYPE_OUT, SaldoHistory::TYPE_WITHDRAW, SaldoHistory::STATUS_SUCCESS
        ])->first();

        $saldo_tersedia = $this->calculateAvailableBalance();

        return response()->json([
            'total_topup' => number_format($totals->total_topup ?? 0, 0, ',', '.'),
            'total_pengurangan' => number_format($totals->total_pengurangan ?? 0, 0, ',', '.'),
            'saldo_tersedia' => number_format($saldo_tersedia, 0, ',', '.'),
        ]);
    }

    protected function calculateAvailableBalance()
    {
        return Student::when(request()->filled('school_id'), function ($q) {
                $q->whereHas('classroom', function ($c) {
                    $c->where('school_id', request()->school_id);
                });
            })
            ->when(request()->filled('classroom_id'), function ($q) {
                $q->where('classroom_id', request()->classroom_id);
            })
            ->sum('saldo');
    }

    protected function formatDataTable($data)
    {
        return DataTables::of($data)
            ->filterColumn('student.name', function ($query, $keyword) {
                $query->whereHas('student', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('nis', 'like', "%{$keyword}%")
                      ->orWhere('nisn', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('student.nis', function ($query, $keyword) {
                $query->whereHas('student', function ($q) use ($keyword) {
                    $q->where('nis', 'like', "%{$keyword}%")
                      ->orWhere('nisn', 'like', "%{$keyword}%")
                      ->orWhere('name', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('amount', function ($data) {
                return $this->formatAmountColumn($data);
            })
            ->editColumn('status', function ($data) {
                return $this->formatStatusColumn($data);
            })
            ->addColumn('date', function ($data) {
                return '<span data-order="' . $data->created_at->timestamp . '">' 
                    . $data->created_at->translatedFormat('d F Y' . ' <br>' . 'H:i:s') 
                    . '</span>';
            })
            ->orderColumn('date', function ($query, $order) {
                $query->orderBy('created_at', $order);
            })
            ->editColumn('balance_before', function ($data) {
                $val = (float) ($data->balance_before ?? 0);
                return '<span data-order="' . $val . '">' . \format_saldo_badge($data->balance_before) . '</span>';
            })
            ->orderColumn('balance_before', function ($query, $order) {
                $query->orderBy('balance_before', $order);
            })
            ->editColumn('balance_after', function ($data) {
                $val = (float) ($data->balance_after ?? 0);
                return '<span data-order="' . $val . '">' . \format_saldo_badge($data->balance_after) . '</span>';
            })
            ->orderColumn('balance_after', function ($query, $order) {
                $query->orderBy('balance_after', $order);
            })
            ->addColumn('action', function ($data) {
                $actionDelete = route('report-saldo.destroy', $data->id);
                return "<div class='d-flex gap-2 flex-nowrap justify-content-center'>" .
                    // add icon delete
                    view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Laporan Saldo Santri']) .
                    "</div>";
            })
            ->rawColumns(['amount', 'status', 'date', 'action', 'balance_before', 'balance_after'])
            ->make(true);
    }

    protected function formatAmountColumn($data)
    {
        $badgeClass = $data->type === 'IN' ? 'bg-success' : 'bg-danger';
        $sign = $data->type === 'IN' ? '+' : '-';
        return "<span class=\"badge {$badgeClass}\">{$sign}" . number_format($data->amount, 0, ',', '.') . "</span>";
    }

    protected function formatStatusColumn($data)
    {
        $badgeClass = $data->status === SaldoHistory::STATUS_SUCCESS ? 'bg-success' : ($data->status === SaldoHistory::STATUS_PENDING ? 'bg-warning' : 'bg-danger');
        return "<span class=\"badge {$badgeClass}\">{$data->status}</span>";
    }


    public function export(Request $request)
    {
        return Excel::download(new SaldoStudentExport(), "Laporan Data Saldo Siswa." . $request->type);
    }

    public function destroy($id)
    {

        if (!Auth::user()->can('Delete Laporan Saldo Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $saldoHistory = SaldoHistory::findOrFail($id);

        $saldoHistory->delete();
        return redirect()->back()->with('success', 'Data Riwayat Saldo Santri berhasil dihapus');
    }
}
