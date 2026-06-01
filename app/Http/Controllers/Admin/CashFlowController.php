<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Admin;
use App\Models\CashFlow;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\CashFlowCategory;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\CashFlowRequest;
use App\Models\Bill;
use App\Models\PaymentMethod;

class CashFlowController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Arus Kas')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax() && request()->type == 'data') {
            $data = CashFlow::when(request()->filled('start_date') && request()->filled('end_date'), function ($query) {
                $query->whereBetween('date', [request()->start_date, request()->end_date]);
            })
                ->when(request()->filled('category'), function ($query) {
                    $query->where('cashflow_category_id', request()->category);
                })
                ->when(request()->filled('status'), function ($query) {
                    $query->where('status', request()->status);
                })
                ->latest();

            return DataTables::of($data)
                ->editColumn('type', function ($data) {
                    return $data->type == CashFlow::TYPE_INCOME
                        ? '<span class="badge bg-primary">Pemasukan</span>'
                        : '<span class="badge bg-danger">Pengeluaran</span>';
                })
                ->editColumn('date', function ($data) {
                    return Carbon::parse($data->date)->translatedFormat('d F Y');
                })
                ->editColumn('amount', function ($data) {
                    return 'Rp ' . number_format($data->amount, 0, ',', '.');
                })
                ->addColumn('category', function ($data) {
                    return $data->cashflow_category?->name ?? '-';
                })
                ->addColumn('from_to', function ($data) {
                    return $data->sender?->name . ' -> ' . $data->receiver?->name;
                })
                ->addColumn('status', function ($data) {
                    return match ($data->status) {
                        'PENDING' => "<span class='badge bg-warning'>Menunggu Konfirmasi</span>",
                        'APPROVED' => "<span class='badge bg-success'>Disetujui</span>",
                        'REJECTED' => "<span class='badge bg-danger'>Ditolak - " . $data->reason . "</span>",
                        default => '-',
                    };
                })
                ->addColumn('proof', function ($data) {
                    if ($data->proof_of_payment) {
                        return "<a href='" . storage_asset($data->proof_of_payment) . "' data-lightbox='proof' data-title='Bukti Pembayaran'>
                        <img src='" . storage_asset($data->proof_of_payment) . "' alt='Proof of Payment' class='img-thumbnail' style='cursor: pointer; width: 100px; height: 100px; object-fit: cover;' />
                    </a>";
                    }
                    return '-';
                })
                ->addColumn('action', function ($data) {
                    $action = "";

                    if (Auth::id() == $data->receiver_id && $data->status == CashFlow::STATUS_PENDING) {
                        $action .= "<button class='btn btn-success btn-sm approve-btn' data-id='" . $data->id . "'>Terima</button>&nbsp;";
                        $action .= "<button class='btn btn-danger btn-sm reject-btn' data-id='" . $data->id . "' data-bs-toggle='modal' data-bs-target='#rejectModal'>Tolak</button>";
                    }

                    if (Auth::id() == $data->sender_id && in_array($data->status, [CashFlow::STATUS_PENDING, CashFlow::STATUS_REJECTED])) {
                        $actionEdit = route('cashflow.edit', $data->id);
                        $actionDelete = route('cashflow.destroy', $data->id);

                        $action .= view('components.action.edit', ['action' => $actionEdit, 'name' => 'Arus Kas']) . '&nbsp;';
                        $action .= view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Arus Kas']);
                    }

                    return "<div class='d-flex justify-content-center'>" . $action . "</div>";
                })
                ->rawColumns(['action', 'status', 'proof', 'type'])
                ->make(true);
        }

        if (request()->type == 'summary') {
            return $this->summary();
        }

        return view('admins.cashflow.index');
    }


    public function ensureHandoverCategoriesExist()
    {
        $catPiket = CashFlowCategory::firstOrCreate(
            ['name' => 'Serah Terima Piket ke Bendahara'],
            ['description' => 'Alur serah terima uang tunai dari petugas piket ke bendahara']
        );
        $catYayasan = CashFlowCategory::firstOrCreate(
            ['name' => 'Serah Terima Bendahara ke Yayasan'],
            ['description' => 'Alur serah terima uang dari bendahara ke pengurus yayasan']
        );
        return [$catPiket, $catYayasan];
    }

    public function summary()
    {
        $this->ensureHandoverCategoriesExist();

        $startDate = request()->filled('start_date') ? Carbon::parse(request()->start_date) : null;
        $endDate = request()->filled('end_date') ? Carbon::parse(request()->end_date) : null;

        // 1. Hitung Target & Realisasi dari SEMUA tagihan
        $billQuery = Bill::query();
        
        if ($startDate) {
            $billQuery->where(function ($query) use ($startDate) {
                $query->where('year', '>', $startDate->year)
                    ->orWhere(function ($sub) use ($startDate) {
                        $sub->where('year', '=', $startDate->year)
                            ->where('month', '>=', $startDate->month);
                    });
            });
        }
        if ($endDate) {
            $billQuery->where(function ($query) use ($endDate) {
                $query->where('year', '<', $endDate->year)
                    ->orWhere(function ($sub) use ($endDate) {
                        $sub->where('year', '=', $endDate->year)
                            ->where('month', '<=', $endDate->month);
                    });
            });
        }

        $totalCashflows = (clone $billQuery)->sum('amount'); // Target Total Pemasukan
        $totalIncomes = (clone $billQuery)->where('status', 'PAID')->sum('amount'); // Realisasi Pemasukan

        $statusPemasukan = 'Sesuai';
        if ($totalIncomes < $totalCashflows) {
            $statusPemasukan = 'Defisit';
        } elseif ($totalIncomes > $totalCashflows) {
            $statusPemasukan = 'Surplus';
        }

        // 2. Pengeluaran & Sisa Saldo
        $totalExpenses = CashFlow::where('type', CashFlow::TYPE_EXPENSE)
            ->where('status', CashFlow::STATUS_APPROVED)
            ->when($startDate, fn($query) => $query->whereDate('date', '>=', $startDate->toDateString()))
            ->when($endDate, fn($query) => $query->whereDate('date', '<=', $endDate->toDateString()))
            ->sum('amount');

        $remainingBalances = max($totalIncomes - $totalExpenses, 0);

        // 3. Breakdown per Jenis/Nama Pembayaran (Unifikasi & Resolusi Ambigu Lembaga + Tahun Ajaran)
        $paidBills = (clone $billQuery)
            ->where('status', 'PAID')
            ->with(['billType.billItem', 'billType.academicYear'])
            ->get();

        $breakdownGrouped = [];
        foreach ($paidBills as $bill) {
            $billType = $bill->billType;
            if (!$billType) continue;

            $typeName = trim($billType->name);
            $unitName = $billType->billItem?->name ?? '';
            $yearName = $billType->academicYear?->name ?? '';

            // Buat nama gabungan non-ambigu jika Lembaga atau Tahun Ajaran berbeda
            $fullName = $typeName;
            if ($unitName || $yearName) {
                $parts = [];
                if ($unitName) $parts[] = $unitName;
                if ($yearName) $parts[] = $yearName;
                $fullName .= ' (' . implode(' - ', $parts) . ')';
            }

            if (!isset($breakdownGrouped[$fullName])) {
                $breakdownGrouped[$fullName] = 0;
            }
            $breakdownGrouped[$fullName] += $bill->amount;
        }

        $breakdownBills = [];
        foreach ($breakdownGrouped as $name => $amount) {
            $breakdownBills[] = [
                'name' => $name,
                'total_amount' => $amount,
                'total_formatted' => 'Rp ' . number_format($amount, 0, ',', '.'),
            ];
        }

        // Urutkan berdasarkan total pemasukan tertinggi (descending)
        usort($breakdownBills, function ($a, $b) {
            return $b['total_amount'] <=> $a['total_amount'];
        });

        // 4. Breakdown per Sumber Pembayaran (Tunai, Debit Saldo, Transfer Aplikasi)
        $sourceBreakdownRaw = Transaction::where('transactions.status', Transaction::STATUS_PAID)
            ->where('transactions.type', Transaction::TYPE_BILL)
            ->when($startDate, fn($q) => $q->whereDate('transactions.paid_at', '>=', $startDate->toDateString()))
            ->when($endDate, fn($q) => $q->whereDate('transactions.paid_at', '<=', $endDate->toDateString()))
            ->join('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id')
            ->select('payment_methods.type', DB::raw('SUM(transactions.pay_amount) as total_amount'))
            ->groupBy('payment_methods.type')
            ->get();

        $sourceBreakdown = [
            'Tunai' => 0,
            'Debit Saldo' => 0,
            'Transfer Aplikasi' => 0,
        ];

        foreach ($sourceBreakdownRaw as $item) {
            if ($item->type == PaymentMethod::TYPE_CASH) {
                $sourceBreakdown['Tunai'] += $item->total_amount;
            } elseif ($item->type == PaymentMethod::TYPE_BALANCE) {
                $sourceBreakdown['Debit Saldo'] += $item->total_amount;
            } else {
                $sourceBreakdown['Transfer Aplikasi'] += $item->total_amount;
            }
        }

        // 5. Tracing Petugas Piket (Tunai)
        $piketOfficers = Transaction::where('transactions.status', Transaction::STATUS_PAID)
            ->where('transactions.type', Transaction::TYPE_BILL)
            ->join('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id')
            ->where('payment_methods.type', PaymentMethod::TYPE_CASH)
            ->when($startDate, fn($q) => $q->whereDate('transactions.paid_at', '>=', $startDate->toDateString()))
            ->when($endDate, fn($q) => $q->whereDate('transactions.paid_at', '<=', $endDate->toDateString()))
            ->join('admins', 'transactions.admin_id', '=', 'admins.id')
            ->select('admins.id', 'admins.name', DB::raw('SUM(transactions.pay_amount) as total_cash'), DB::raw('COUNT(transactions.id) as total_txs'))
            ->groupBy('admins.id', 'admins.name')
            ->get()
            ->map(function ($officer) {
                $catPiket = CashFlowCategory::where('name', 'Serah Terima Piket ke Bendahara')->first();
                $handedOver = CashFlow::where('sender_id', $officer->id)
                    ->where('status', CashFlow::STATUS_APPROVED)
                    ->when($catPiket, fn($q) => $q->where('cash_flow_category_id', $catPiket->id))
                    ->sum('amount');
                
                $cashInHand = max($officer->total_cash - $handedOver, 0);
                
                return [
                    'id' => $officer->id,
                    'name' => $officer->name,
                    'total_collected' => (int)$officer->total_cash,
                    'total_collected_formatted' => 'Rp ' . number_format($officer->total_cash, 0, ',', '.'),
                    'handed_over' => (int)$handedOver,
                    'handed_over_formatted' => 'Rp ' . number_format($handedOver, 0, ',', '.'),
                    'cash_in_hand' => (int)$cashInHand,
                    'cash_in_hand_formatted' => 'Rp ' . number_format($cashInHand, 0, ',', '.'),
                    'total_txs' => $officer->total_txs,
                ];
            });

        // 6. Workflow Stats Piping
        $catPiket = CashFlowCategory::where('name', 'Serah Terima Piket ke Bendahara')->first();
        $catYayasan = CashFlowCategory::where('name', 'Serah Terima Bendahara ke Yayasan')->first();

        $totalPiketCash = Transaction::where('transactions.status', Transaction::STATUS_PAID)
            ->join('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id')
            ->where('payment_methods.type', PaymentMethod::TYPE_CASH)
            ->when($startDate, fn($q) => $q->whereDate('transactions.paid_at', '>=', $startDate->toDateString()))
            ->when($endDate, fn($q) => $q->whereDate('transactions.paid_at', '<=', $endDate->toDateString()))
            ->sum('transactions.pay_amount');

        $totalHandedToBendahara = CashFlow::where('status', CashFlow::STATUS_APPROVED)
            ->when($catPiket, fn($q) => $q->where('cash_flow_category_id', $catPiket->id))
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate->toDateString()))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate->toDateString()))
            ->sum('amount');

        $totalHandedToYayasan = CashFlow::where('status', CashFlow::STATUS_APPROVED)
            ->when($catYayasan, fn($q) => $q->where('cash_flow_category_id', $catYayasan->id))
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate->toDateString()))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate->toDateString()))
            ->sum('amount');

        $activeAdmins = Admin::select('id', 'name')->where('id', '!=', Auth::id())->orderBy('name')->get();

        return response()->json([
            'total_incomes' => number_format($totalIncomes, 0, ',', '.'),
            'total_expenses' => number_format($totalExpenses, 0, ',', '.'),
            'remaining_balances' => number_format($remainingBalances, 0, ',', '.'),
            'total_cashflows' => number_format($totalCashflows, 0, ',', '.'),
            'status_pemasukan' => $statusPemasukan,
            'breakdown_bills' => $breakdownBills,
            'breakdown_sources' => [
                'tunai' => number_format($sourceBreakdown['Tunai'], 0, ',', '.'),
                'saldo' => number_format($sourceBreakdown['Debit Saldo'], 0, ',', '.'),
                'transfer' => number_format($sourceBreakdown['Transfer Aplikasi'], 0, ',', '.'),
            ],
            'piket_officers' => $piketOfficers,
            'workflow_stats' => [
                'total_piket_cash' => 'Rp ' . number_format($totalPiketCash, 0, ',', '.'),
                'total_handed_bendahara' => 'Rp ' . number_format($totalHandedToBendahara, 0, ',', '.'),
                'total_handed_yayasan' => 'Rp ' . number_format($totalHandedToYayasan, 0, ',', '.'),
            ],
            'categories' => [
                'piket_to_bendahara' => $catPiket?->id,
                'bendahara_to_yayasan' => $catYayasan?->id,
            ],
            'active_admins' => $activeAdmins,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = CashFlowCategory::select('id', 'name')->orderBy('name')->get();
        $admins = Admin::select('id', 'name')->where('id', '!=', Auth::id())->orderBy('name')->get();
        return view('admins.cashflow.create-edit', compact('categories', 'admins'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CashFlowRequest $request)
    {
        $data = $request->validated();
        $cashflowCount = CashFlow::whereDate('created_at', now())->count();
        $data['payment_code'] = 'CT-' . now()->format('Ymd') . str_pad($cashflowCount + 1, 3, '0', STR_PAD_LEFT);
        $data['amount'] = preg_replace('/\D/', '', $data['amount']);
        $data['sender_id'] = Auth::id();
        $data['status'] = 'PENDING';
        if ($request->hasFile('proof_of_payment')) {
            $data['proof_of_payment'] = 'storage/' . $request->file('proof_of_payment')->store('images/cashflow', ['disk' => 'public']);
        }

        CashFlow::create($data);
        return redirect()->route('cashflow.index')->with('success', 'Berhasil Mengajukan Arus Kas');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CashFlow $cashflow)
    {
        $categories = CashFlowCategory::select('id', 'name')->orderBy('name')->get();
        $admins = Admin::select('id', 'name')->where('id', '!=', Auth::id())->orderBy('name')->get();
        return view('admins.cashflow.create-edit', compact('categories', 'admins', 'cashflow'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CashFlowRequest $request, CashFlow $cashflow)
    {
        $data = $request->validated();
        $data['amount'] = preg_replace('/\D/', '', $data['amount']);
        $data['sender_id'] = Auth::id();
        $data['status'] = 'PENDING';
        if ($request->hasFile('proof_of_payment')) {
            $data['proof_of_payment'] = 'storage/' . $request->file('proof_of_payment')->store('images/cashflow', ['disk' => 'public']);
        }

        $cashflow->update($data);
        return redirect()->route('cashflow.index')->with('success', 'Berhasil Mengajukan Arus Kas');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CashFlow $cashflow)
    {
        $cashflow->delete();
        return redirect()->route('cashflow.index')->with('success', 'Berhasil Menghapus Arus Kas');
    }

    public function approve($id)
    {
        $cashflow = CashFlow::findOrFail($id);
        // if (Auth::id() == $cashflow->receiver_id && $cashflow->status == CashFlow::STATUS_PENDING) {
        $cashflow->status = CashFlow::STATUS_APPROVED;
        $cashflow->save();

        return response()->json(['message' => 'Arus Kas telah disetujui.']);
        // }
        return response()->json(['message' => 'Tidak dapat menyetujui Arus Kas.'], 422);
    }

    public function reject(Request $request, $id)
    {
        $cashflow = CashFlow::findOrFail($id);
        // if (Auth::id() == $cashflow->receiver_id && $cashflow->status == CashFlow::STATUS_PENDING) {
        $cashflow->status = CashFlow::STATUS_REJECTED;
        $cashflow->reason = $request->reason;
        $cashflow->save();

        return response()->json(['message' => 'Arus Kas telah ditolak.']);
        // }
        return response()->json(['message' => 'Tidak dapat menolak Arus Kas.'], 422);
    }
}
