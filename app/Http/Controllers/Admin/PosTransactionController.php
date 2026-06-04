<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Outlet;
use Illuminate\Http\Request;
use App\Models\OutletHandover;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PointOfSaleTransaction;

class PosTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!Auth::user()->can('Manage Laporan Pos Multi Outlet') && !Auth::user()->can('Manage Laporan Pos Kasir')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        // Tentukan outlet_ids berdasarkan hak akses admin yang login
        $authOutletIds = auth()->user()->getOutletIds();
        $hasOutletRestriction = count($authOutletIds) > 0;
        $outletId = $request->input('outlet_id');

        if ($request->ajax()) {
            if ($request->type == 'top-items') {
                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');
                
                if ($hasOutletRestriction) {
                    $queryOutletId = $outletId && in_array($outletId, $authOutletIds) ? $outletId : $authOutletIds;
                } else {
                    $queryOutletId = $outletId;
                }

                $data = \App\Models\PointOfSaleTransactionDetail::query()
                    ->join('point_of_sale_transactions', 'point_of_sale_transaction_details.point_of_sale_transaction_id', '=', 'point_of_sale_transactions.id')
                    ->select('point_of_sale_transaction_details.item_id', \DB::raw('COUNT(point_of_sale_transaction_details.id) as total_transaction'))
                    ->where('point_of_sale_transactions.status', PointOfSaleTransaction::STATUS_SUCCESS)
                    ->whereNull('point_of_sale_transaction_details.deleted_at')
                    ->whereNull('point_of_sale_transactions.deleted_at')
                    ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                        $q->whereDate('point_of_sale_transactions.created_at', '>=', $startDate)
                          ->whereDate('point_of_sale_transactions.created_at', '<=', $endDate);
                    })
                    ->when($queryOutletId, function($q) use ($queryOutletId) {
                        if (is_array($queryOutletId)) {
                            $q->whereIn('point_of_sale_transactions.outlet_id', $queryOutletId);
                        } else {
                            $q->where('point_of_sale_transactions.outlet_id', $queryOutletId);
                        }
                    })
                    ->groupBy('point_of_sale_transaction_details.item_id')
                    ->orderByDesc('total_transaction')
                    ->take(10)
                    ->with('item')
                    ->get()
                    ->map(function ($detail) {
                        return [
                            'name' => $detail->item?->name ?? 'Barang Terhapus',
                            'total_transaction' => $detail->total_transaction
                        ];
                    });

                return DataTables::of($data)->make(true);
            }

            // Base query untuk tabel transaksi
            $data = PointOfSaleTransaction::with(['outlet', 'student', 'student.classroom', 'admins', 'pointOfSaleTransactionDetails.item'])
                ->when($hasOutletRestriction, function ($q) use ($authOutletIds) {
                    $q->whereIn('outlet_id', $authOutletIds);
                })
                ->when(!$hasOutletRestriction && $request->filled('outlet_id'), function ($q) use ($request) {
                    $q->where('outlet_id', $request->outlet_id);
                })
                ->when($hasOutletRestriction && $request->filled('outlet_id'), function ($q) use ($request, $authOutletIds) {
                    // Jika admin multi-outlet memilih filter outlet tertentu, pastikan outlet itu ada dalam daftar yang diassign
                    if (in_array($request->outlet_id, $authOutletIds)) {
                        $q->where('outlet_id', $request->outlet_id);
                    }
                })
                ->when($request->filled('start_date') && $request->filled('end_date'), function ($query) use ($request) {
                    $query->whereDate('created_at', '>=', $request->start_date)
                        ->whereDate('created_at', '<=', $request->end_date);
                })
                ->when($request->filled('status'), function ($query) use ($request) {
                    $query->where('status', $request->status);
                })
                ->latest();

            // Hitung total ringkasan terfilter
            if ($request->data == 'total') {
                $totals = (clone $data)->selectRaw('
                    COUNT(id) as count,
                    SUM(pay_amount) as sales,
                    SUM(profit) as profit
                ')->first();

                // Hitung rekap dinamis Hari Ini, Minggu Ini, Bulan Ini
                // dengan tetap memperhitungkan filter outlet (jika ada)
                $filterOutletId = $request->input('outlet_id');
                $rekapOutletIds = $hasOutletRestriction ? $authOutletIds : [];

                $startDateInput = $request->input('start_date');
                $endDateInput = $request->input('end_date');

                if ($startDateInput && $endDateInput) {
                    $startDate = Carbon::parse($startDateInput);
                    $endDate = Carbon::parse($endDateInput);
                    $today = Carbon::today();
                    if ($today->between($startDate, $endDate)) {
                        $targetDate = Carbon::now();
                    } else {
                        $targetDate = $endDate->isFuture() ? Carbon::now() : $endDate->endOfDay();
                    }
                } else {
                    $targetDate = Carbon::now();
                }

                $targetDateToday = $targetDate->copy()->startOfDay();
                $targetDateWeekStart = $targetDate->copy()->startOfWeek();
                $targetDateWeekEnd = $targetDate->copy()->endOfWeek();
                $targetDateMonthStart = $targetDate->copy()->startOfMonth();
                $targetDateMonthEnd = $targetDate->copy()->endOfMonth();

                $todayQuery = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                    ->whereDate('created_at', $targetDateToday)
                    ->when(!empty($rekapOutletIds), function ($q) use ($rekapOutletIds) {
                        $q->whereIn('outlet_id', $rekapOutletIds);
                    })
                    ->when($filterOutletId, function ($q) use ($filterOutletId) {
                        $q->where('outlet_id', $filterOutletId);
                    });

                $weekQuery = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                    ->whereBetween('created_at', [$targetDateWeekStart, $targetDateWeekEnd])
                    ->when(!empty($rekapOutletIds), function ($q) use ($rekapOutletIds) {
                        $q->whereIn('outlet_id', $rekapOutletIds);
                    })
                    ->when($filterOutletId, function ($q) use ($filterOutletId) {
                        $q->where('outlet_id', $filterOutletId);
                    });

                $monthQuery = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                    ->whereBetween('created_at', [$targetDateMonthStart, $targetDateMonthEnd])
                    ->when(!empty($rekapOutletIds), function ($q) use ($rekapOutletIds) {
                        $q->whereIn('outlet_id', $rekapOutletIds);
                    })
                    ->when($filterOutletId, function ($q) use ($filterOutletId) {
                        $q->where('outlet_id', $filterOutletId);
                    });

                return response()->json([
                    'total_sales' => 'Rp ' . number_format($totals->sales ?? 0, 0, ',', '.'),
                    'total_profit' => 'Rp ' . number_format($totals->profit ?? 0, 0, ',', '.'),
                    'total_transactions' => number_format($totals->count ?? 0, 0, ',', '.'),

                    // Rekap waktu
                    'today_sales' => 'Rp ' . number_format($todayQuery->sum('pay_amount'), 0, ',', '.'),
                    'today_profit' => 'Rp ' . number_format($todayQuery->sum('profit'), 0, ',', '.'),
                    'today_count' => number_format($todayQuery->count(), 0, ',', '.'),

                    'week_sales' => 'Rp ' . number_format($weekQuery->sum('pay_amount'), 0, ',', '.'),
                    'week_profit' => 'Rp ' . number_format($weekQuery->sum('profit'), 0, ',', '.'),
                    'week_count' => number_format($weekQuery->count(), 0, ',', '.'),

                    'month_sales' => 'Rp ' . number_format($monthQuery->sum('pay_amount'), 0, ',', '.'),
                    'month_profit' => 'Rp ' . number_format($monthQuery->sum('profit'), 0, ',', '.'),
                    'month_count' => number_format($monthQuery->count(), 0, ',', '.'),
                ]);
            } elseif ($request->data == 'table') {
                return DataTables::of($data)
                    ->addColumn('payment_code', function ($data) {
                        return '<strong>' . ($data->payment_code ?? '-') . '</strong>';
                    })
                    ->addColumn('pay_amount', function ($data) {
                        return 'Rp ' . number_format($data->pay_amount, 0, ',', '.');
                    })
                    ->addColumn('profit', function ($data) {
                        return 'Rp ' . number_format($data->profit, 0, ',', '.');
                    })
                    ->addColumn('date', function ($data) {
                        return Carbon::parse($data->created_at)->translatedFormat('d F Y H:i:s');
                    })
                    ->addColumn('status', function ($data) {
                        if ($data->status == PointOfSaleTransaction::STATUS_SUCCESS) {
                            return '<span class="badge badge-success px-3 py-2">Sukses</span>';
                        } elseif ($data->status == PointOfSaleTransaction::STATUS_PENDING) {
                            return '<span class="badge badge-warning px-3 py-2 text-dark">Pending</span>';
                        } else {
                            return '<span class="badge badge-danger px-3 py-2">Gagal</span>';
                        }
                    })
                    ->addColumn('details', function ($data) {
                        $items = [];
                        foreach ($data->pointOfSaleTransactionDetails as $detail) {
                            $items[] = ($detail->item?->name ?? 'Barang') . ' (' . $detail->quantity . 'x)';
                        }
                        return implode(', ', $items) ?: '-';
                    })
                    ->addColumn('student', function ($data) {
                        if ($data->type == PointOfSaleTransaction::TYPE_UMUM || !$data->student) {
                            return '<div class="d-flex align-items-center gap-2">
                                <span class="badge badge-light-secondary px-3 py-2 text-dark">Umum</span>
                            </div>';
                        }

                        $studentName = $data->student->name;
                        $className = $data->student->classroom?->name ?? '-';
                        $avatarUrl = $data->student->avatar ? asset($data->student->avatar) : asset('assets/media/avatars/default.png');

                        return '<div class="student-card" style="display: flex; align-items: center; gap: 10px;">
                            <img src="' . $avatarUrl . '" alt="Avatar" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                            <div>
                                <div style="font-weight: 600; color: #1e293b; font-size: 13px;">' . $studentName . '</div>
                                <div style="font-size: 11px; color: #64748b;">Kelas: ' . $className . '</div>
                            </div>
                        </div>';
                    })
                    ->addColumn('admin', function ($data) {
                        return $data->admins?->name ?? '-';
                    })
                    ->addColumn('outlet', function ($data) {
                        return $data->outlet?->name ?? '-';
                    })
                    ->addColumn('action', function ($data) use ($hasOutletRestriction) {
                        $actionDelete = route('pos-transaction.destroy', $data->id);
                        $invoiceUrl = route('order-item-history.print', $data->id);
                        
                        $html = "<div class='d-flex gap-2 justify-content-center'>";
                        $html .= "<a href='" . $invoiceUrl . "' target='_blank' class='btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1' title='Cetak Invoice'><i class='fa-solid fa-print text-primary fs-6'></i></a>";
                        
                        if (!$hasOutletRestriction) { // Hanya superadmin yang bisa hapus transaksi
                            $html .= view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Transaksi POS']);
                        }
                        
                        $html .= "</div>";
                        return $html;
                    })
                    ->rawColumns(['payment_code', 'date', 'action', 'student', 'status', 'details'])
                    ->make(true);
            }
        }

        // Tampilkan halaman pertama
        // Jika admin multi-outlet, tampilkan hanya outlet yang diassign
        if ($hasOutletRestriction) {
            $outlets = Outlet::whereIn('id', $authOutletIds)->orderBy('name')->get();
        } else {
            $outlets = Outlet::orderBy('name')->get();
        }

        // Hitung rekap waktu dinamis untuk inisiasi awal
        $startDateInput = $request->input('start_date');
        $endDateInput = $request->input('end_date');

        if ($startDateInput && $endDateInput) {
            $startDate = Carbon::parse($startDateInput);
            $endDate = Carbon::parse($endDateInput);
            $today = Carbon::today();
            if ($today->between($startDate, $endDate)) {
                $targetDate = Carbon::now();
            } else {
                $targetDate = $endDate->isFuture() ? Carbon::now() : $endDate->endOfDay();
            }
        } else {
            $targetDate = Carbon::now();
        }

        $targetDateToday = $targetDate->copy()->startOfDay();
        $targetDateWeekStart = $targetDate->copy()->startOfWeek();
        $targetDateWeekEnd = $targetDate->copy()->endOfWeek();
        $targetDateMonthStart = $targetDate->copy()->startOfMonth();
        $targetDateMonthEnd = $targetDate->copy()->endOfMonth();

        $todayQuery = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
            ->whereDate('created_at', $targetDateToday)
            ->when($hasOutletRestriction, function ($q) use ($authOutletIds) {
                $q->whereIn('outlet_id', $authOutletIds);
            });

        $weekQuery = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
            ->whereBetween('created_at', [$targetDateWeekStart, $targetDateWeekEnd])
            ->when($hasOutletRestriction, function ($q) use ($authOutletIds) {
                $q->whereIn('outlet_id', $authOutletIds);
            });

        $monthQuery = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
            ->whereBetween('created_at', [$targetDateMonthStart, $targetDateMonthEnd])
            ->when($hasOutletRestriction, function ($q) use ($authOutletIds) {
                $q->whereIn('outlet_id', $authOutletIds);
            });

        $rekapWaktu = [
            'today_sales' => $todayQuery->sum('pay_amount'),
            'today_profit' => $todayQuery->sum('profit'),
            'today_count' => $todayQuery->count(),

            'week_sales' => $weekQuery->sum('pay_amount'),
            'week_profit' => $weekQuery->sum('profit'),
            'week_count' => $weekQuery->count(),

            'month_sales' => $monthQuery->sum('pay_amount'),
            'month_profit' => $monthQuery->sum('profit'),
            'month_count' => $monthQuery->count(),
        ];

        // Rekap Dana Per Outlet (untuk Tab Serah Terima)
        $outletsSummary = [];
        
        // Find main outlet (Koperasi)
        $mainOutlet = $outlets->first(fn($ot) => in_array(strtoupper($ot->code), ['KPR', 'KOPERASI']) || strtoupper($ot->name) === 'KOPERASI');
        $mainOutletId = $mainOutlet?->id;

        // Pre-calculate sales, received handovers, and sent handovers for all outlets
        $salesByOutlet = [];
        $receivedHandoversByOutlet = [];
        $sentHandoversByOutlet = [];

        foreach ($outlets as $ot) {
            $salesByOutlet[$ot->id] = PointOfSaleTransaction::where('outlet_id', $ot->id)
                ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                ->where('type', PointOfSaleTransaction::TYPE_SANTRI)
                ->sum('pay_amount');

            $receivedHandoversByOutlet[$ot->id] = OutletHandover::where('recipient_outlet_id', $ot->id)
                ->sum('amount');

            $sentHandoversByOutlet[$ot->id] = OutletHandover::where('outlet_id', $ot->id)
                ->sum('amount');
        }

        // Calculate pending amount based on parent-child logic
        foreach ($outlets as $ot) {
            $totalSales = $salesByOutlet[$ot->id] ?? 0;

            if ($mainOutletId && $ot->id == $mainOutletId) {
                // Main outlet (Koperasi):
                // Pending amount is the sum of child outlets' pending amounts
                $pendingAmount = 0;
                foreach ($outlets as $childOt) {
                    if ($childOt->id != $mainOutletId) {
                        $childSales = $salesByOutlet[$childOt->id] ?? 0;
                        $childReceived = $receivedHandoversByOutlet[$childOt->id] ?? 0;
                        $pendingAmount += max(0, $childSales - $childReceived);
                    }
                }
                $totalHandovers = $sentHandoversByOutlet[$ot->id] ?? 0;
            } else {
                // Child outlet:
                // Pending amount is its own sales minus handovers received from Koperasi
                $totalHandovers = $receivedHandoversByOutlet[$ot->id] ?? 0;
                $pendingAmount = max(0, $totalSales - $totalHandovers);
            }

            $outletsSummary[] = [
                'id' => $ot->id,
                'name' => $ot->name,
                'code' => $ot->code,
                'total_sales' => $totalSales,
                'total_handovers' => $totalHandovers,
                'pending_amount' => $pendingAmount,
            ];
        }

        $year = now()->year;
        $chartIncomesCategories = collect(range(1, 12))->map(fn($month) => Carbon::create($year, $month, 1)->locale('id')->monthName)->toArray();
        
        $chartCashierOmzet = $this->generateMonthlyChartData($year, 'pay_amount', $outletId, $hasOutletRestriction, $authOutletIds);
        $chartCashierProfit = $this->generateMonthlyChartData($year, 'profit', $outletId, $hasOutletRestriction, $authOutletIds);

        // Count of active products
        $totalProduct = \App\Models\Item::whereIsActive(true)
            ->when($hasOutletRestriction, function ($q) use ($authOutletIds) {
                $q->whereIn('outlet_id', $authOutletIds);
            })
            ->when(!$hasOutletRestriction && $outletId, function($q) use ($outletId) {
                $q->where('outlet_id', $outletId);
            })
            ->when($hasOutletRestriction && $outletId, function($q) use ($outletId, $authOutletIds) {
                if (in_array($outletId, $authOutletIds)) {
                    $q->where('outlet_id', $outletId);
                }
            })
            ->count();

        // Calculate global totals
        $transactionQueryGlobal = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
            ->when($hasOutletRestriction, function ($q) use ($authOutletIds) {
                $q->whereIn('outlet_id', $authOutletIds);
            })
            ->when(!$hasOutletRestriction && $outletId, function ($q) use ($outletId) {
                $q->where('outlet_id', $outletId);
            })
            ->when($hasOutletRestriction && $outletId, function ($q) use ($outletId, $authOutletIds) {
                if (in_array($outletId, $authOutletIds)) {
                    $q->where('outlet_id', $outletId);
                }
            });

        $totalTransaction = (clone $transactionQueryGlobal)->count();
        $totalSales = (clone $transactionQueryGlobal)->sum('pay_amount');
        $totalIncome = (clone $transactionQueryGlobal)->sum('profit');

        $admins = \App\Models\Admin::orderBy('name')->get();

        return view('admins.pos-transaction.index', compact(
            'outlets', 
            'rekapWaktu', 
            'outletsSummary', 
            'hasOutletRestriction',
            'chartCashierOmzet',
            'chartCashierProfit',
            'chartIncomesCategories',
            'totalProduct',
            'totalTransaction',
            'totalSales',
            'totalIncome',
            'admins'
        ));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (count(auth()->user()->getOutletIds()) > 0 && !auth()->user()->hasRole('Super Admin')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk menghapus transaksi');
        }

        try {
            $transaction = PointOfSaleTransaction::findOrFail($id);
            
            // Hapus detail transaksi juga
            $transaction->pointOfSaleTransactionDetails()->delete();
            $transaction->delete();

            return redirect()->back()->with('success', 'Transaksi berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Generate monthly chart data (omzet or profit).
     */
    private function generateMonthlyChartData($year, $column, $outletId = null, $hasOutletRestriction = false, $authOutletIds = [])
    {
        return collect(range(1, 12))->map(function ($month) use ($year, $column, $outletId, $hasOutletRestriction, $authOutletIds) {
            return intval(PointOfSaleTransaction::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                ->when($hasOutletRestriction, function ($q) use ($authOutletIds) {
                    $q->whereIn('outlet_id', $authOutletIds);
                })
                ->when(!$hasOutletRestriction && $outletId, function($q) use ($outletId) {
                    $q->where('outlet_id', $outletId);
                })
                ->when($hasOutletRestriction && $outletId, function($q) use ($outletId, $authOutletIds) {
                    if (in_array($outletId, $authOutletIds)) {
                        $q->where('outlet_id', $outletId);
                    }
                })
                ->sum($column));
        })->toArray();
    }
}
