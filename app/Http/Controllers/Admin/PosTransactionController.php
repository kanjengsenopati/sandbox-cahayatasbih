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
        if (!Auth::user()->can('Manage Laporan Pos Multi Outlet')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        // Tentukan outlet_ids berdasarkan hak akses admin yang login
        $authOutletIds = auth()->user()->getOutletIds();
        $hasOutletRestriction = count($authOutletIds) > 0;
        $outletId = $request->input('outlet_id');

        if ($request->ajax()) {
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

                $todayQuery = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                    ->whereDate('created_at', Carbon::today())
                    ->when(!empty($rekapOutletIds), function ($q) use ($rekapOutletIds) {
                        $q->whereIn('outlet_id', $rekapOutletIds);
                    })
                    ->when($filterOutletId, function ($q) use ($filterOutletId) {
                        $q->where('outlet_id', $filterOutletId);
                    });

                $weekQuery = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                    ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                    ->when(!empty($rekapOutletIds), function ($q) use ($rekapOutletIds) {
                        $q->whereIn('outlet_id', $rekapOutletIds);
                    })
                    ->when($filterOutletId, function ($q) use ($filterOutletId) {
                        $q->where('outlet_id', $filterOutletId);
                    });

                $monthQuery = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                    ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
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
        $todayQuery = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
            ->whereDate('created_at', Carbon::today())
            ->when($hasOutletRestriction, function ($q) use ($authOutletIds) {
                $q->whereIn('outlet_id', $authOutletIds);
            });

        $weekQuery = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->when($hasOutletRestriction, function ($q) use ($authOutletIds) {
                $q->whereIn('outlet_id', $authOutletIds);
            });

        $monthQuery = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
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
        foreach ($outlets as $ot) {
            $totalSales = PointOfSaleTransaction::where('outlet_id', $ot->id)
                ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                ->where('type', PointOfSaleTransaction::TYPE_SANTRI)
                ->sum('pay_amount');

            $totalHandovers = OutletHandover::where('outlet_id', $ot->id)
                ->sum('amount');

            $outletsSummary[] = [
                'id' => $ot->id,
                'name' => $ot->name,
                'code' => $ot->code,
                'total_sales' => $totalSales,
                'total_handovers' => $totalHandovers,
                'pending_amount' => max(0, $totalSales - $totalHandovers),
            ];
        }

        return view('admins.pos-transaction.index', compact('outlets', 'rekapWaktu', 'outletsSummary', 'hasOutletRestriction'));
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
}
