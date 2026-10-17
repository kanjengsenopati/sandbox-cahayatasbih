<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Admin;
use App\Models\School;
use App\Models\Classroom;
use App\Models\BillType;
use App\Models\BillItem;
use App\Models\Transaction;
use App\Models\SaldoHistory;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Yajra\DataTables\DataTables;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportTransactionExport;
use App\Traits\CanonicalBillTypeTrait;

class ReportTransactionController extends Controller
{
    use CanonicalBillTypeTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Laporan Transaksi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $requestedSchoolId = (request()->filled('school_id') && request()->school_id !== 'null')
            ? request()->school_id
            : null;
        $scopedSchoolIds = $this->resolveScopedSchoolIds($requestedSchoolId);
        $allowedBillItemIds = $this->getBillItemIdsBySchoolIds($scopedSchoolIds);

        if (request()->ajax()) {
            session()->save();

            $filterBillType = request()->filled('bill_type_id') ? request()->input('bill_type_id') : null;
            $matchingInfo = $filterBillType
                ? $this->getMatchingBillTypeInfo($filterBillType, $allowedBillItemIds)
                : ['ids' => [], 'names' => []];
            $matchedIds = $matchingInfo['ids'];

            $txTypeFilter = request()->input('type_data') ?: request()->input('type');

            $data = Transaction::where('status', Transaction::STATUS_PAID)
                ->with(['student.user', 'student.classroom', 'paymentMethod', 'admin', 'transactionDetails.bill.billType', 'transactionDetails.bill.academicYear', 'transactionDetails.saldoHistory', 'transactionDetails.savingHistory'])
                ->when(empty($scopedSchoolIds), function ($query) {
                    $query->whereRaw('1 = 0');
                })
                ->when(request()->filled('start_date'), function ($query) {
                    $query->whereDate('created_at', '>=', request()->start_date);
                })
                ->when(request()->filled('end_date'), function ($query) {
                    $query->whereDate('created_at', '<=', request()->end_date);
                })
                ->when(request()->filled('admin_id'), function ($query) {
                    $query->where('admin_id', request()->admin_id);
                })
                ->when(in_array($txTypeFilter, [Transaction::TYPE_BILL, Transaction::TYPE_SALDO, Transaction::TYPE_SAVING], true), function ($query) use ($txTypeFilter) {
                    $query->where('type', $txTypeFilter);
                })
                ->when(request()->filled('student_name'), function ($query) {
                    $searchName = strtolower(trim(request()->student_name));
                    $query->where(function ($q) use ($searchName) {
                        $q->whereRaw('LOWER(payment_code) LIKE ?', ['%' . $searchName . '%'])
                          ->orWhereHas('student', function ($sQ) use ($searchName) {
                              $sQ->whereRaw('LOWER(name) LIKE ?', ['%' . $searchName . '%'])
                                 ->orWhereRaw('LOWER(nis) LIKE ?', ['%' . $searchName . '%'])
                                 ->orWhereRaw('LOWER(nisn) LIKE ?', ['%' . $searchName . '%']);
                          });
                    });
                })
                ->schoolFilter('school_id', $requestedSchoolId)
                ->classroomFilter('classroom_id', request()->classroom_id)
                ->where(function ($uptQuery) use ($allowedBillItemIds) {
                    $uptQuery->where('type', '!=', Transaction::TYPE_BILL)
                        ->orWhere(function ($billTxQuery) use ($allowedBillItemIds) {
                            if ($allowedBillItemIds->isEmpty()) {
                                $billTxQuery->whereRaw('1 = 0');
                            } else {
                                $billTxQuery->whereExists(function ($sub) use ($allowedBillItemIds) {
                                    $sub->select(DB::raw(1))
                                        ->from('transaction_details')
                                        ->join('bills', 'transaction_details.bill_id', '=', 'bills.id')
                                        ->join('bill_types', 'bills.bill_type_id', '=', 'bill_types.id')
                                        ->whereColumn('transaction_details.transaction_id', 'transactions.id')
                                        ->whereNull('transaction_details.deleted_at')
                                        ->whereNull('bills.deleted_at')
                                        ->whereIn('bill_types.bill_item_id', $allowedBillItemIds);
                                });
                            }
                        });
                })
                ->when(!empty($filterBillType), function ($query) use ($matchedIds, $allowedBillItemIds) {
                    $query->where('type', Transaction::TYPE_BILL);

                    if (empty($matchedIds)) {
                        $query->whereRaw('1 = 0');
                    } else {
                        $query->whereExists(function ($subQuery) use ($matchedIds, $allowedBillItemIds) {
                            $subQuery->select(DB::raw(1))
                                ->from('transaction_details')
                                ->join('bills', 'transaction_details.bill_id', '=', 'bills.id')
                                ->join('bill_types', 'bills.bill_type_id', '=', 'bill_types.id')
                                ->whereColumn('transaction_details.transaction_id', 'transactions.id')
                                ->whereNull('transaction_details.deleted_at')
                                ->whereNull('bills.deleted_at')
                                ->whereIn('bill_types.id', $matchedIds);

                            if ($allowedBillItemIds->isNotEmpty()) {
                                $subQuery->whereIn('bill_types.bill_item_id', $allowedBillItemIds);
                            }
                        });
                    }
                })
                ->hasSchool()
                ->latest();

            if (request()->data == 'total') {
                // Fetch all types of transactions and sum them in a single fast query
                $totals = (clone $data)->selectRaw("
                    SUM(CASE WHEN type = '" . Transaction::TYPE_BILL . "' THEN pay_amount ELSE 0 END) as total_bill,
                    SUM(CASE WHEN type = '" . Transaction::TYPE_SALDO . "' THEN pay_amount ELSE 0 END) as total_saldo,
                    SUM(CASE WHEN type = '" . Transaction::TYPE_SAVING . "' THEN pay_amount ELSE 0 END) as saldo_saving
                ")->first();

                return response()->json([
                    'total_bill' => number_format($totals->total_bill ?? 0, 0, ',', '.'),
                    'total_saldo' => number_format($totals->total_saldo ?? 0, 0, ',', '.'),
                    'saldo_saving' => number_format($totals->saldo_saving ?? 0, 0, ',', '.'),
                ]);
            } elseif (request()->data == 'table') {
                $allowedBillItemIdsArray = $allowedBillItemIds->all();
                $filterScopedBillDetails = function ($details) use ($allowedBillItemIdsArray, $filterBillType, $matchedIds) {
                    return $details->filter(function ($detail) use ($allowedBillItemIdsArray, $filterBillType, $matchedIds) {
                        $bt = $detail->bill?->billType;
                        if (!$bt) {
                            return false;
                        }
                        if (!empty($allowedBillItemIdsArray) && !in_array($bt->bill_item_id, $allowedBillItemIdsArray, true)) {
                            return false;
                        }
                        if (!empty($filterBillType)) {
                            return in_array($bt->id, $matchedIds, true);
                        }
                        return true;
                    })->values();
                };

                return DataTables::of($data)
                    ->addColumn('amount', function ($data) use ($filterScopedBillDetails, $filterBillType) {
                        if ($data->type == Transaction::TYPE_BILL && !empty($filterBillType)) {
                            $scopedDetails = $filterScopedBillDetails($data->transactionDetails ?? collect());
                            if ($scopedDetails->isNotEmpty()) {
                                $sum = $scopedDetails->sum(fn($d) => (int) ($d->amount ?: ($d->bill?->amount ?? 0)));
                                if ($sum > 0) {
                                    return number_format($sum, 0, ',', '.');
                                }
                            }
                        }
                        return number_format($data->pay_amount, 0, ',', '.');
                    })
                    ->addColumn('date', function ($data) {
                        // Mengatur lokal bahasa Indonesia
                        Carbon::setLocale('id');

                        // Pastikan $data->created_at di-cast menjadi Carbon
                        $createdAt = Carbon::parse($data->created_at);

                        // Menggunakan translatedFormat untuk format tanggal dalam bahasa Indonesia
                        return $createdAt->translatedFormat('d F Y' . ' <br>' . 'H:i:s');
                    })

                    ->editColumn('type', function ($data) {
                        if ($data->type == Transaction::TYPE_BILL) {
                            return '<span class="badge badge-primary">Tagihan</span>';
                        } elseif ($data->type == Transaction::TYPE_SALDO) {
                            if ($data->transactionDetails?->first()?->saldoHistory?->type == SaldoHistory::TYPE_IN) {
                                return '<span class="badge badge-success">Top Up Saldo</span>';
                            } else {
                                return '<span class="badge badge-danger">Tarik Saldo</span>';
                            }
                        } elseif ($data->type == Transaction::TYPE_SAVING) {
                            return '<span class="badge badge-info">Tabungan</span>';
                        } else {
                            return '-';
                        }
                    })
                    ->addColumn('payment_method', function ($data) {
                        if ($data->paymentMethod?->type == PaymentMethod::TYPE_BALANCE) {
                            return '<span class="badge badge-success">Saldo</span>';
                        } elseif ($data->paymentMethod?->type == PaymentMethod::TYPE_XENDIT) {
                            return '<span class="badge badge-info">Xendit</span>';
                        } elseif ($data->paymentMethod?->type == PaymentMethod::TYPE_CASH) {
                            return '<span class="badge badge-warning">Tunai</span>';
                        } elseif ($data->paymentMethod?->type == PaymentMethod::TYPE_TRANSFER) {
                            return '<span class="badge badge-primary">Transfer</span>';
                        } else {
                            return '-';
                        }
                    })
                    ->addColumn('details', function ($data) use ($filterScopedBillDetails) {
                        $detailsCollection = $data->type == Transaction::TYPE_BILL
                            ? $filterScopedBillDetails($data->transactionDetails ?? collect())
                            : ($data->transactionDetails ?? collect());

                        $mapped = $detailsCollection->map(function ($detail) use ($data) {
                            if ($data->type == Transaction::TYPE_BILL) {
                                $bill = $detail->bill;
                                $billType = $bill?->billType?->name ?? 'Lain-lain';
                                $academicYear = $bill?->academicYear?->name ?? ($bill?->year ? (string)$bill->year : '-');
                                $monthName = '-';
                                if (!empty($bill?->month)) {
                                    try {
                                        $monthName = Carbon::createFromFormat('m', $bill->month)->translatedFormat('F');
                                    } catch (\Exception $e) {
                                        $monthName = (string)$bill->month;
                                    }
                                }
                                $year = $bill?->year ?? '';
                                $periodMonth = $monthName . ($year ? ' ' . $year : '');
                                $amount = $detail->amount ?? ($bill?->amount ?? 0);
                                return [
                                    'type' => 'BILL',
                                    'bill_type' => $billType,
                                    'academic_year' => $academicYear,
                                    'period_month' => $periodMonth,
                                    'month' => $monthName,
                                    'year' => $year,
                                    'amount' => (int) $amount,
                                ];
                            } elseif ($data->type == Transaction::TYPE_SALDO) {
                                $amount = $detail->amount ?? $data->pay_amount;
                                return [
                                    'type' => 'SALDO',
                                    'bill_type' => 'Saldo',
                                    'academic_year' => '-',
                                    'period_month' => $detail->saldoHistory?->description ?? 'Transaksi Saldo',
                                    'amount' => (int) $amount,
                                ];
                            } elseif ($data->type == Transaction::TYPE_SAVING) {
                                $amount = $detail->amount ?? $data->pay_amount;
                                return [
                                    'type' => 'SAVING',
                                    'bill_type' => 'Tabungan',
                                    'academic_year' => '-',
                                    'period_month' => $detail->savingHistory?->description ?? 'Transaksi Tabungan',
                                    'amount' => (int) $amount,
                                ];
                            }
                            return [
                                'type' => 'OTHER',
                                'bill_type' => 'Lainnya',
                                'academic_year' => '-',
                                'period_month' => 'Detail Transaksi',
                                'amount' => (int) $data->pay_amount,
                            ];
                        })->toArray();

                        return array_values($mapped);
                    })
                    ->addColumn('item', function ($data) use ($filterScopedBillDetails) {
                        $details = $data->type == Transaction::TYPE_BILL
                            ? $filterScopedBillDetails($data->transactionDetails ?? collect())
                            : ($data->transactionDetails ?? collect());
                        $totalCount = $details->count();
                        if ($totalCount == 0) {
                            return '<span class="text-muted fs-7">-</span>';
                        }

                        if ($data->type == Transaction::TYPE_BILL) {
                            $billTypeNames = $details->map(function ($d) {
                                return $d->bill?->billType?->name;
                            })->filter()->unique();

                            if ($billTypeNames->count() == 1) {
                                $summaryText = $totalCount . ' Item (' . $billTypeNames->first() . ')';
                            } elseif ($billTypeNames->count() > 1) {
                                $summaryText = $totalCount . ' Item (' . $billTypeNames->count() . ' Jenis Tagihan)';
                            } else {
                                $summaryText = $totalCount . ' Item Tagihan';
                            }
                        } elseif ($data->type == Transaction::TYPE_SALDO) {
                            $summaryText = '1 Detail (Saldo)';
                        } elseif ($data->type == Transaction::TYPE_SAVING) {
                            $summaryText = '1 Detail (Tabungan)';
                        } else {
                            $summaryText = $totalCount . ' Item';
                        }

                        return "<button type='button' class='btn btn-sm btn-light-primary btn-flex align-items-center py-1.5 px-3 fs-7 fw-bold btn-toggle-detail' data-id='{$data->id}'>
                            <i class='fas fa-list-ul me-2 text-primary fs-8'></i>
                            <span class='me-2'>{$summaryText}</span>
                            <i class='fas fa-chevron-down fs-8 toggle-arrow text-primary transition-transform'></i>
                        </button>";
                    })
                    ->addColumn('admin', function ($data) {
                        return $data->admin?->name ?? '<span class="badge badge-primary">CT-PAY</span>';
                    })
                    ->addColumn('action', function ($data) {
                        $actionDelete = route('report-transaction.destroy', $data->id);

                        $rawPhone = $data->student?->user?->phone;
                        $waButton = '';
                        if (!empty($rawPhone)) {
                            $phone = preg_replace('/\D/', '', $rawPhone);
                            if (str_starts_with($phone, '0')) {
                                $phone = '62' . substr($phone, 1);
                            }
                            $waMessage = \App\Services\SendNotifWaService::sendMessageBillNotification($data);
                            $waUrl = 'https://wa.me/' . $phone . '?text=' . rawurlencode($waMessage);
                            $parentName = htmlspecialchars($data->student?->user?->name ?? 'Wali Santri', ENT_QUOTES);
                            $waButton = "<a href='" . $waUrl . "' target='_blank' class='btn btn-sm btn-success me-1' title='Kirim WA ke Wali Santri ({$parentName})'><i class='fab fa-whatsapp'></i></a>";
                        }

                        return "<div class='d-flex gap-2 flex-nowrap justify-content-center'>" .
                            "<a href='" . route('transaction.invoice', $data->id) . "' target='_blank' class='btn btn-sm btn-primary' title='Cetak Invoice'><i class='fas fa-print'></i></a>" .
                            $waButton .
                            view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Laporan Transaksi']) .
                            "</div>";
                    })
                    ->rawColumns(['date', 'type', 'payment_method', 'item', 'action', 'admin'])
                    ->make(true);
            }
        }

        // Ambil list petugas yang berhak akses Entri Data Pembayaran atau memiliki riwayat transaksi sesuai scope UPT
        $admins = $this->getPaymentOfficers($scopedSchoolIds);

        // Hanya tampilkan lembaga operasional (PONDOK, MA, SMP) yang berada dalam cakupan hak akses admin (hasSchool)
        $schools = School::hasSchool()
            ->whereIn('type', [School::TYPE_PONDOK, School::TYPE_MA, School::TYPE_SMP])
            ->select('id', 'name', 'type')
            ->orderBy('name')
            ->get();

        $billTypesQuery = BillType::with(['billItem', 'academicYear'])
            ->select('id', 'name', 'academic_year_id', 'bill_item_id')
            ->whereNotIn('id', $this->getExcludedBillTypeIds());

        // Fail-Closed: jika $allowedBillItemIds kosong, paksa hasil kosong agar tidak bocor lintas UPT
        if ($allowedBillItemIds->isNotEmpty()) {
            $billTypesQuery->whereIn('bill_item_id', $allowedBillItemIds);
        } else {
            $billTypesQuery->whereRaw('1 = 0');
        }

        $billTypes = $billTypesQuery->get()->map(function ($item) {
            $posName = $item->billItem->name ?? null;
            $canonicalName = $this->getCanonicalBillTypeName($item->name, $posName);
            return (object) [
                'id' => $canonicalName,
                'name' => $canonicalName,
            ];
        })->unique('name')->sortBy('name')->values();

        $classroomsQuery = Classroom::select('id', 'name', 'school_id');
        if (!empty($scopedSchoolIds)) {
            $classroomsQuery->whereIn('school_id', $scopedSchoolIds);
        } else {
            $classroomsQuery->whereRaw('1 = 0');
        }
        $classrooms = $classroomsQuery->orderByRaw(\App\Helpers\DbCompat::classroomOrder())->get();

        return view('admins.report-transaction.index', compact('schools', 'classrooms', 'admins', 'billTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!Auth::user()->can('Delete Laporan Transaksi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $transaction = Transaction::with('transactionDetails')->findOrFail($id);
        // delete transaction details
        $transaction->transactionDetails()->delete();
        $transaction->delete();
        return redirect()->back()->with('success', 'Berhasil menghapus transaksi');
    }

    public function export()
    {
        if (!Auth::user()->can('Manage Laporan Transaksi')) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Maaf, Anda tidak memiliki akses untuk export laporan transaksi'], 403);
            }
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk export laporan transaksi');
        }

        session()->save();
        @set_time_limit(300);
        @ini_set('memory_limit', '1024M');

        try {
            $startDate = request()->filled('start_date') ? request()->start_date : 'All';
            $endDate = request()->filled('end_date') ? request()->end_date : 'All';
            $ext = in_array(request()->type, ['xlsx', 'csv'], true) ? request()->type : 'xlsx';

            return Excel::download(new ReportTransactionExport, "Laporan Transaksi {$startDate} - {$endDate}.{$ext}");
        } catch (\Throwable $e) {
            \Log::error('Export excel error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal memproses data export Excel: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal memproses data export Excel: ' . $e->getMessage());
        }
    }

