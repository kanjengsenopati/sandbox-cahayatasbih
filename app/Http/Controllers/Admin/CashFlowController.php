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
                ->when(auth()->user()->outlet_id, function($q) {
                    $q->where('outlet_id', auth()->user()->outlet_id);
                })
                ->when(!auth()->user()->outlet_id && request()->filled('outlet_id'), function($q) {
                    $q->where('outlet_id', request()->outlet_id);
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

        if (request()->type == 'piket_transactions') {
            $adminId = request()->input('admin_id');
            $startDate = request()->filled('start_date') ? Carbon::parse(request()->start_date) : null;
            $endDate = request()->filled('end_date') ? Carbon::parse(request()->end_date) : null;
            $academicYearId = request()->filled('academic_year_id') ? request()->academic_year_id : null;
            $outletId = auth()->user()->outlet_id ?? request()->input('outlet_id');

            $transactions = Transaction::where('transactions.status', Transaction::STATUS_PAID)
                ->where('transactions.type', Transaction::TYPE_BILL)
                ->where('transactions.admin_id', $adminId)
                ->join('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id')
                ->where('payment_methods.type', PaymentMethod::TYPE_CASH)
                ->when($startDate, fn($q) => $q->whereDate('transactions.paid_at', '>=', $startDate->toDateString()))
                ->when($endDate, fn($q) => $q->whereDate('transactions.paid_at', '<=', $endDate->toDateString()))
                ->when($academicYearId, function ($q) use ($academicYearId) {
                    $q->whereHas('transactionDetails.bill', function ($bq) use ($academicYearId) {
                        $bq->where('academic_year_id', $academicYearId);
                    });
                })
                ->when($outletId, function ($q) use ($outletId) {
                    $q->whereHas('admin', function ($aq) use ($outletId) {
                        $aq->where('outlet_id', $outletId);
                    });
                })
                ->with([
                    'transactionDetails.bill.billType',
                    'transactionDetails.bill.academicYear',
                    'transactionDetails.bill.student.classroom.school',
                    'transactionDetails.bill.classroom'
                ])
                ->select('transactions.*')
                ->latest('transactions.paid_at')
                ->get();

            $data = [];
            foreach ($transactions as $tx) {
                foreach ($tx->transactionDetails as $detail) {
                    $bill = $detail->bill;
                    if (!$bill) continue;

                    if ($academicYearId && $bill->academic_year_id != $academicYearId) {
                        continue;
                    }

                    $billTypeName = strtoupper($bill->billType?->name ?? '-');
                    $monthName = strtoupper($bill->translated_month ?? '-');
                    $academicYear = $bill->academicYear?->name ?? '-';
                    $billTypeFormatted = "{$billTypeName} {$monthName} {$academicYear}";

                    $school = $bill->student?->classroom?->school;
                    $isDemo = $school && ($school->type === \App\Models\School::TYPE_DEMO || stripos($school->name, 'DEMO') !== false);

                    $data[] = [
                        'date' => Carbon::parse($tx->paid_at)->translatedFormat('d F Y'),
                        'time' => Carbon::parse($tx->paid_at)->translatedFormat('H:i'),
                        'date_raw' => $tx->paid_at,
                        'bill_type' => $billTypeFormatted,
                        'amount' => $bill->amount,
                        'amount_formatted' => 'Rp ' . number_format($bill->amount, 0, ',', '.'),
                        'student_name' => $bill->student?->name ?? '-',
                        'classroom' => $bill->classroom?->name ?? $bill->student?->classroom?->name ?? '-',
                        'upt' => $bill->student?->classroom?->school?->name ?? '-',
                        'academic_year' => $bill->academicYear?->name ?? '-',
                        'is_demo' => (bool)$isDemo,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        }

        if (request()->type == 'summary') {
            return $this->summary();
        }

        $academicYears = \App\Models\AcademicYear::orderBy('name', 'desc')->get();
        $outlets = \App\Models\Outlet::orderBy('name')->get();
        $banks = \App\Models\Bank::where('is_active', true)->orderBy('name')->get();
        return view('admins.cashflow.index', compact('academicYears', 'outlets', 'banks'));
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

        $outletId = auth()->user()->outlet_id ?? request()->input('outlet_id');
        $startDate = request()->filled('start_date') ? Carbon::parse(request()->start_date) : null;
        $endDate = request()->filled('end_date') ? Carbon::parse(request()->end_date) : null;
        $academicYearId = request()->filled('academic_year_id') ? request()->academic_year_id : null;
        $billTypeName = request()->filled('bill_type_name') ? request()->bill_type_name : null;
        $paymentSource = request()->input('payment_source');

        // 1. Hitung Target & Realisasi dari SEMUA tagihan
        $billQuery = Bill::query();
        if ($academicYearId) {
            $billQuery->where('academic_year_id', $academicYearId);
        }
        if ($billTypeName) {
            $billQuery->whereHas('billType', function ($q) use ($billTypeName) {
                $q->where('name', $billTypeName);
            });
        }
        
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

        $totalCashflowsQuery = (clone $billQuery)->whereDoesntHave('student.classroom.school', function ($q) {
            $q->where('type', \App\Models\School::TYPE_DEMO)->orWhere('name', 'LIKE', '%DEMO%');
        });
        if ($paymentSource && $paymentSource !== 'saldo') {
            $totalCashflowsQuery->whereHas('billType.billTypeBank', function($q) use ($paymentSource) {
                $q->where('bank_id', $paymentSource);
            });
        }
        $totalCashflows = $totalCashflowsQuery->sum('amount'); // Target Total Pemasukan

        $totalIncomesQuery = (clone $billQuery)->where('status', 'PAID')->whereDoesntHave('student.classroom.school', function ($q) {
            $q->where('type', \App\Models\School::TYPE_DEMO)->orWhere('name', 'LIKE', '%DEMO%');
        });
        if ($paymentSource) {
            if ($paymentSource === 'saldo') {
                $totalIncomesQuery->whereHas('transactions', function($tq) {
                    $tq->where('transactions.status', 'PAID')
                       ->whereHas('paymentMethod', function($pq) {
                           $pq->where('type', 'BALANCE');
                       });
                });
            } else {
                $totalIncomesQuery->where(function($q) use ($paymentSource) {
                    $q->whereHas('transactions', function($tq) use ($paymentSource) {
                        $tq->where('transactions.status', 'PAID')
                           ->whereHas('activeProof', function($pq) use ($paymentSource) {
                               $pq->where('bank_id', $paymentSource);
                           });
                    })
                    ->orWhere(function($sub) use ($paymentSource) {
                        $sub->whereHas('billType.billTypeBank', function($tqb) use ($paymentSource) {
                            $tqb->where('bank_id', $paymentSource);
                        })->whereDoesntHave('transactions.activeProof');
                    });
                });
            }
        }
        $totalIncomes = $totalIncomesQuery->sum('amount'); // Realisasi Pemasukan

        $statusPemasukan = 'Sesuai';
        if ($totalIncomes < $totalCashflows) {
            $statusPemasukan = 'Defisit';
        } elseif ($totalIncomes > $totalCashflows) {
            $statusPemasukan = 'Surplus';
        }

        // 2. Pengeluaran & Sisa Saldo
        $totalExpenses = CashFlow::where('type', CashFlow::TYPE_EXPENSE)
            ->where('status', CashFlow::STATUS_APPROVED)
            ->when($outletId, fn($q) => $q->where('outlet_id', $outletId))
            ->when($startDate, fn($query) => $query->whereDate('date', '>=', $startDate->toDateString()))
            ->when($endDate, fn($query) => $query->whereDate('date', '<=', $endDate->toDateString()))
            ->sum('amount');

        $remainingBalances = max($totalIncomes - $totalExpenses, 0);

        // 2.5. Ambil semua nama jenis tagihan unik (untuk populate dropdown filter, tidak difilter bill_type_name)
        $allBillTypeNamesQuery = DB::table('bill_types as bt')
            ->join('bills as b', 'b.bill_type_id', '=', 'bt.id')
            ->whereNull('b.deleted_at')
            ->when($academicYearId, fn($q) => $q->where('b.academic_year_id', $academicYearId))
            ->select('bt.name')
            ->distinct()
            ->orderBy('bt.name')
            ->pluck('name')
            ->toArray();

        // 3. Breakdown per Jenis/Nama Pembayaran (Efficient DB-level aggregation)
        $breakdownQuery = DB::table('bills as b')
            ->join('bill_types as bt', 'b.bill_type_id', '=', 'bt.id')
            ->leftJoin('bill_items as bi', 'bt.bill_item_id', '=', 'bi.id')
            ->leftJoin('academic_years as ay', 'bt.academic_year_id', '=', 'ay.id')
            ->leftJoinSub($makeBillPaymentsSub($paymentSource), 'bp', 'b.id', '=', 'bp.bill_id')
            ->whereNull('b.deleted_at')
            ->select(
                'bt.name as type_name',
                'bi.name as unit_name',
                'ay.name as year_name',
                DB::raw('SUM(b.amount) as target'),
                DB::raw("SUM(CASE WHEN b.status = 'PAID' AND bp.bill_id IS NOT NULL THEN b.amount ELSE 0 END) as paid")
            )
            ->groupBy('bt.name', 'bi.name', 'ay.name');

        if ($paymentSource && $paymentSource !== 'saldo') {
            $breakdownQuery->whereExists(function($q) use ($paymentSource) {
                $q->select(DB::raw(1))
                  ->from('bill_type_banks as btb')
                  ->whereColumn('btb.bill_type_id', 'b.bill_type_id')
                  ->where('btb.bank_id', $paymentSource)
                  ->whereNull('btb.deleted_at');
            });
        }

        if ($academicYearId) {
            $breakdownQuery->where('b.academic_year_id', $academicYearId);
        }
        if ($billTypeName) {
            $breakdownQuery->where('bt.name', $billTypeName);
        }
        if ($startDate) {
            $breakdownQuery->where(function ($query) use ($startDate) {
                $query->where('b.year', '>', $startDate->year)
                    ->orWhere(function ($sub) use ($startDate) {
                        $sub->where('b.year', '=', $startDate->year)
                            ->where('b.month', '>=', $startDate->month);
                    });
            });
        }
        if ($endDate) {
            $breakdownQuery->where(function ($query) use ($endDate) {
                $query->where('b.year', '<', $endDate->year)
                    ->orWhere(function ($sub) use ($endDate) {
                        $sub->where('b.year', '=', $endDate->year)
                            ->where('b.month', '<=', $endDate->month);
                    });
            });
        }

        $breakdownRaw = $breakdownQuery->orderByDesc('target')->get();

        $breakdownBills = [];
        foreach ($breakdownRaw as $row) {
            $fullName = trim($row->type_name);
            $parts = [];
            if ($row->unit_name) $parts[] = $row->unit_name;
            if ($row->year_name) $parts[] = $row->year_name;
            if (!empty($parts)) {
                $fullName .= ' (' . implode(' - ', $parts) . ')';
            }

            $breakdownBills[] = [
                'name' => $fullName,
                'target_amount' => (int)$row->target,
                'target_formatted' => 'Rp ' . number_format($row->target, 0, ',', '.'),
                'total_amount' => (int)$row->paid,
                'total_formatted' => 'Rp ' . number_format($row->paid, 0, ',', '.'),
            ];
        }

        // 3.5. Detailed Breakdown per Jenis/Nama Pembayaran and Payment Source
        // Subquery: mapping bill_id -> payment method type (fresh closure agar tidak corrupt saat reuse)
        $makeBillPaymentsSub = function ($paymentSource = null) {
            $query = DB::table('transaction_details as td')
                ->join('transactions as t', 'td.transaction_id', '=', 't.id')
                ->join('payment_methods as pm', 't.payment_method_id', '=', 'pm.id')
                ->leftJoin('transaction_proofs as tp', function($join) {
                    $join->on('tp.transaction_id', '=', 't.id')
                         ->where('tp.is_active', '=', 1)
                         ->whereNull('tp.deleted_at');
                })
                ->where('t.status', 'PAID')
                ->where('t.type', 'BILL')
                ->whereNull('t.deleted_at')
                ->whereNull('td.deleted_at')
                ->whereNotNull('td.bill_id')
                ->select('td.bill_id', DB::raw('MAX(pm.type) as pm_type'))
                ->groupBy('td.bill_id');

            if ($paymentSource) {
                if ($paymentSource === 'saldo') {
                    $query->where('pm.type', '=', 'BALANCE');
                } else {
                    $query->where(function($q) use ($paymentSource) {
                        $q->where('tp.bank_id', '=', $paymentSource)
                          ->orWhere(function($sub) use ($paymentSource) {
                              $sub->whereNull('tp.bank_id')
                                  ->whereExists(function($ex) use ($paymentSource) {
                                      $ex->select(DB::raw(1))
                                         ->from('bill_type_banks as btb')
                                         ->join('bills as bl', 'bl.bill_type_id', '=', 'btb.bill_type_id')
                                         ->whereColumn('bl.id', 'td.bill_id')
                                         ->where('btb.bank_id', $paymentSource)
                                         ->whereNull('btb.deleted_at');
                                  });
                          });
                    });
                }
            }

            return $query;
        };

        $breakdownDetailQuery = DB::table('bills as b')
            ->join('bill_types as bt', 'b.bill_type_id', '=', 'bt.id')
            ->leftJoin('bill_items as bi', 'bt.bill_item_id', '=', 'bi.id')
            ->leftJoin('academic_years as ay', 'bt.academic_year_id', '=', 'ay.id')
            ->leftJoinSub($makeBillPaymentsSub($paymentSource), 'bp', 'b.id', '=', 'bp.bill_id')
            ->whereNull('b.deleted_at')
            ->select(
                'bt.id as bill_type_id',
                'bt.name as type_name',
                'bi.name as unit_name',
                'ay.name as year_name',
                DB::raw('SUM(b.amount) as target'),
                DB::raw("SUM(CASE WHEN b.status = 'PAID' AND bp.bill_id IS NOT NULL THEN b.amount ELSE 0 END) as paid"),
                DB::raw("SUM(CASE WHEN b.status = 'PAID' AND bp.bill_id IS NOT NULL AND bp.pm_type = 'CASH' THEN b.amount ELSE 0 END) as paid_cash"),
                DB::raw("SUM(CASE WHEN b.status = 'PAID' AND bp.bill_id IS NOT NULL AND bp.pm_type = 'BALANCE' THEN b.amount ELSE 0 END) as paid_balance"),
                DB::raw("SUM(CASE WHEN b.status = 'PAID' AND bp.bill_id IS NOT NULL AND bp.pm_type NOT IN ('CASH', 'BALANCE') THEN b.amount ELSE 0 END) as paid_transfer")
            )
            ->groupBy('bt.id', 'bt.name', 'bi.name', 'ay.name');

        if ($paymentSource && $paymentSource !== 'saldo') {
            $breakdownDetailQuery->whereExists(function($q) use ($paymentSource) {
                $q->select(DB::raw(1))
                  ->from('bill_type_banks as btb')
                  ->whereColumn('btb.bill_type_id', 'b.bill_type_id')
                  ->where('btb.bank_id', $paymentSource)
                  ->whereNull('btb.deleted_at');
            });
        }

        if ($academicYearId) {
            $breakdownDetailQuery->where('b.academic_year_id', $academicYearId);
        }
        if ($billTypeName) {
            $breakdownDetailQuery->where('bt.name', $billTypeName);
        }
        if ($startDate) {
            $breakdownDetailQuery->where(function ($query) use ($startDate) {
                $query->where('b.year', '>', $startDate->year)
                    ->orWhere(function ($sub) use ($startDate) {
                        $sub->where('b.year', '=', $startDate->year)
                            ->where('b.month', '>=', $startDate->month);
                    });
            });
        }
        if ($endDate) {
            $breakdownDetailQuery->where(function ($query) use ($endDate) {
                $query->where('b.year', '<', $endDate->year)
                    ->orWhere(function ($sub) use ($endDate) {
                        $sub->where('b.year', '=', $endDate->year)
                            ->where('b.month', '<=', $endDate->month);
                    });
            });
        }

        $breakdownDetailRaw = $breakdownDetailQuery->orderByDesc('target')->get();

        $billTypeIds = $breakdownDetailRaw->pluck('bill_type_id')->filter()->unique()->toArray();
        $billTypeBanks = \App\Models\BillTypeBank::whereIn('bill_type_id', $billTypeIds)
            ->whereHas('bank', function ($q) {
                $q->whereNull('deleted_at')->where('is_active', true);
            })
            ->with(['bank' => function ($q) {
                $q->where('is_active', true);
            }])
            ->get()
            ->groupBy('bill_type_id');

        $breakdownDetailBills = [];
        foreach ($breakdownDetailRaw as $row) {
            $fullName = trim($row->type_name);
            $parts = [];
            if ($row->unit_name) $parts[] = $row->unit_name;
            if ($row->year_name) $parts[] = $row->year_name;
            if (!empty($parts)) {
                $fullName .= ' (' . implode(' - ', $parts) . ')';
            }

            $banks = [];
            if (isset($billTypeBanks[$row->bill_type_id])) {
                foreach ($billTypeBanks[$row->bill_type_id] as $btBank) {
                    if ($btBank->bank) {
                        $banks[] = [
                            'bank_name' => $btBank->bank->name,
                            'account_number' => $btBank->bank->account_number,
                            'account_name' => $btBank->bank->account_name,
                        ];
                    }
                }
            }

            $breakdownDetailBills[] = [
                'name' => $fullName,
                'target_amount' => (int)$row->target,
                'target_formatted' => 'Rp ' . number_format($row->target, 0, ',', '.'),
                'total_amount' => (int)$row->paid,
                'total_formatted' => 'Rp ' . number_format($row->paid, 0, ',', '.'),
                'paid_cash' => (int)$row->paid_cash,
                'paid_cash_formatted' => 'Rp ' . number_format($row->paid_cash, 0, ',', '.'),
                'paid_balance' => (int)$row->paid_balance,
                'paid_balance_formatted' => 'Rp ' . number_format($row->paid_balance, 0, ',', '.'),
                'paid_transfer' => (int)$row->paid_transfer,
                'paid_transfer_formatted' => 'Rp ' . number_format($row->paid_transfer, 0, ',', '.'),
                'banks' => $banks,
            ];
        }

        // 4. Breakdown per Sumber Pembayaran — query independen langsung ke DB
        // Menggunakan fresh subquery (closure) agar tidak ada bug reuse query builder.
        // Join: bills -> bill_types -> transaction_details -> transactions -> payment_methods
        $sourceRaw = DB::table('bills as b')
            ->join('bill_types as bt', 'b.bill_type_id', '=', 'bt.id')
            ->join('transaction_details as td', function ($join) {
                $join->on('td.bill_id', '=', 'b.id')
                     ->whereNull('td.deleted_at')
                     ->whereNotNull('td.bill_id');
            })
            ->join('transactions as t', function ($join) {
                $join->on('t.id', '=', 'td.transaction_id')
                     ->where('t.status', '=', 'PAID')
                     ->where('t.type', '=', 'BILL')
                     ->whereNull('t.deleted_at');
            })
            ->join('payment_methods as pm', 'pm.id', '=', 't.payment_method_id')
            ->join('students as s', 'b.student_id', '=', 's.id')
            ->join('classrooms as c', 's.classroom_id', '=', 'c.id')
            ->join('schools as sc', 'c.school_id', '=', 'sc.id')
            ->where('sc.type', '!=', \App\Models\School::TYPE_DEMO)
            ->where('sc.name', 'NOT LIKE', '%DEMO%')
            ->whereNull('b.deleted_at')
            ->where('b.status', 'PAID')
            ->when($academicYearId, fn($q) => $q->where('b.academic_year_id', $academicYearId))
            ->when($billTypeName, fn($q) => $q->where('bt.name', $billTypeName))
            ->when($startDate, function ($q) use ($startDate) {
                $q->where(function ($inner) use ($startDate) {
                    $inner->where('b.year', '>', $startDate->year)
                          ->orWhere(function ($sub) use ($startDate) {
                              $sub->where('b.year', '=', $startDate->year)
                                  ->where('b.month', '>=', $startDate->month);
                          });
                });
            })
            ->when($endDate, function ($q) use ($endDate) {
                $q->where(function ($inner) use ($endDate) {
                    $inner->where('b.year', '<', $endDate->year)
                          ->orWhere(function ($sub) use ($endDate) {
                              $sub->where('b.year', '=', $endDate->year)
                                  ->where('b.month', '<=', $endDate->month);
                          });
                });
            })
            ->select(
                DB::raw("SUM(CASE WHEN pm.type = 'CASH'    THEN b.amount ELSE 0 END) as paid_cash"),
                DB::raw("SUM(CASE WHEN pm.type = 'BALANCE' THEN b.amount ELSE 0 END) as paid_balance"),
                DB::raw("SUM(CASE WHEN pm.type NOT IN ('CASH','BALANCE') THEN b.amount ELSE 0 END) as paid_transfer")
            )
            ->first();

        $sourceRawQuery = DB::table('bills as b')
            ->join('bill_types as bt', 'b.bill_type_id', '=', 'bt.id')
            ->join('transaction_details as td', function ($join) {
                $join->on('td.bill_id', '=', 'b.id')
                     ->whereNull('td.deleted_at')
                     ->whereNotNull('td.bill_id');
            })
            ->join('transactions as t', function ($join) {
                $join->on('t.id', '=', 'td.transaction_id')
                     ->where('t.status', '=', 'PAID')
                     ->where('t.type', '=', 'BILL')
                     ->whereNull('t.deleted_at');
            })
            ->join('payment_methods as pm', 'pm.id', '=', 't.payment_method_id')
            ->leftJoin('transaction_proofs as tp', function($join) {
                $join->on('tp.transaction_id', '=', 't.id')
                     ->where('tp.is_active', '=', 1)
                     ->whereNull('tp.deleted_at');
            })
            ->join('students as s', 'b.student_id', '=', 's.id')
            ->join('classrooms as c', 's.classroom_id', '=', 'c.id')
            ->join('schools as sc', 'c.school_id', '=', 'sc.id')
            ->where('sc.type', '!=', \App\Models\School::TYPE_DEMO)
            ->where('sc.name', 'NOT LIKE', '%DEMO%')
            ->whereNull('b.deleted_at')
            ->where('b.status', 'PAID');

        if ($paymentSource) {
            if ($paymentSource === 'saldo') {
                $sourceRawQuery->where('pm.type', '=', 'BALANCE');
            } else {
                $sourceRawQuery->where(function($q) use ($paymentSource) {
                    $q->where('tp.bank_id', '=', $paymentSource)
                      ->orWhere(function($sub) use ($paymentSource) {
                          $sub->whereNull('tp.bank_id')
                              ->whereExists(function($ex) use ($paymentSource) {
                                  $ex->select(DB::raw(1))
                                     ->from('bill_type_banks as btb')
                                     ->whereColumn('btb.bill_type_id', 'b.bill_type_id')
                                     ->where('btb.bank_id', $paymentSource)
                                     ->whereNull('btb.deleted_at');
                              });
                      });
                });
            }
        }

        $sourceRaw = $sourceRawQuery
            ->when($academicYearId, fn($q) => $q->where('b.academic_year_id', $academicYearId))
            ->when($billTypeName, fn($q) => $q->where('bt.name', $billTypeName))
            ->when($startDate, function ($q) use ($startDate) {
                $q->where(function ($inner) use ($startDate) {
                    $inner->where('b.year', '>', $startDate->year)
                          ->orWhere(function ($sub) use ($startDate) {
                              $sub->where('b.year', '=', $startDate->year)
                                  ->where('b.month', '>=', $startDate->month);
                          });
                });
            })
            ->when($endDate, function ($q) use ($endDate) {
                $q->where(function ($inner) use ($endDate) {
                    $inner->where('b.year', '<', $endDate->year)
                          ->orWhere(function ($sub) use ($endDate) {
                              $sub->where('b.year', '=', $endDate->year)
                                  ->where('b.month', '<=', $endDate->month);
                          });
                });
            })
            ->select(
                DB::raw("SUM(CASE WHEN pm.type = 'CASH' AND " . ($paymentSource ? '1=0' : '1=1') . " THEN b.amount ELSE 0 END) as paid_cash"),
                DB::raw("SUM(CASE WHEN pm.type = 'BALANCE' AND " . (($paymentSource && $paymentSource !== 'saldo') ? '1=0' : '1=1') . " THEN b.amount ELSE 0 END) as paid_balance"),
                DB::raw("SUM(CASE WHEN pm.type NOT IN ('CASH','BALANCE') AND " . (($paymentSource && $paymentSource === 'saldo') ? '1=0' : '1=1') . " THEN b.amount ELSE 0 END) as paid_transfer")
            )
            ->first();

        $sourceBreakdown = [
            'Tunai'            => (int)($sourceRaw->paid_cash     ?? 0),
            'Debit Saldo'      => (int)($sourceRaw->paid_balance  ?? 0),
            'Transfer Bank'    => (int)($sourceRaw->paid_transfer ?? 0),
        ];

        // 5. Tracing Petugas Piket (Tunai)
        if ($paymentSource) {
            $piketOfficers = collect();
            $totalPiketCash = 0;
        } else {
            $piketOfficers = Transaction::where('transactions.status', Transaction::STATUS_PAID)
                ->where('transactions.type', Transaction::TYPE_BILL)
                ->join('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id')
                ->where('payment_methods.type', PaymentMethod::TYPE_CASH)
                ->join('students as s', 'transactions.student_id', '=', 's.id')
                ->join('classrooms as c', 's.classroom_id', '=', 'c.id')
                ->join('schools as sc', 'c.school_id', '=', 'sc.id')
                ->where('sc.type', '!=', \App\Models\School::TYPE_DEMO)
                ->where('sc.name', 'NOT LIKE', '%DEMO%')
                ->when($startDate, fn($q) => $q->whereDate('transactions.paid_at', '>=', $startDate->toDateString()))
                ->when($endDate, fn($q) => $q->whereDate('transactions.paid_at', '<=', $endDate->toDateString()))
                ->when($academicYearId, function ($q) use ($academicYearId) {
                    $q->whereHas('transactionDetails.bill', function ($bq) use ($academicYearId) {
                        $bq->where('academic_year_id', $academicYearId);
                    });
                })
                ->join('admins', 'transactions.admin_id', '=', 'admins.id')
                ->when($outletId, fn($q) => $q->where('admins.outlet_id', $outletId))
                ->select('admins.id', 'admins.name', DB::raw('SUM(transactions.pay_amount) as total_cash'), DB::raw('COUNT(transactions.id) as total_txs'))
                ->groupBy('admins.id', 'admins.name')
                ->get()
                ->map(function ($officer) use ($outletId) {
                    $catPiket = CashFlowCategory::where('name', 'Serah Terima Piket ke Bendahara')->first();
                    $handedOver = CashFlow::where('sender_id', $officer->id)
                        ->where('status', CashFlow::STATUS_APPROVED)
                        ->when($outletId, fn($q) => $q->where('outlet_id', $outletId))
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

            $totalPiketCash = Transaction::where('transactions.status', Transaction::STATUS_PAID)
                ->join('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id')
                ->where('payment_methods.type', PaymentMethod::TYPE_CASH)
                ->join('students as s', 'transactions.student_id', '=', 's.id')
                ->join('classrooms as c', 's.classroom_id', '=', 'c.id')
                ->join('schools as sc', 'c.school_id', '=', 'sc.id')
                ->where('sc.type', '!=', \App\Models\School::TYPE_DEMO)
                ->where('sc.name', 'NOT LIKE', '%DEMO%')
                ->when($startDate, fn($q) => $q->whereDate('transactions.paid_at', '>=', $startDate->toDateString()))
                ->when($endDate, fn($q) => $q->whereDate('transactions.paid_at', '<=', $endDate->toDateString()))
                ->when($academicYearId, function ($q) use ($academicYearId) {
                    $q->whereHas('transactionDetails.bill', function ($bq) use ($academicYearId) {
                        $bq->where('academic_year_id', $academicYearId);
                    });
                })
                ->join('admins', 'transactions.admin_id', '=', 'admins.id')
                ->when($outletId, fn($q) => $q->where('admins.outlet_id', $outletId))
                ->sum('transactions.pay_amount');
        }

        // 6. Workflow Stats Piping
        $catPiket = CashFlowCategory::where('name', 'Serah Terima Piket ke Bendahara')->first();
        $catYayasan = CashFlowCategory::where('name', 'Serah Terima Bendahara ke Yayasan')->first();

        $totalHandedToBendahara = CashFlow::where('status', CashFlow::STATUS_APPROVED)
            ->when($outletId, fn($q) => $q->where('outlet_id', $outletId))
            ->when($catPiket, fn($q) => $q->where('cash_flow_category_id', $catPiket->id))
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate->toDateString()))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate->toDateString()))
            ->sum('amount');

        $totalHandedToYayasan = CashFlow::where('status', CashFlow::STATUS_APPROVED)
            ->when($outletId, fn($q) => $q->where('outlet_id', $outletId))
            ->when($catYayasan, fn($q) => $q->where('cash_flow_category_id', $catYayasan->id))
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate->toDateString()))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate->toDateString()))
            ->sum('amount');

        $activeAdmins = Admin::select('id', 'name')
            ->where('id', '!=', Auth::id())
            ->when($outletId, fn($q) => $q->where('outlet_id', $outletId))
            ->orderBy('name')->get();

        $diffPemasukan = $totalIncomes - $totalCashflows;

        return response()->json([
            'total_incomes' => number_format($totalIncomes, 0, ',', '.'),
            'total_expenses' => number_format($totalExpenses, 0, ',', '.'),
            'remaining_balances' => number_format($remainingBalances, 0, ',', '.'),
            'total_cashflows' => number_format($totalCashflows, 0, ',', '.'),
            'status_pemasukan' => $statusPemasukan,
            'status_pemasukan_diff' => $diffPemasukan,
            'status_pemasukan_diff_formatted' => ($diffPemasukan >= 0 ? 'Rp ' : 'Rp -') . number_format(abs($diffPemasukan), 0, ',', '.'),
            'percentage_realisasi' => number_format($totalCashflows > 0 ? ($totalIncomes / $totalCashflows) * 100 : 0, 2, ',', '.') . '%',
            'breakdown_bills' => $breakdownBills,
            'breakdown_detail_bills' => $breakdownDetailBills,
            'all_bill_type_names' => $allBillTypeNamesQuery,
            'breakdown_sources' => [
                'tunai' => number_format($sourceBreakdown['Tunai'], 0, ',', '.'),
                'saldo' => number_format($sourceBreakdown['Debit Saldo'], 0, ',', '.'),
                'transfer' => number_format($sourceBreakdown['Transfer Bank'], 0, ',', '.'),
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
            'active_banks' => \App\Models\Bank::where('is_active', true)->get()->map(function($bank) {
                return [
                    'bank_name' => $bank->name,
                    'account_number' => $bank->account_number,
                    'account_name' => $bank->account_name,
                ];
            })->toArray(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = CashFlowCategory::select('id', 'name')->orderBy('name')->get();
        $admins = Admin::select('id', 'name')->where('id', '!=', Auth::id())->orderBy('name')->get();
        $outlets = \App\Models\Outlet::orderBy('name')->get();
        return view('admins.cashflow.create-edit', compact('categories', 'admins', 'outlets'));
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
        if (auth()->user()->outlet_id) {
            $data['outlet_id'] = auth()->user()->outlet_id;
        } else {
            $data['outlet_id'] = $request->outlet_id;
        }
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
        $outlets = \App\Models\Outlet::orderBy('name')->get();
        return view('admins.cashflow.create-edit', compact('categories', 'admins', 'cashflow', 'outlets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CashFlowRequest $request, CashFlow $cashflow)
    {
        $data = $request->validated();
        $data['amount'] = preg_replace('/\D/', '', $data['amount']);
        $data['sender_id'] = Auth::id();
        if (auth()->user()->outlet_id) {
            $data['outlet_id'] = auth()->user()->outlet_id;
        } else {
            $data['outlet_id'] = $request->outlet_id;
        }
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
