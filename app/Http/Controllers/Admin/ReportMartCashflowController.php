<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\PointOfSaleTransaction;
use App\Services\MartUserScopeService;
use App\Services\OutletContextService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;
use App\Exports\ReportMartCashflowExport;

class ReportMartCashflowController extends Controller
{
    /**
     * Check authorization for Arus Kas Mart module.
     */
    protected function checkAuthorization()
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->can('Manage Laporan Pos Multi Outlet') ||
               $user->can('Manage Laporan Pos Kasir') ||
               $user->can('POS Outlet') ||
               $user->can('Manage Pos Kasir') ||
               $user->isSuperAdmin() ||
               (method_exists($user, 'isKoordinatorCahayaMart') && $user->isKoordinatorCahayaMart()) ||
               (method_exists($user, 'isKoordinatorPondokMart') && $user->isKoordinatorPondokMart());
    }

    /**
     * Display a listing of Arus Kas Mart transactions.
     */
    public function index(Request $request)
    {
        if (!$this->checkAuthorization()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Maaf, Anda tidak memiliki akses untuk modul ini.'], 403);
            }
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        session()->save();

        $user = Auth::user();
        $koperasiId = OutletContextService::getKoperasiOutletId();
        $isRestricted = MartUserScopeService::isUserOutletRestricted($user);
        $allowedOutletIds = MartUserScopeService::getAllowedOutletIds($user);

        // Build base query strictly for Saldo student transactions
        $query = PointOfSaleTransaction::query()
            ->where('point_of_sale_transactions.type', PointOfSaleTransaction::TYPE_SANTRI)
            ->whereNotNull('point_of_sale_transactions.student_id')
            ->with([
                'student' => function ($q) {
                    $q->withTrashed()->with('classroom');
                },
                'outlet' => function ($q) {
                    $q->withTrashed();
                },
                'admins' => function ($q) {
                    $q->withTrashed();
                },
                'pointOfSaleTransactionDetails' => function ($q) {
                    $q->withTrashed()->with('item');
                },
            ]);

        // Enforce Outlet Scope Isolation
        if ($isRestricted) {
            if ($request->filled('outlet_id') && in_array($request->outlet_id, $allowedOutletIds)) {
                $query->where('point_of_sale_transactions.outlet_id', $request->outlet_id);
            } else {
                $query->whereIn('point_of_sale_transactions.outlet_id', $allowedOutletIds);
            }
        } else {
            $source = $request->input('source', 'all');
            $outletId = $request->input('outlet_id');

            if ($outletId && $outletId !== 'all') {
                $query->where('point_of_sale_transactions.outlet_id', $outletId);
            } elseif ($source === 'koperasi') {
                $query->where('point_of_sale_transactions.outlet_id', $koperasiId);
            } elseif ($source === 'outlet') {
                $query->where('point_of_sale_transactions.outlet_id', '!=', $koperasiId);
            }
        }

        // Date Range & Period Filtering
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereDate('point_of_sale_transactions.created_at', '>=', $request->start_date)
                  ->whereDate('point_of_sale_transactions.created_at', '<=', $request->end_date);
        } elseif ($request->filled('period') && $request->period !== 'all') {
            if ($request->period === 'today') {
                $query->whereDate('point_of_sale_transactions.created_at', Carbon::today());
            } elseif ($request->period === 'week') {
                $query->whereBetween('point_of_sale_transactions.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            } elseif ($request->period === 'month') {
                $query->whereBetween('point_of_sale_transactions.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
            }
        }

        // Status Filtering
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('point_of_sale_transactions.status', $request->status);
        }

        // Handle AJAX DataTables & Summary Requests
        if ($request->ajax() || $request->wantsJson()) {
            if ($request->data === 'summary') {
                $summaryTotal = (clone $query)->selectRaw('
                    COUNT(id) as total_count,
                    COALESCE(SUM(pay_amount), 0) as total_amount,
                    COALESCE(SUM(CASE WHEN outlet_id != ? THEN pay_amount ELSE 0 END), 0) as outlet_amount,
                    COALESCE(SUM(CASE WHEN outlet_id = ? THEN pay_amount ELSE 0 END), 0) as koperasi_amount,
                    COUNT(CASE WHEN outlet_id != ? THEN id END) as outlet_count,
                    COUNT(CASE WHEN outlet_id = ? THEN id END) as koperasi_count
                ', [$koperasiId, $koperasiId, $koperasiId, $koperasiId])->first();

                $totalCount = (int) ($summaryTotal->total_count ?? 0);
                $totalAmount = (float) ($summaryTotal->total_amount ?? 0);
                $avgAmount = $totalCount > 0 ? round($totalAmount / $totalCount) : 0;

                return response()->json([
                    'total_amount' => 'Rp ' . number_format($totalAmount, 0, ',', '.'),
                    'total_count' => number_format($totalCount, 0, ',', '.') . ' Transaksi',
                    'outlet_amount' => 'Rp ' . number_format($summaryTotal->outlet_amount ?? 0, 0, ',', '.'),
                    'outlet_count' => number_format($summaryTotal->outlet_count ?? 0, 0, ',', '.') . ' Transaksi',
                    'koperasi_amount' => 'Rp ' . number_format($summaryTotal->koperasi_amount ?? 0, 0, ',', '.'),
                    'koperasi_count' => number_format($summaryTotal->koperasi_count ?? 0, 0, ',', '.') . ' Transaksi',
                ]);
            }

            if (!$request->has('draw')) {
                $request->merge(['draw' => 1]);
            }
            if (!$request->has('start')) {
                $request->merge(['start' => 0]);
            }
            if (!$request->has('length')) {
                $request->merge(['length' => 25]);
            }

            $data = $query->latest('point_of_sale_transactions.created_at');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('payment_code', function ($row) {
                    return '<span class="fw-bold text-gray-800">' . e($row->payment_code ?? '-') . '</span>';
                })
                ->addColumn('student', function ($row) {
                    if (!$row->student) {
                        return '<span class="text-muted fs-7">Siswa Terhapus</span>';
                    }
                    $name = e($row->student->name);
                    $nis = e($row->student->nis ?? '-');
                    $classroom = e($row->student->classroom?->name ?? '-');
                    $avatar = $row->student->avatar ? asset($row->student->avatar) : asset('assets/media/avatars/default.png');

                    return '<div class="d-flex align-items-center gap-3">
                        <img src="' . $avatar . '" alt="avatar" class="rounded-circle" style="width: 34px; height: 34px; object-fit: cover;">
                        <div class="d-flex flex-column">
                            <span class="text-gray-900 fw-bold fs-7">' . $name . '</span>
                            <span class="text-muted fs-8">NIS: ' . $nis . ' &bull; Kelas: ' . $classroom . '</span>
                        </div>
                    </div>';
                })
                ->addColumn('outlet', function ($row) use ($koperasiId) {
                    $outletName = e($row->outlet?->name ?? 'Outlet');
                    $isKoperasi = $row->outlet_id === $koperasiId;

                    if ($isKoperasi) {
                        return '<span class="badge px-3 py-2" style="background-color: rgba(37, 99, 235, 0.1); color: #2563eb; border: 1px solid rgba(37, 99, 235, 0.2); border-radius: 8px; font-weight: 600;">
                            <i class="fa-solid fa-store me-1 fs-8 text-primary"></i> ' . $outletName . ' (Pondok Mart)
                        </span>';
                    } else {
                        return '<span class="badge px-3 py-2" style="background-color: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 8px; font-weight: 600;">
                            <i class="fa-solid fa-shop me-1 fs-8 text-warning"></i> ' . $outletName . '
                        </span>';
                    }
                })
                ->addColumn('details', function ($row) {
                    $details = $row->pointOfSaleTransactionDetails;
                    if (!$details || $details->isEmpty()) {
                        return '<span class="text-muted fs-8">-</span>';
                    }
                    $totalItems = $details->count();
                    $visibleLimit = 2;

                    $renderItem = function ($detail) {
                        $name = e($detail->item?->name ?? 'Barang');
                        $qty = (int) $detail->quantity;
                        return '<div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge badge-light-primary fw-bolder px-2 py-0.5 fs-8 text-primary" style="border-radius: 6px;">' . $qty . 'x</span>
                            <span class="text-gray-800 fw-semibold fs-7 text-truncate" title="' . $name . '" style="max-width: 180px;">' . $name . '</span>
                        </div>';
                    };

                    $html = '<div class="pos-item-stack d-flex flex-column">';
                    foreach ($details->take($visibleLimit) as $detail) {
                        $html .= $renderItem($detail);
                    }

                    if ($totalItems > $visibleLimit) {
                        $remaining = $details->slice($visibleLimit);
                        $collapseId = 'collapse-mart-items-' . $row->id;
                        $remainingCount = $totalItems - $visibleLimit;

                        $html .= '<div class="collapse" id="' . $collapseId . '">';
                        foreach ($remaining as $detail) {
                            $html .= $renderItem($detail);
                        }
                        $html .= '</div>';
                        $html .= '<a class="text-primary fw-bold cursor-pointer text-decoration-none fs-8 mt-1 d-inline-flex align-items-center" data-bs-toggle="collapse" href="#' . $collapseId . '" role="button" aria-expanded="false" onclick="event.stopPropagation();">
                            +' . $remainingCount . ' item lainnya <i class="fas fa-chevron-down fs-9 ms-1"></i>
                        </a>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('pay_amount', function ($row) {
                    return '<span class="fw-bolder fs-6" style="color: #10b981;">Rp ' . number_format($row->pay_amount, 0, ',', '.') . '</span>';
                })
                ->addColumn('date', function ($row) {
                    return Carbon::parse($row->created_at)->translatedFormat('d F Y H:i');
                })
                ->addColumn('status', function ($row) {
                    if ($row->status === PointOfSaleTransaction::STATUS_SUCCESS) {
                        return '<span class="badge px-3 py-1.5" style="background-color: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 8px; font-weight: 600;">Sukses</span>';
                    } elseif ($row->status === PointOfSaleTransaction::STATUS_PENDING) {
                        return '<span class="badge px-3 py-1.5" style="background-color: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 8px; font-weight: 600;">Pending</span>';
                    } else {
                        return '<span class="badge px-3 py-1.5" style="background-color: rgba(220, 38, 38, 0.1); color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.2); border-radius: 8px; font-weight: 600;">Gagal</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $invoiceUrl = route('order-item-history.print', $row->id);
                    return '<div class="d-flex items-center gap-2 justify-content-center">
                        <a href="' . $invoiceUrl . '" target="_blank" class="btn btn-icon btn-light-primary w-32px h-32px rounded-circle" title="Cetak Struk">
                            <i class="fa-solid fa-print fs-6"></i>
                        </a>
                    </div>';
                })
                ->rawColumns(['payment_code', 'student', 'outlet', 'details', 'pay_amount', 'status', 'action'])
                ->make(true);
        }

        // Available outlets for dropdown
        if ($isRestricted) {
            $outlets = Outlet::whereIn('id', $allowedOutletIds)->orderBy('name')->get();
            $defaultSource = 'outlet';
        } else {
            $outlets = Outlet::where('is_active', true)->orderBy('name')->get();
            $defaultSource = 'all';
        }

        return view('admins.report-mart-cashflow.index', [
            'isRestricted' => $isRestricted,
            'allowedOutletIds' => $allowedOutletIds,
            'outlets' => $outlets,
            'koperasiId' => $koperasiId,
            'defaultSource' => $defaultSource,
        ]);
    }

    /**
     * Export Arus Kas Mart transactions to Excel.
     */
    public function export(Request $request)
    {
        if (!$this->checkAuthorization()) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk export laporan ini');
        }

        $filename = 'Laporan_Arus_Kas_Mart_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new ReportMartCashflowExport($request), $filename);
    }
}
