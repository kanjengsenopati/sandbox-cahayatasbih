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
        $koperasiOutlet = $koperasiId ? Outlet::withTrashed()->find($koperasiId) : null;
        $defaultOutlet = Outlet::withTrashed()->where('id', '!=', $koperasiId)->orderBy('created_at')->first();
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
                    $q->withTrashed()->with([
                        'item' => function ($iq) {
                            $iq->withTrashed()->with([
                                'outlet' => function ($oq) {
                                    $oq->withTrashed();
                                },
                            ]);
                        },
                    ]);
                },
            ]);

        // Enforce Outlet Scope Isolation (including fallback resolution via item.outlet_id when transaction outlet_id is NULL)
        if ($isRestricted) {
            $targetOutletIds = ($request->filled('outlet_id') && $request->outlet_id !== 'all' && in_array($request->outlet_id, $allowedOutletIds))
                ? [$request->outlet_id]
                : $allowedOutletIds;

            $this->applyAllowedOutletsFilter($query, $targetOutletIds, $koperasiId);
        } else {
            $source = $request->input('source', 'all');
            $outletId = $request->input('outlet_id');

            if ($outletId && $outletId !== 'all') {
                $this->applyAllowedOutletsFilter($query, [$outletId], $koperasiId);
            } elseif ($source === 'koperasi') {
                $this->applyKoperasiSourceFilter($query, $koperasiId);
            } elseif ($source === 'outlet') {
                $this->applyOutletSourceFilter($query, $koperasiId);
            }
        }

        // Date Range & Period Filtering
        if ($request->filled('start_date') || $request->filled('end_date')) {
            if ($request->filled('start_date')) {
                $query->whereDate('point_of_sale_transactions.created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('point_of_sale_transactions.created_at', '<=', $request->end_date);
            }
        } elseif ($request->filled('period') && $request->period !== 'all') {
            if ($request->period === 'today') {
                $query->whereDate('point_of_sale_transactions.created_at', Carbon::today());
            } elseif ($request->period === 'week') {
                $query->whereBetween('point_of_sale_transactions.created_at', [Carbon::now()->startOfWeek(Carbon::MONDAY), Carbon::now()->endOfWeek(Carbon::SUNDAY)]);
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
                $nonKoperasiTxIds = self::getNonKoperasiItemTransactionIds($koperasiId);

                if (empty($nonKoperasiTxIds)) {
                    $isOutletSql = "(point_of_sale_transactions.outlet_id IS NOT NULL AND point_of_sale_transactions.outlet_id != ?)";
                    $isKoperasiSql = "(point_of_sale_transactions.outlet_id = ? OR point_of_sale_transactions.outlet_id IS NULL)";
                    $bindings = [$koperasiId, $koperasiId, $koperasiId, $koperasiId];
                } else {
                    $placeholders = implode(',', array_fill(0, count($nonKoperasiTxIds), '?'));
                    $isOutletSql = "((point_of_sale_transactions.outlet_id IS NOT NULL AND point_of_sale_transactions.outlet_id != ?) OR (point_of_sale_transactions.outlet_id IS NULL AND point_of_sale_transactions.id IN ({$placeholders})))";
                    $isKoperasiSql = "(point_of_sale_transactions.outlet_id = ? OR (point_of_sale_transactions.outlet_id IS NULL AND point_of_sale_transactions.id NOT IN ({$placeholders})))";
                    $bindings = array_merge(
                        [$koperasiId], $nonKoperasiTxIds,
                        [$koperasiId], $nonKoperasiTxIds,
                        [$koperasiId], $nonKoperasiTxIds,
                        [$koperasiId], $nonKoperasiTxIds
                    );
                }

                $summaryTotal = (clone $query)->selectRaw("
                    COUNT(id) as total_count,
                    COALESCE(SUM(pay_amount), 0) as total_amount,
                    COALESCE(SUM(CASE WHEN {$isOutletSql} THEN pay_amount ELSE 0 END), 0) as outlet_amount,
                    COALESCE(SUM(CASE WHEN {$isKoperasiSql} THEN pay_amount ELSE 0 END), 0) as koperasi_amount,
                    COUNT(CASE WHEN {$isOutletSql} THEN id END) as outlet_count,
                    COUNT(CASE WHEN {$isKoperasiSql} THEN id END) as koperasi_count
                ", $bindings)->first();

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
                    'avg_amount' => 'Rp ' . number_format($avgAmount, 0, ',', '.'),
                ]);
            }

            $defaults = [];
            if (!$request->has('draw')) {
                $defaults['draw'] = 1;
            }
            if (!$request->has('start')) {
                $defaults['start'] = 0;
            }
            if (!$request->has('length')) {
                $defaults['length'] = 25;
            }
            if (!empty($defaults)) {
                $request->merge($defaults);
                request()->merge($defaults);
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
                ->addColumn('outlet', function ($row) use ($koperasiId, $koperasiOutlet, $defaultOutlet) {
                    [$isKoperasi, $outletLabel] = self::resolveTransactionUnitInfo($row, $koperasiId, $koperasiOutlet, $defaultOutlet);
                    $escapedLabel = e($outletLabel);

                    if ($isKoperasi) {
                        return '<span class="badge px-3 py-2" style="background-color: rgba(37, 99, 235, 0.1); color: #2563eb; border: 1px solid rgba(37, 99, 235, 0.2); border-radius: 8px; font-weight: 600;">
                            <i class="fa-solid fa-store me-1 fs-8 text-primary"></i> ' . $escapedLabel . '
                        </span>';
                    } else {
                        return '<span class="badge px-3 py-2" style="background-color: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 8px; font-weight: 600;">
                            <i class="fa-solid fa-shop me-1 fs-8 text-warning"></i> ' . $escapedLabel . '
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

    /**
     * Get transaction IDs (with outlet_id IS NULL) that contain items belonging to non-Koperasi outlets.
     */
    public static function getNonKoperasiItemTransactionIds(?string $koperasiId, ?array $specificOutletIds = null): array
    {
        $itemQuery = \App\Models\Item::withTrashed()->whereNotNull('outlet_id');
        if ($koperasiId) {
            $itemQuery->where('outlet_id', '!=', $koperasiId);
        }
        if ($specificOutletIds !== null) {
            $nonKoperasiOutlets = $koperasiId ? array_values(array_diff($specificOutletIds, [$koperasiId])) : $specificOutletIds;
            if (empty($nonKoperasiOutlets)) {
                return [];
            }
            $itemQuery->whereIn('outlet_id', $nonKoperasiOutlets);
        }

        $outletItemIds = $itemQuery->pluck('id')->toArray();
        if (empty($outletItemIds)) {
            return [];
        }

        return \Illuminate\Support\Facades\DB::table('point_of_sale_transaction_details')
            ->whereNull('deleted_at')
            ->whereIn('item_id', $outletItemIds)
            ->distinct()
            ->pluck('point_of_sale_transaction_id')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Filter query to transactions belonging to the given allowed outlet IDs,
     * including legacy/null outlet_id transactions resolved via item.outlet_id.
     */
    public static function applyAllowedOutletsFilter($query, array $targetOutletIds, ?string $koperasiId): void
    {
        if (empty($targetOutletIds)) {
            $query->whereRaw('1 = 0');
            return;
        }

        $includesKoperasi = $koperasiId && in_array($koperasiId, $targetOutletIds, true);
        $matchedOutletTxIds = self::getNonKoperasiItemTransactionIds($koperasiId, $targetOutletIds);
        $allNonKoperasiTxIds = $includesKoperasi ? self::getNonKoperasiItemTransactionIds($koperasiId) : [];

        $query->where(function ($q) use ($targetOutletIds, $includesKoperasi, $matchedOutletTxIds, $allNonKoperasiTxIds) {
            $q->whereIn('point_of_sale_transactions.outlet_id', $targetOutletIds);

            if ($includesKoperasi) {
                $q->orWhere(function ($sub) use ($allNonKoperasiTxIds) {
                    $sub->whereNull('point_of_sale_transactions.outlet_id');
                    if (!empty($allNonKoperasiTxIds)) {
                        $sub->whereNotIn('point_of_sale_transactions.id', $allNonKoperasiTxIds);
                    }
                });
            }

            if (!empty($matchedOutletTxIds)) {
                $q->orWhere(function ($sub) use ($matchedOutletTxIds) {
                    $sub->whereNull('point_of_sale_transactions.outlet_id')
                        ->whereIn('point_of_sale_transactions.id', $matchedOutletTxIds);
                });
            }
        });
    }

    /**
     * Filter query strictly to Pondok Mart / Koperasi unit transactions.
     */
    public static function applyKoperasiSourceFilter($query, ?string $koperasiId): void
    {
        $nonKoperasiTxIds = self::getNonKoperasiItemTransactionIds($koperasiId);

        $query->where(function ($q) use ($koperasiId, $nonKoperasiTxIds) {
            $q->where('point_of_sale_transactions.outlet_id', $koperasiId)
              ->orWhere(function ($sub) use ($nonKoperasiTxIds) {
                  $sub->whereNull('point_of_sale_transactions.outlet_id');
                  if (!empty($nonKoperasiTxIds)) {
                      $sub->whereNotIn('point_of_sale_transactions.id', $nonKoperasiTxIds);
                  }
              });
        });
    }

    /**
     * Filter query strictly to non-Koperasi Outlet unit transactions.
     */
    public static function applyOutletSourceFilter($query, ?string $koperasiId): void
    {
        $nonKoperasiTxIds = self::getNonKoperasiItemTransactionIds($koperasiId);

        $query->where(function ($q) use ($koperasiId, $nonKoperasiTxIds) {
            $q->where(function ($sub) use ($koperasiId) {
                $sub->whereNotNull('point_of_sale_transactions.outlet_id')
                    ->where('point_of_sale_transactions.outlet_id', '!=', $koperasiId);
            });

            if (!empty($nonKoperasiTxIds)) {
                $q->orWhere(function ($sub) use ($nonKoperasiTxIds) {
                    $sub->whereNull('point_of_sale_transactions.outlet_id')
                        ->whereIn('point_of_sale_transactions.id', $nonKoperasiTxIds);
                });
            }
        });
    }

    /**
     * Resolve effective unit (Koperasi / Pondok Mart vs Outlet) and display label from database.
     *
     * @return array{0: bool, 1: string} [$isKoperasi, $outletLabel]
     */
    public static function resolveTransactionUnitInfo($row, ?string $koperasiId, ?Outlet $koperasiOutlet = null, ?Outlet $defaultOutlet = null): array
    {
        $resolvedOutlet = $row->outlet;

        if (!$resolvedOutlet && $row->relationLoaded('pointOfSaleTransactionDetails')) {
            // Prioritize any detail item belonging to a non-Koperasi outlet
            foreach ($row->pointOfSaleTransactionDetails as $detail) {
                $itemOutlet = $detail->item?->outlet;
                if ($itemOutlet && $itemOutlet->id !== $koperasiId) {
                    $resolvedOutlet = $itemOutlet;
                    break;
                }
                if ($itemOutlet && !$resolvedOutlet) {
                    $resolvedOutlet = $itemOutlet;
                }
            }
        }

        if (!$resolvedOutlet) {
            $resolvedOutlet = $koperasiOutlet;
        }

        $isKoperasi = ($row->outlet_id && $row->outlet_id === $koperasiId)
            || ($resolvedOutlet && $resolvedOutlet->id === $koperasiId)
            || ($resolvedOutlet && (
                strtoupper((string) $resolvedOutlet->code) === 'KPR' ||
                stripos((string) $resolvedOutlet->name, 'koperasi') !== false ||
                stripos((string) $resolvedOutlet->name, 'mart') !== false
            ))
            || (!$row->outlet_id && !$resolvedOutlet);

        if ($isKoperasi) {
            $dbName = $resolvedOutlet?->name ?? $koperasiOutlet?->name ?? 'KOPERASI';
            $label = stripos($dbName, 'mart') === false ? ($dbName . ' (Pondok Mart)') : $dbName;
            return [true, $label];
        }

        $dbName = $resolvedOutlet?->name ?? $defaultOutlet?->name ?? 'OUTLET';
        return [false, $dbName];
    }
}
