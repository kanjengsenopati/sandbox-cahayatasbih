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
                        return "<a href='" . asset($data->proof_of_payment) . "' data-lightbox='proof' data-title='Bukti Pembayaran'>
                        <img src='" . asset($data->proof_of_payment) . "' alt='Proof of Payment' class='img-thumbnail' style='cursor: pointer; width: 100px; height: 100px; object-fit: cover;' />
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


    public function summary()
    {
        $billAppFee = [
            '0a33726f-d9e7-4e78-bb09-db99e81314dd',
            'da831a2d-069f-46fa-b44d-d7b2cb6a9a8e',
            'a4e65f7e-c265-4da5-96a9-92076e33f141',
        ];

        $startDate = request()->filled('start_date') ? Carbon::parse(request()->start_date) : null;
        $endDate = request()->filled('end_date') ? Carbon::parse(request()->end_date) : null;

        // Menggabungkan query untuk totalIncomes dan totalCashflows
        $billQuery = Bill::whereIn('bill_type_id', $billAppFee)
            ->when($startDate, function ($query) use ($startDate) {
                $query->where(function ($subQuery) use ($startDate) {
                    $subQuery->where('year', '>', $startDate->year)
                        ->orWhere(function ($subSubQuery) use ($startDate) {
                            $subSubQuery->where('year', '=', $startDate->year)
                                ->where('month', '>=', $startDate->month);
                        });
                });
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->where(function ($subQuery) use ($endDate) {
                    $subQuery->where('year', '<', $endDate->year)
                        ->orWhere(function ($subSubQuery) use ($endDate) {
                            $subSubQuery->where('year', '=', $endDate->year)
                                ->where('month', '<=', $endDate->month);
                        });
                });
            });

        $totalIncomes = (clone $billQuery)
            ->where('status', 'PAID')
            ->sum('amount');

        $totalCashflows = $billQuery->sum('amount');

        $totalExpenses = CashFlow::where('type', CashFlow::TYPE_EXPENSE)
            ->where('status', CashFlow::STATUS_APPROVED)
            ->when($startDate, fn($query) => $query->whereDate('date', '>=', $startDate->toDateString()))
            ->when($endDate, fn($query) => $query->whereDate('date', '<=', $endDate->toDateString()))
            ->sum('amount');

        $remainingBalances = max($totalIncomes - $totalExpenses, 0);

        return response()->json([
            'total_incomes' => number_format($totalIncomes, 0, ',', '.'),
            'total_expenses' => number_format($totalExpenses, 0, ',', '.'),
            'remaining_balances' => number_format($remainingBalances, 0, ',', '.'),
            'total_cashflows' => number_format($totalCashflows, 0, ',', '.'),
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
