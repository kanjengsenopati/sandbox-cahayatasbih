<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Admin;
use App\Models\School;
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

class ReportTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Laporan Transaksi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = Transaction::where('status', Transaction::STATUS_PAID)
                ->with(['student.user', 'student.classroom', 'paymentMethod', 'admin', 'transactionDetails.bill.billType', 'transactionDetails.bill.academicYear', 'transactionDetails.saldoHistory', 'transactionDetails.savingHistory'])
                ->when(request()->filled('start_date'), function ($query) {
                    $query->whereDate('created_at', '>=', request()->start_date);
                })
                ->when(request()->filled('end_date'), function ($query) {
                    $query->whereDate('created_at', '<=', request()->end_date);
                })
                ->when(request()->filled('admin_id'), function ($query) {
                    $query->where('admin_id', request()->admin_id);
                })
                ->when(request()->filled('student_name'), function ($query) {
                    $searchName = strtolower(trim(request()->student_name));
                    $query->whereHas('student', function ($sQ) use ($searchName) {
                        $sQ->whereRaw('LOWER(name) LIKE ?', ['%' . $searchName . '%'])
                           ->orWhereRaw('LOWER(nis) LIKE ?', ['%' . $searchName . '%'])
                           ->orWhereRaw('LOWER(nisn) LIKE ?', ['%' . $searchName . '%']);
                    });
                })
                ->schoolFilter('school_id', request()->school_id)
                ->classroomFilter('classroom_id', request()->classroom_id)
                ->when(request()->filled('bill_type_id'), function ($query) {
                    $val = request()->input('bill_type_id');
                    $query->where('type', Transaction::TYPE_BILL)
                        ->whereExists(function ($subQuery) use ($val) {
                            $subQuery->select(DB::raw(1))
                                ->from('transaction_details')
                                ->join('bills', 'transaction_details.bill_id', '=', 'bills.id')
                                ->join('bill_types', 'bills.bill_type_id', '=', 'bill_types.id')
                                ->whereColumn('transaction_details.transaction_id', 'transactions.id')
                                ->where(function($q) use ($val) {
                                    if (is_array($val)) {
                                        $q->whereIn('bill_types.id', $val)
                                          ->orWhereIn('bill_types.name', $val);
                                    } else {
                                        $q->where('bill_types.id', $val)
                                          ->orWhere('bill_types.name', $val);
                                    }
                                });
                        });
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
                return DataTables::of($data)
                    ->addColumn('amount', function ($data) {
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
                    ->addColumn('details', function ($data) {
                        $mapped = $data->transactionDetails->map(function ($detail) use ($data) {
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
                    ->addColumn('item', function ($data) {
                        $details = $data->transactionDetails;
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
        // ambil list admin dari transaction 
        $admin_ids = Transaction::where('status', Transaction::STATUS_PAID)->pluck('admin_id')->unique();
        // ambil list admin nama dari admin_ids
        $admins = Admin::whereIn('id', $admin_ids)->select('id', 'name')->orderBy('name')->get();
        $schools = School::orderBy('name')->get();
        $billTypesQuery = BillType::with('academicYear')->select('id', 'name', 'academic_year_id')->whereNotIn('id', [
            '02dae620-fc2c-4bf2-9e13-c5c1950e4d48',
            '615a34af-be2d-45f2-9830-720fea341a0c',
            'f3a25c77-f8c0-4882-8286-571bc57bf87c',
            'ce389861-40ab-4523-9364-3458e9dfda1d'
        ]);

        $schoolId = request()->school_id;
        if (!$schoolId) {
            $admin = Auth::user();
            if ($admin && !$admin->hasRole('Super Admin')) {
                $schoolIds = method_exists($admin, 'getSchoolIds') ? $admin->getSchoolIds() : [];
                $schoolId = $schoolIds[0] ?? null;
            }
        }

        if ($schoolId) {
            $school = \App\Models\School::find($schoolId);
            if ($school) {
                // Database-driven: filter bill_types berdasarkan Pos Bayar (bill_item_id)
                // Mapping School.type -> BillItem.name
                $billItemIds = $this->getBillItemIdsBySchoolType($school->type);
                if ($billItemIds->isNotEmpty()) {
                    $billTypesQuery->whereIn('bill_item_id', $billItemIds);
                }
            }
        }
        $billTypes = $billTypesQuery->orderBy('name')->get()->unique('name')->values();
        return view('admins.report-transaction.index', compact('schools', 'admins', 'billTypes'));
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
        return Excel::download(new ReportTransactionExport, 'Laporan Transaksi ' . request()->start_date . ' - ' . request()->end_date . '.' . request()->type);
    }

    /**
     * Get dynamic filters based on school_id
     */
    public function getFilters(Request $request)
    {
        $schoolId = $request->school_id;

        // 1. Dapatkan Jenis Tagihan yang valid untuk Lembaga ini
        // Database-driven: filter berdasarkan Pos Bayar (bill_item_id)
        $billTypesQuery = BillType::with('academicYear')->select('id', 'name', 'academic_year_id')->whereNotIn('id', [
            '02dae620-fc2c-4bf2-9e13-c5c1950e4d48',
            '615a34af-be2d-45f2-9830-720fea341a0c',
            'f3a25c77-f8c0-4882-8286-571bc57bf87c',
            'ce389861-40ab-4523-9364-3458e9dfda1d'
        ]);

        if ($schoolId) {
            $school = \App\Models\School::find($schoolId);
            if ($school) {
                // Database-driven: filter bill_types berdasarkan Pos Bayar (bill_item_id)
                $billItemIds = $this->getBillItemIdsBySchoolType($school->type);
                if ($billItemIds->isNotEmpty()) {
                    $billTypesQuery->whereIn('bill_item_id', $billItemIds);
                }
            }
        }

        $billTypes = $billTypesQuery->orderBy('name')->get()->unique('name')->values()->map(function($item) {
            return ['id' => $item->name, 'name' => $item->name];
        });

        // 2. Dapatkan Petugas yang valid untuk Lembaga ini
        $adminsQuery = Admin::select('id', 'name');
        
        if ($schoolId) {
            // Hanya petugas yang memiliki transaksi di lembaga ini, atau yang di-assign ke lembaga ini
            $adminsQuery->where(function($q) use ($schoolId) {
                $q->where('school_id', $schoolId)
                  ->orWhereHas('adminSchool', function($sq) use ($schoolId) {
                      $sq->where('school_id', $schoolId);
                  })
                  ->orWhereExists(function ($eq) use ($schoolId) {
                      $eq->select(DB::raw(1))
                          ->from('transactions')
                          ->join('students', 'transactions.student_id', '=', 'students.id')
                          ->join('classrooms', 'students.classroom_id', '=', 'classrooms.id')
                          ->whereColumn('transactions.admin_id', 'admins.id')
                          ->where('classrooms.school_id', $schoolId);
                  });
            });
        } else {
            // Default: Petugas yang pernah melakukan transaksi
            $adminsQuery->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('transactions')
                    ->whereColumn('transactions.admin_id', 'admins.id');
            });
        }

        $admins = $adminsQuery->orderBy('name')->get();

        return response()->json([
            'bill_types' => $billTypes,
            'admins' => $admins,
        ]);
    }

    /**
     * Map School.type ke BillItem IDs (Pos Bayar/UPT) untuk database-driven filtering.
     * Relasi: School.type -> BillItem.name -> bill_types.bill_item_id
     */
    private function getBillItemIdsBySchoolType(?string $schoolType)
    {
        // Mapping School.type ke nama Pos Bayar (BillItem) yang sesuai
        $mapping = [
            School::TYPE_PONDOK => ['PONDOK'],
            School::TYPE_MA     => ['MADRASAH ALIYAH'],
            School::TYPE_SMP    => ['SMP'],
        ];

        $type = strtoupper($schoolType ?? '');
        $billItemNames = $mapping[$type] ?? [];

        if (empty($billItemNames)) {
            return collect();
        }

        return BillItem::whereIn('name', $billItemNames)->pluck('id');
    }
}