    /**
     * Export data to Google Sheets: direct auto-create via Google Apps Script Web App
     * or fallback to TSV format for instant clipboard copy and paste.
     */
    public function exportSheets(Request $request)
    {
        if (!Auth::user()->can('Manage Laporan Transaksi')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        session()->save();
        @set_time_limit(300);
        @ini_set('memory_limit', '1024M');

        try {
            $export = new ReportTransactionExport();
            $dataResult = $export->generateDataArray();
            $tsvResult = $export->generateTsv();

            $startDate = $request->start_date ?: date('Y-m-01');
            $endDate = $request->end_date ?: date('Y-m-d');
            $sheetTitle = 'Laporan Transaksi ' . $startDate . ' s/d ' . $endDate;

            $gasWebappUrl = config('services.google.sheets_webapp_url') ?: env('GOOGLE_SHEETS_WEBAPP_URL');

            // Jika Google Apps Script Web App URL telah dikonfigurasi, kirim payload langsung
            if (!empty($gasWebappUrl)) {
                try {
                    $response = \Illuminate\Support\Facades\Http::timeout(90)
                        ->withOptions([
                            'allow_redirects' => [
                                'max' => 5,
                                'strict' => true, // Menjaga metode POST dan request body saat 302 redirect
                                'referer' => true,
                                'protocols' => ['https']
                            ]
                        ])
                        ->post($gasWebappUrl, [
                            'title' => $sheetTitle,
                            'values' => $dataResult['values'],
                        ]);

                    if ($response->successful()) {
                        $json = $response->json();
                        if (!empty($json['url'])) {
                            return response()->json([
                                'success' => true,
                                'mode' => 'direct',
                                'url' => $json['url'],
                                'count' => $dataResult['count'],
                                'tsv' => $tsvResult['tsv'],
                            ]);
                        }
                    }
                    \Log::warning('Google Apps Script response unhandled: ' . $response->body());
                } catch (\Throwable $gasError) {
                    \Log::error('GAS Webhook failed, falling back to TSV: ' . $gasError->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'mode' => 'fallback',
                'title' => $sheetTitle,
                'values' => $dataResult['values'],
                'tsv' => $tsvResult['tsv'],
                'count' => $tsvResult['count'],
                'gas_configured' => !empty($gasWebappUrl),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Export sheets error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses data export: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dynamic filters based on school_id with strict UPT scope isolation (Zero Filter Leakage)
     */
    public function getFilters(Request $request)
    {
        $requestedSchoolId = ($request->filled('school_id') && $request->school_id !== 'null')
            ? $request->school_id
            : null;
        $scopedSchoolIds = $this->resolveScopedSchoolIds($requestedSchoolId);
        $allowedBillItemIds = $this->getBillItemIdsBySchoolIds($scopedSchoolIds);

        // 1. Dapatkan Jenis Tagihan yang valid untuk cakupan Lembaga ini
        $billTypesQuery = BillType::with(['billItem', 'academicYear'])
            ->select('id', 'name', 'academic_year_id', 'bill_item_id')
            ->whereNotIn('id', $this->getExcludedBillTypeIds());

        // Fail-Closed: jika $allowedBillItemIds kosong, paksa hasil 0 agar tidak bocor lintas UPT / DEMO
        if ($allowedBillItemIds->isNotEmpty()) {
            $billTypesQuery->whereIn('bill_item_id', $allowedBillItemIds);
        } else {
            $billTypesQuery->whereRaw('1 = 0');
        }

        $billTypes = $billTypesQuery->get()->map(function ($item) {
            $posName = $item->billItem->name ?? null;
            $canonicalName = $this->getCanonicalBillTypeName($item->name, $posName);
            return [
                'id' => $canonicalName,
                'name' => $canonicalName,
            ];
        })->unique('name')->sortBy('name')->values();

        // 2. Dapatkan Petugas yang valid untuk cakupan Lembaga ini
        $admins = empty($scopedSchoolIds) ? collect() : $this->getPaymentOfficers($scopedSchoolIds);

        return response()->json([
            'bill_types' => $billTypes,
            'admins' => $admins,
        ]);
    }

    /**
     * Dapatkan daftar petugas yang berhak mengakses Entri Data Pembayaran
     * atau yang memiliki riwayat transaksi pembayaran pada cakupan UPT terkait.
     *
     * @param string|array|null $schoolScope
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getPaymentOfficers($schoolScope = null)
    {
        $schoolIds = is_array($schoolScope)
            ? array_values(array_filter($schoolScope))
            : (!empty($schoolScope) && $schoolScope !== 'null' ? [$schoolScope] : []);

        $adminsQuery = Admin::select('admins.id', 'admins.name');

        $paymentPermissions = [
            'Manage Tagihan',
            'Create Tagihan',
            'Edit Tagihan',
            'Manage Transaksi',
            'Create Transaksi',
        ];

        $paymentRoleNames = [
            'Super Admin',
            'superadmin',
            'Admin',
            'admin',
            'Bendahara',
            'Petugas Keuangan',
            'Admin Tagihan',
            'Keuangan',
        ];

        $txAdminIdsQuery = DB::table('transactions')
            ->whereNotNull('transactions.admin_id')
            ->whereNull('transactions.deleted_at');

        if (!empty($schoolIds)) {
            $classroomIds = Classroom::whereIn('school_id', $schoolIds)->pluck('id')->toArray();
            if (empty($classroomIds)) {
                $txAdminIds = [];
            } else {
                $txAdminIds = $txAdminIdsQuery
                    ->join('students', 'transactions.student_id', '=', 'students.id')
                    ->whereIn('students.classroom_id', $classroomIds)
                    ->distinct()
                    ->pluck('transactions.admin_id')
                    ->toArray();
            }
        } else {
            $txAdminIds = $txAdminIdsQuery->distinct()->pluck('transactions.admin_id')->toArray();
        }

        $adminsQuery->where(function ($q) use ($paymentPermissions, $paymentRoleNames, $schoolIds, $txAdminIds) {
            // 1. Pernah mencatat transaksi (riwayat transaksi nyata)
            if (!empty($txAdminIds)) {
                $q->whereIn('admins.id', $txAdminIds);
            }

            // 2. Memiliki hak akses ke Entri Data Pembayaran
            $q->orWhere(function ($accessQ) use ($paymentPermissions, $paymentRoleNames, $schoolIds) {
                // Pastikan user masih aktif jika belum ada riwayat transaksi
                $accessQ->where('admins.is_active', true);

                // Cek permission / role pembayaran
                $accessQ->where(function ($permQ) use ($paymentPermissions, $paymentRoleNames) {
                    $permQ->whereHas('permissions', function ($pq) use ($paymentPermissions) {
                        $pq->whereIn('name', $paymentPermissions);
                    })
                    ->orWhereHas('roles', function ($rq) use ($paymentPermissions, $paymentRoleNames) {
                        $rq->whereIn('name', $paymentRoleNames)
                           ->orWhere('name', 'like', '%Bendahara%')
                           ->orWhere('name', 'like', '%Keuangan%')
                           ->orWhere('name', 'like', '%Tagihan%')
                           ->orWhereHas('permissions', function ($rpq) use ($paymentPermissions) {
                               $rpq->whereIn('name', $paymentPermissions);
                           });
                    })
                    ->orWhereExists(function ($subRole) use ($paymentRoleNames) {
                        $subRole->select(DB::raw(1))
                            ->from('roles')
                            ->whereColumn('roles.id', 'admins.role_id')
                            ->where(function ($rrq) use ($paymentRoleNames) {
                                $rrq->whereIn('roles.name', $paymentRoleNames)
                                    ->orWhere('roles.name', 'like', '%Bendahara%')
                                    ->orWhere('roles.name', 'like', '%Keuangan%')
                                    ->orWhere('roles.name', 'like', '%Tagihan%');
                            });
                    });
                });

                // Batasi petugas pembayaran sesuai cakupan lembaga
                if (!empty($schoolIds)) {
                    $accessQ->where(function ($sq) use ($schoolIds) {
                        $sq->whereIn('admins.school_id', $schoolIds)
                           ->orWhereHas('adminSchool', function ($asq) use ($schoolIds) {
                               $asq->whereIn('school_id', $schoolIds);
                           })
                           ->orWhere('admins.access_scope', 'all')
                           ->orWhereHas('roles', function ($rq) {
                               $rq->whereIn('name', ['Super Admin', 'superadmin']);
                           })
                           ->orWhereExists(function ($srq) {
                               $srq->select(DB::raw(1))
                                   ->from('roles')
                                   ->whereColumn('roles.id', 'admins.role_id')
                                   ->whereIn('roles.name', ['Super Admin', 'superadmin']);
                           });
                    });
                }
            });
        });

        return $adminsQuery->orderBy('admins.name')->get();
    }
}
