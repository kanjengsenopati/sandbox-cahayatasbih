<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Outlet;
use App\Models\Admin;
use App\Models\CashFlow;
use App\Models\CashFlowCategory;
use App\Models\OutletHandover;
use App\Models\PointOfSaleTransaction;
use App\Services\OutletContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfitLossReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if ($user && $user->isKasir()) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Maaf, Anda tidak memiliki akses.'], 403);
                }
                if ($user->isKasirKoperasi()) {
                    return redirect('/order-item?mode=kantin')->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
                }
                return redirect('/order-item?mode=outlet')->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
            }
            return $next($request);
        });
    }

    /**
     * Ensure default expense categories exist in the database.
     */
    private function ensureExpenseCategoriesExist()
    {
        \Illuminate\Support\Facades\Cache::rememberForever('profit_loss_categories_seeded', function () {
            $categories = ['Listrik', 'Honor Kasir', 'Server Aplikasi', 'IT Support', 'Lainnya'];
            foreach ($categories as $catName) {
                CashFlowCategory::firstOrCreate(
                    ['name' => $catName],
                    ['description' => "Kategori pengeluaran operasional: $catName"]
                );
            }
            return true;
        });
    }

    public function index(Request $request)
    {
        if (!Auth::user()->can('Manage Arus Kas') && !Auth::user()->can('Manage Laporan Pos Multi Outlet') && !Auth::user()->can('Manage Laporan Rugi Laba')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        // Tentukan default categories
        $this->ensureExpenseCategoriesExist();

        // Tentukan outlet_ids berdasarkan hak akses admin yang login
        $authOutletIds = auth()->user()->getOutletIds();
        $hasOutletRestriction = count($authOutletIds) > 0;
        $outletId = $request->input('outlet_id');

        $koperasiId = OutletContextService::getKoperasiOutletId();
        $koperasiOutlet = Outlet::find($koperasiId);

        if (!$outletId && !$hasOutletRestriction) {
            if ($request->input('mode') === 'outlet') {
                $queryOutletId = Outlet::where('id', '!=', $koperasiId)->pluck('id')->toArray();
            } else {
                $queryOutletId = $koperasiId;
                $outletId = $koperasiId;
            }
        } else {
            if ($hasOutletRestriction) {
                $queryOutletId = $outletId && in_array($outletId, $authOutletIds) ? $outletId : $authOutletIds;
            } else {
                $queryOutletId = $outletId;
            }
        }

        // Tentukan rentang tanggal filter (default: awal bulan ini s.d hari ini)
        $startDateInput = $request->input('start_date');
        $endDateInput = $request->input('end_date');

        if ($startDateInput && $endDateInput) {
            $startDate = Carbon::parse($startDateInput)->format('Y-m-d');
            $endDate = Carbon::parse($endDateInput)->format('Y-m-d');
        } else {
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        // 1. Hitung Penjualan POS & Profit POS (Realisasi)
        $posQuery = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->when($queryOutletId, function ($q) use ($queryOutletId) {
                if (is_array($queryOutletId)) {
                    $q->whereIn('outlet_id', $queryOutletId);
                } else {
                    $q->where('outlet_id', $queryOutletId);
                }
            });

        $posSalesTotal = (clone $posQuery)->sum('pay_amount');
        $posProfitTotal = (clone $posQuery)->sum('profit');
        $posCostTotal = max($posSalesTotal - $posProfitTotal, 0);

        // Breakdown POS: Non-tunai Santri vs Tunai Umum
        $posSantriSalesTotal = (clone $posQuery)->where('type', PointOfSaleTransaction::TYPE_SANTRI)->sum('pay_amount');
        $posUmumSalesTotal = (clone $posQuery)->where('type', PointOfSaleTransaction::TYPE_UMUM)->sum('pay_amount');

        // 2. Real Movement Keuangan: Handover yang sudah terjadi (Settled)
        // A. Handover Saldo Koperasi ➔ Outlet
        $santriHandoverQuery = OutletHandover::where('handover_type', OutletHandover::TYPE_KOPERASI_TO_OUTLET)
            ->whereBetween('handover_date', [$startDate, $endDate])
            ->when($queryOutletId, function ($q) use ($queryOutletId) {
                if (is_array($queryOutletId)) {
                    $q->whereIn('recipient_outlet_id', $queryOutletId);
                } else {
                    $q->where('recipient_outlet_id', $queryOutletId);
                }
            });
        $santriHandoverTotal = (clone $santriHandoverQuery)->sum('amount');

        // B. Handover Setor Kasir Tunai ➔ Manajemen
        $cashierHandoverQuery = OutletHandover::where('handover_type', OutletHandover::TYPE_CASHIER_TO_MANAGEMENT)
            ->whereBetween('handover_date', [$startDate, $endDate])
            ->when($queryOutletId, function ($q) use ($queryOutletId) {
                if (is_array($queryOutletId)) {
                    $q->whereIn('outlet_id', $queryOutletId);
                } else {
                    $q->where('outlet_id', $queryOutletId);
                }
            });
        $cashierHandoverTotal = (clone $cashierHandoverQuery)->sum('amount');

        // Total Kas Riil Diterima via Handover
        $totalRealHandover = $santriHandoverTotal + $cashierHandoverTotal;

        // Pending Handover (Selisih Log Sistem vs Real Handover)
        $pendingSantriHandover = max(0, $posSantriSalesTotal - $santriHandoverTotal);
        $pendingCashierHandover = max(0, $posUmumSalesTotal - $cashierHandoverTotal);
        $totalPendingHandover = $pendingSantriHandover + $pendingCashierHandover;

        // 3. Hitung Pemasukan Kas Operasional Eksternal (APPROVED, excluding internal handovers)
        $excludeCategoryNames = [
            'Serah Terima Dana',
            'Setoran Kasir Tunai',
            'Serah Terima Piket ke Bendahara',
            'Serah Terima Bendahara ke Yayasan'
        ];

        $cashIncomesQuery = CashFlow::where('type', CashFlow::TYPE_INCOME)
            ->where('status', CashFlow::STATUS_APPROVED)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereHas('cashflow_category', function($q) use ($excludeCategoryNames) {
                $q->whereNotIn('name', $excludeCategoryNames);
            })
            ->when($queryOutletId, function ($q) use ($queryOutletId) {
                if (is_array($queryOutletId)) {
                    $q->whereIn('outlet_id', $queryOutletId);
                } else {
                    $q->where('outlet_id', $queryOutletId);
                }
            });

        $cashIncomesTotal = $cashIncomesQuery->sum('amount');
        $cashIncomesBreakdown = $cashIncomesQuery->select('cash_flow_category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('cash_flow_category_id')
            ->with('cashflow_category')
            ->get();

        // 4. Hitung Pengeluaran Kas Operasional (APPROVED)
        $cashExpensesQuery = CashFlow::where('type', CashFlow::TYPE_EXPENSE)
            ->where('status', CashFlow::STATUS_APPROVED)
            ->whereBetween('date', [$startDate, $endDate])
            ->when($queryOutletId, function ($q) use ($queryOutletId) {
                if (is_array($queryOutletId)) {
                    $q->whereIn('outlet_id', $queryOutletId);
                } else {
                    $q->where('outlet_id', $queryOutletId);
                }
            });

        $cashExpensesTotal = $cashExpensesQuery->sum('amount');

        // Custom breakdown grouping to separate custom "Lainnya" subcategories
        $cashExpensesBreakdown = [];
        $rawExpenses = (clone $cashExpensesQuery)
            ->select('id', 'cash_flow_category_id', 'description', 'amount')
            ->with('cashflow_category:id,name')
            ->get();

        foreach ($rawExpenses as $exp) {
            $catName = $exp->cashflow_category?->name ?? 'Lainnya';

            if ($catName === 'Lainnya' && preg_match('/^\[Kustom:\s*([^\]]+)\]/', $exp->description, $matches)) {
                $displayName = trim($matches[1]);
            } else {
                $displayName = $catName;
            }

            if (!isset($cashExpensesBreakdown[$displayName])) {
                $cashExpensesBreakdown[$displayName] = 0;
            }
            $cashExpensesBreakdown[$displayName] += $exp->amount;
        }

        $cashExpensesBreakdownFormatted = [];
        foreach ($cashExpensesBreakdown as $name => $total) {
            $cashExpensesBreakdownFormatted[] = (object)[
                'category_name' => $name,
                'total' => $total
            ];
        }

        // 5. Perhitungan Laba Rugi Komprehensif
        $totalRevenues = $posSalesTotal + $cashIncomesTotal;
        $totalHpp = $posCostTotal;
        $grossProfit = $totalRevenues - $totalHpp;
        $totalExpenses = $cashExpensesTotal;
        $netProfit = $grossProfit - $totalExpenses;

        // Posisi Kas Riil (Net Cash Realized)
        $realCashRevenues = $totalRealHandover + $cashIncomesTotal;
        $realNetCashProfit = $realCashRevenues - $totalHpp - $totalExpenses;

        // Fetch outlets for dropdown filter
        if ($hasOutletRestriction) {
            $outlets = Outlet::whereIn('id', $authOutletIds)
                ->when(request('mode') === 'outlet', function($q) use ($koperasiId) {
                    $q->where('id', '!=', $koperasiId);
                })
                ->when(request('mode') !== 'outlet', function($q) use ($koperasiId) {
                    $q->where('id', $koperasiId);
                })
                ->orderBy('name')->get();
        } else {
            $outlets = Outlet::query()
                ->when(request('mode') === 'outlet', function($q) use ($koperasiId) {
                    $q->where('id', '!=', $koperasiId);
                })
                ->when(request('mode') !== 'outlet', function($q) use ($koperasiId) {
                    $q->where('id', $koperasiId);
                })
                ->orderBy('name')->get();
        }

        // Selected outlet details
        $selectedOutlet = null;
        if ($outletId && !$hasOutletRestriction) {
            $selectedOutlet = Outlet::find($outletId);
        } elseif ($outletId && $hasOutletRestriction && in_array($outletId, $authOutletIds)) {
            $selectedOutlet = Outlet::find($outletId);
        }

        // Fetch expenses list for Transaksi tab
        $expensesList = (clone $cashExpensesQuery)
            ->with(['cashflow_category', 'sender'])
            ->latest('date')
            ->get()
            ->map(function ($exp) {
                $categoryName = $exp->cashflow_category?->name ?? '-';
                $cleanDescription = $exp->description;
                if ($categoryName === 'Lainnya' && preg_match('/^\[Kustom:\s*([^\]]+)\]\s*(?:-\s*)?(.*)/s', $exp->description, $matches)) {
                    $categoryName = trim($matches[1]);
                    $cleanDescription = trim($matches[2]);
                }
                $exp->display_category_name = $categoryName;
                $exp->display_description = $cleanDescription;
                return $exp;
            });

        // Fetch handovers list for Serah Terima tab
        $handoversList = OutletHandover::with(['outlet', 'recipientOutlet', 'recipient', 'cashier', 'creator'])
            ->whereBetween('handover_date', [$startDate, $endDate])
            ->when($queryOutletId, function ($q) use ($queryOutletId) {
                if (is_array($queryOutletId)) {
                    $q->where(function ($sub) use ($queryOutletId) {
                        $sub->whereIn('outlet_id', $queryOutletId)
                            ->orWhereIn('recipient_outlet_id', $queryOutletId);
                    });
                } else {
                    $q->where(function ($sub) use ($queryOutletId) {
                        $sub->where('outlet_id', $queryOutletId)
                            ->orWhere('recipient_outlet_id', $queryOutletId);
                    });
                }
            })
            ->latest('handover_date')
            ->get();

        // Operational expense categories for form select dropdown
        $expenseCategories = CashFlowCategory::whereIn('name', ['Listrik', 'Honor Kasir', 'Server Aplikasi', 'IT Support', 'Lainnya'])
            ->orderBy('name')
            ->get();

        // Data pendukung modal handover
        $allAdmins = Admin::orderBy('name')->get();
        $childOutlets = Outlet::where('id', '!=', $koperasiId)->orderBy('name')->get();
        $allOutlets = Outlet::orderBy('name')->get();

        return view('admins.report-profit-loss.index', compact(
            'outlets',
            'allOutlets',
            'childOutlets',
            'selectedOutlet',
            'koperasiOutlet',
            'startDate',
            'endDate',
            'posSalesTotal',
            'posSantriSalesTotal',
            'posUmumSalesTotal',
            'santriHandoverTotal',
            'cashierHandoverTotal',
            'totalRealHandover',
            'pendingSantriHandover',
            'pendingCashierHandover',
            'totalPendingHandover',
            'posProfitTotal',
            'posCostTotal',
            'cashIncomesTotal',
            'cashIncomesBreakdown',
            'cashExpensesTotal',
            'cashExpensesBreakdownFormatted',
            'totalRevenues',
            'totalHpp',
            'grossProfit',
            'totalExpenses',
            'netProfit',
            'realNetCashProfit',
            'hasOutletRestriction',
            'expensesList',
            'handoversList',
            'expenseCategories',
            'allAdmins'
        ));
    }

    /**
     * Store a newly created operational expense.
     */
    public function storeExpense(Request $request)
    {
        if (!Auth::user()->can('Create Arus Kas') && !Auth::user()->hasRole('Super Admin')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk mencatat pengeluaran operasional');
        }

        $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'cash_flow_category_id' => 'required|exists:cash_flow_categories,id',
            'custom_category' => 'nullable|string|max:255',
            'amount' => 'required',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'proof_of_payment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            $category = CashFlowCategory::findOrFail($request->cash_flow_category_id);
            $description = $request->description;

            if ($category->name === 'Lainnya' && $request->filled('custom_category')) {
                $customName = strip_tags($request->custom_category);
                $description = "[Kustom: {$customName}]" . ($description ? " - " . $description : "");
            }

            $amount = preg_replace('/\D/', '', $request->amount);

            $cashflowCount = CashFlow::whereDate('created_at', now())->count();
            $paymentCode = 'EXP-' . now()->format('Ymd') . str_pad($cashflowCount + 1, 3, '0', STR_PAD_LEFT);

            $data = [
                'sender_id' => Auth::id(),
                'receiver_id' => Auth::id(),
                'outlet_id' => $request->outlet_id,
                'cash_flow_category_id' => $request->cash_flow_category_id,
                'payment_code' => $paymentCode,
                'type' => CashFlow::TYPE_EXPENSE,
                'amount' => $amount,
                'date' => $request->date,
                'description' => $description,
                'status' => CashFlow::STATUS_APPROVED,
                'payment_method' => 'CASH',
            ];

            if ($request->hasFile('proof_of_payment')) {
                $path = $request->file('proof_of_payment')->store('images/cashflow', ['disk' => 'public']);
                $data['proof_of_payment'] = 'storage/' . $path;
            }

            CashFlow::create($data);

            return redirect()->back()->with('success', 'Berhasil mencatat pengeluaran operasional');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mencatat pengeluaran: ' . $e->getMessage());
        }
    }

    /**
     * Delete an operational expense.
     */
    public function destroyExpense($id)
    {
        if (!Auth::user()->can('Delete Arus Kas') && !Auth::user()->hasRole('Super Admin')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk menghapus pengeluaran operasional');
        }

        try {
            $cashflow = CashFlow::findOrFail($id);

            $authOutletIds = auth()->user()->getOutletIds();
            $hasOutletRestriction = count($authOutletIds) > 0;
            if ($hasOutletRestriction && !in_array($cashflow->outlet_id, $authOutletIds)) {
                return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk menghapus transaksi outlet ini');
            }

            if ($cashflow->proof_of_payment) {
                $cleanPath = str_replace('storage/', '', $cashflow->proof_of_payment);
                Storage::disk('public')->delete($cleanPath);
            }

            $cashflow->delete();

            return redirect()->back()->with('success', 'Berhasil menghapus pengeluaran operasional');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus pengeluaran: ' . $e->getMessage());
        }
    }
}
