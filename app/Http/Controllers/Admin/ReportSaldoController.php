<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\School;
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
        return view('admins.report-saldo.index', compact('outlets'));
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
            ->when(request()->filled('outlet_id'), function ($query) {
                $query->where('outlet_id', request()->outlet_id);
            })
            ->when(request()->filled('start_date'), function ($query) {
                $query->whereDate('created_at', '>=', request()->start_date);
            })
            ->when(request()->filled('end_date'), function ($query) {
                $query->whereDate('created_at', '<=', request()->end_date);
            })
            ->latest();
    }

    protected function calculateTotals($data)
    {
        $total_topup = $data->where('type', SaldoHistory::TYPE_IN)->where('status', SaldoHistory::STATUS_SUCCESS)->sum('amount');
        $data = $this->querySaldoHistory();
        $total_pengurangan = $data->whereIn('type', [SaldoHistory::TYPE_OUT, SaldoHistory::TYPE_WITHDRAW])->where('status', SaldoHistory::STATUS_SUCCESS)->sum('amount');
        $saldo_tersedia = $this->calculateAvailableBalance();

        return response()->json([
            'total_topup' => number_format($total_topup, 0, ',', '.'),
            'total_pengurangan' => number_format($total_pengurangan, 0, ',', '.'),
            'saldo_tersedia' => number_format($saldo_tersedia, 0, ',', '.'),
        ]);
    }

    protected function calculateAvailableBalance()
    {
        return Student::sum('saldo');
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
                return $data->created_at->translatedFormat('d F Y' . ' <br>' . 'H:i:s');
            })
            ->editColumn('balance_before', function ($data) {
                return '<span class="badge bg-info">Rp ' . number_format($data->balance_before, 0, ',', '.') . '</span>';
            })
            ->editColumn('balance_after', function ($data) {
                return '<span class="badge bg-info">Rp ' . number_format($data->balance_after, 0, ',', '.') . '</span>';
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
