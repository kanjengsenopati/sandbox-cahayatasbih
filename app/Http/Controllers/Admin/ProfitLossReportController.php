<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Outlet;
use App\Models\CashFlow;
use App\Models\CashFlowCategory;
use App\Models\PointOfSaleTransaction;
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
            if ($user && $user->hasAnyRole(['Kasir Koperasi', 'Kasir Karyawan Outlet', 'Kasir'])) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Maaf, Anda tidak memiliki akses.'], 403);
                }
                if ($user->hasRole('Kasir Koperasi')) {
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
        $categories = ['Listrik', 'Honor Kasir', 'Server Aplikasi', 'IT Support', 'Lainnya'];
        foreach ($categories as $catName) {
            CashFlowCategory::firstOrCreate(
                ['name' => $catName],
                ['description' => "Kategori pengeluaran operasional: $catName"]
            );
        }
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

        $koperasi = Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
        $koperasiId = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';

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

        // 2. Hitung Pemasukan Kas Operasional (APPROVED, excluding internal handovers)
        $excludeCategoryNames = [
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

        // 3. Hitung Pengeluaran Kas Operasional (APPROVED)
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
        $rawExpenses = (clone $cashExpensesQuery)->with('cashflow_category')->get();
        
        foreach ($rawExpenses as $exp) {
            $catName = $exp->cashflow_category?->name ?? 'Lainnya';
            
            // Check if it is a custom category under "Lainnya"
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

        // Convert key-value back to array for simple Blade rendering
        $cashExpensesBreakdownFormatted = [];
        foreach ($cashExpensesBreakdown as $name => $total) {
            $cashExpensesBreakdownFormatted[] = (object)[
                'category_name' => $name,
                'total' => $total
            ];
        }

        // 4. Perhitungan Rugi Laba
        $totalRevenues = $posSalesTotal + $cashIncomesTotal;
        $totalHpp = $posCostTotal;
        $grossProfit = $totalRevenues - $totalHpp;
        $totalExpenses = $cashExpensesTotal;
        $netProfit = $grossProfit - $totalExpenses;

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

        // Current selected outlet details
        $selectedOutlet = null;
        if ($outletId && !$hasOutletRestriction) {
            $selectedOutlet = Outlet::find($outletId);
        } elseif ($outletId && $hasOutletRestriction && in_array($outletId, $authOutletIds)) {
            $selectedOutlet = Outlet::find($outletId);
        }

        // Fetch expenses list for the Transaksi tab
        $expensesList = (clone $cashExpensesQuery)
            ->with(['cashflow_category', 'sender'])
            ->latest()
            ->get()
            ->map(function ($exp) {
                // Parse custom category from description if present
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

        // Get operational expense categories for form select dropdown
        $expenseCategories = CashFlowCategory::whereIn('name', ['Listrik', 'Honor Kasir', 'Server Aplikasi', 'IT Support', 'Lainnya'])
            ->orderBy('name')
            ->get();

        return view('admins.report-profit-loss.index', compact(
            'outlets',
            'selectedOutlet',
            'startDate',
            'endDate',
            'posSalesTotal',
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
            'hasOutletRestriction',
            'expensesList',
            'expenseCategories'
        ));
    }

    /**
     * Store a newly created operational expense.
     */
    public function storeExpense(Request $request)
    {
        if (!Auth::user()->can('Manage Arus Kas') && !Auth::user()->can('Manage Laporan Pos Multi Outlet') && !Auth::user()->can('Manage Laporan Rugi Laba')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk mencatat pengeluaran');
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

            // If "Lainnya" and free text provided, format description to capture custom category name
            if ($category->name === 'Lainnya' && $request->filled('custom_category')) {
                $customName = strip_tags($request->custom_category);
                $description = "[Kustom: {$customName}]" . ($description ? " - " . $description : "");
            }

            $amount = preg_replace('/\D/', '', $request->amount);

            $cashflowCount = CashFlow::whereDate('created_at', now())->count();
            $paymentCode = 'EXP-' . now()->format('Ymd') . str_pad($cashflowCount + 1, 3, '0', STR_PAD_LEFT);

            $data = [
                'sender_id' => Auth::id(),
                'receiver_id' => Auth::id(), // self receiver
                'outlet_id' => $request->outlet_id,
                'cash_flow_category_id' => $request->cash_flow_category_id,
                'payment_code' => $paymentCode,
                'type' => CashFlow::TYPE_EXPENSE,
                'amount' => $amount,
                'date' => $request->date,
                'description' => $description,
                'status' => CashFlow::STATUS_APPROVED, // Auto-approved operational expense
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
        if (!Auth::user()->can('Manage Arus Kas') && !Auth::user()->can('Manage Laporan Pos Multi Outlet') && !Auth::user()->can('Manage Laporan Rugi Laba')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk menghapus pengeluaran');
        }

        try {
            $cashflow = CashFlow::findOrFail($id);

            // Access control check
            $authOutletIds = auth()->user()->getOutletIds();
            $hasOutletRestriction = count($authOutletIds) > 0;
            if ($hasOutletRestriction && !in_array($cashflow->outlet_id, $authOutletIds)) {
                return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk menghapus transaksi outlet ini');
            }

            // Remove physical proof of payment file if it exists
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
