<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bill;
use App\Models\User;
use App\Models\School;
use App\Models\Contact;
use App\Models\Student;
use App\Models\BillType;
use App\Models\Classroom;
use App\Models\Transaction;
use App\Models\SaldoHistory;

use Exception;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\SavingHistory;
use App\Models\PpdbRegistration;
use App\Models\TransactionProof;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\SendNotifWaService;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Jobs\SendToPushNotificationJob;
use App\Jobs\SendToWhatsappNotificationJob;
use App\Http\Requests\Admin\BillPaymentRequest;
use App\Http\Requests\Admin\UpdateTransactionStatusRequest;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Tagihan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $schools = School::orderBy('name')->hasSchool()->get();
        // Dapatkan semua Tahun Ajaran untuk filter
        $academicYears = \App\Models\AcademicYear::orderBy('start_year', 'desc')->get();

        if ($studentId = request()->student_id) {
            TransactionService::syncStudentBillsFromPaidTransactions($studentId);
            TransactionService::ensureStudentBillsSyncedFromRate($studentId);
            $student = Student::with(['user', 'classroom.school', 'classroomHistories.classroom'])->find($studentId);
            if (!$student) {
                return redirect()->to(route('bill.index'))->with('error', 'Data siswa tidak ditemukan atau telah dihapus.');
            }

            $academicYearId = request()->academic_year_id;

            // Reset academicYearId if it starts before student's entry year
            if ($academicYearId) {
                $selectedYear = \App\Models\AcademicYear::find($academicYearId);
                $selectedStartYear = $selectedYear?->getStartYearSafe();
                if ($selectedStartYear !== null && $student->getEntryYear() > $selectedStartYear) {
                    $academicYearId = null;
                    request()->query->set('academic_year_id', null);
                    request()->request->set('academic_year_id', null);
                }
            }

            $billMonth = $this->getBills($studentId, BillType::TYPE_MONTHLY, $academicYearId);
            $billOthers = $this->getBills($studentId, BillType::TYPE_OTHER, $academicYearId);

            return view('admins.bill.index', compact('student', 'billMonth', 'billOthers', 'schools', 'academicYears'));
        }

        if (request()->ajax()) {
            if (request()->tab === 'archive') {
                return $this->getArchiveTransactionData();
            }
            return $this->getTransactionData();
        }

        return view('admins.bill.index', compact('schools', 'academicYears'));
    }

    private function getBills($studentId, $type, $academicYearId = null)
    {
        $query = BillType::with(['billItem', 'academicYear', 'bills' => function ($query) use ($studentId, $academicYearId) {
                $query->where('student_id', $studentId);
                if ($academicYearId) {
                    $query->where('academic_year_id', $academicYearId);
                }
                $query->with(['classroom.school', 'transactions.admin', 'transactions.user']);
            }])
            ->where('type', $type)
            ->whereHas('bills', function ($query) use ($studentId, $academicYearId) {
                $query->where('student_id', $studentId);
                if ($academicYearId) {
                    $query->where('academic_year_id', $academicYearId);
                }
            });

        return $query->latest()
            ->get()
            ->map(fn($item) => $this->calculateBillTotals($item, $studentId));
    }

    private function calculateBillTotals($item, $studentId)
    {
        $bills = $item->bills;
        $upperName = strtoupper($item->name ?? '');

        $isZarkasi = str_contains($upperName, 'ZARKASI');
        $isAplikasi = str_contains($upperName, 'APLIKASI');
        $isSyahriah = str_contains($upperName, 'SYAHR');

        if ($item->type === 'MONTHLY') {
            $totalBill = 0;
            $startYear = $item->academicYear?->start_year ?? date('Y');
            $endYear = $item->academicYear?->end_year ?? ($startYear + 1);
            foreach (array_merge(range(7, 12), range(1, 6)) as $m) {
                $y = ($m >= 7) ? $startYear : $endYear;
                $bDet = $bills->firstWhere('month', (int)$m) ?? $bills->firstWhere('month', (string)$m);
                if ($bDet && $bDet->amount > 0) {
                    $totalBill += $bDet->amount;
                } else {
                    $totalBill += TransactionService::resolveStudentRateForBillType($studentId, $item, $m, $y);
                }
            }
            $item->total_bill = $totalBill;
        } else {
            $item->total_bill = $bills->sum('amount');
        }

        if ($isZarkasi || $isAplikasi || $isSyahriah) {
            // Include payments from past/other bill types of the same generic category
            $matchingBills = \App\Models\Bill::where('student_id', $studentId)
                ->whereHas('billType', function ($query) use ($isZarkasi, $isAplikasi, $isSyahriah) {
                    $query->where(function ($q) use ($isZarkasi, $isAplikasi, $isSyahriah) {
                        if ($isZarkasi) $q->orWhere('name', 'like', '%ZARKASI%');
                        if ($isAplikasi) $q->orWhere('name', 'like', '%APLIKASI%');
                        if ($isSyahriah) $q->orWhere('name', 'like', '%SYAHR%');
                    });
                })->get();
            $item->total_paid = $matchingBills->sum('paid_amount');
        } else {
            $item->total_paid = $bills->sum('paid_amount');
        }

        $item->total_unpaid = max(0, $item->total_bill - $item->total_paid);

        return $item;
    }

    private function getTransactionData()
    {
        $transactions = Transaction::with(['student', 'paymentMethod', 'activeProof.bank', 'transactionProofs.bank', 'transactionDetails.bill.billType'])
            ->whereHas('paymentMethod', fn($query) => $query->where('type', PaymentMethod::TYPE_TRANSFER))
            ->where('type', Transaction::TYPE_BILL)
            ->where('status', Transaction::STATUS_PENDING_CONFIRMATION)
            ->hasSchool()
            ->latest();

        return DataTables::of($transactions)
            ->addColumn('proof', fn($transaction) => $this->formatProofColumn($transaction))
            ->editColumn('pay_amount', function ($transaction) {
                $amountHtml = 'Rp ' . number_format($transaction->pay_amount, 0, ',', '.');
                
                $billsInfo = [];
                foreach ($transaction->transactionDetails as $detail) {
                    $bill = $detail->bill;
                    if ($bill) {
                        $billTypeName = $bill->billType->name ?? 'Tagihan';
                        $monthName = $bill->translated_month ?? $bill->getTranslatedMonthAttribute();
                        $period = $monthName ? "{$monthName} {$bill->year}" : $bill->year;
                        $billsInfo[] = "<span class='text-muted fs-8'>• {$billTypeName} ({$period})</span>";
                    }
                }
                
                if (!empty($billsInfo)) {
                    $amountHtml .= '<br><div class="d-flex flex-column mt-1">' . implode('', $billsInfo) . '</div>';
                }
                
                return $amountHtml;
            })
            ->editColumn('status', fn($transaction) => $this->formatStatusColumn($transaction))
            ->addColumn('action', fn($transaction) => $this->formatActionColumn($transaction))
            ->addColumn('bank_recipient', function ($transaction) {
                $proof = $transaction->activeProof ?? $transaction->transactionProofs->first();
                $bank = $proof?->bank;
                if (!$bank) return '-';
                return "{$bank->name}<br><small class='text-muted'>No. Rek: {$bank->account_number}</small><br><small class='text-muted'>A.N: {$bank->account_name}</small>";
            })
            ->rawColumns(['proof', 'action', 'status', 'bank_recipient', 'pay_amount'])
            ->make(true);
    }

    private function formatProofColumn($transaction)
    {
        $proof = $transaction->activeProof ?? $transaction->transactionProofs->first();
        $proofUrl = $proof?->proof_image_url ?? $proof?->proof_image;
        if (!$proofUrl) return '-';
        return "<img src='{$proofUrl}' class='img-fluid img-thumbnail cursor-pointer view-proof-image' data-src='{$proofUrl}' style='max-width: 80px; height: auto; border-radius: 8px;'>";
    }

    private function formatStatusColumn($transaction)
    {
        $statusLabels = [
            Transaction::STATUS_PENDING => 'Belum Dibayar',
            Transaction::STATUS_PENDING_PAYMENT => 'Menunggu Pembayaran',
            Transaction::STATUS_PENDING_CONFIRMATION => 'Menunggu Verifikasi',
            Transaction::STATUS_PAID => 'Lunas',
            Transaction::STATUS_EXPIRED => 'Kedaluwarsa',
            Transaction::STATUS_CANCELLED => 'Dibatalkan',
            Transaction::STATUS_REJECTED => 'Ditolak'
        ];

        $statusClass = [
            Transaction::STATUS_PENDING => 'primary',
            Transaction::STATUS_PENDING_PAYMENT => 'warning',
            Transaction::STATUS_PENDING_CONFIRMATION => 'danger',
            Transaction::STATUS_PAID => 'success',
            Transaction::STATUS_EXPIRED => 'secondary',
            Transaction::STATUS_CANCELLED => 'secondary',
            Transaction::STATUS_REJECTED => 'danger'
        ];

        $statusText = $statusLabels[$transaction->status] ?? '';
        $statusBadge = "<span class='badge badge-{$statusClass[$transaction->status]}'>{$statusText}</span>";

        if ($transaction->activeProof) {
            if ($transaction->activeProof?->ocr_status === 'processed') {
                $statusBadge .= "<br><span class='badge badge-light-success mt-1' style='font-size: 0.7rem;'><i class='fas fa-robot text-success me-1'></i> AI Checked</span>";
                if ($transaction->activeProof?->ocr_amount) {
                    $statusBadge .= "<br><small class='text-muted'>Nominal Terbaca: Rp " . number_format($transaction->activeProof?->ocr_amount, 0, ',', '.') . "</small>";
                }
            } elseif ($transaction->activeProof?->ocr_status === 'failed') {
                $statusBadge .= "<br><span class='badge badge-light-danger mt-1' style='font-size: 0.7rem;'><i class='fas fa-robot text-danger me-1'></i> AI Gagal Membaca</span>";
            }
        }

        if ($transaction->status === Transaction::STATUS_REJECTED && $transaction->activeProof) {
            $statusBadge .= "<br><small class='text-danger d-block mt-1 fw-bold'>{$transaction->activeProof?->note}</small>";
        }

        if ($transaction->created_at) {
            $formattedDate = strtoupper(\Carbon\Carbon::parse($transaction->created_at)->translatedFormat('d-M-Y , H : i'));
            $statusBadge .= "<br><div class='text-slate-400 mt-1' style='font-size: 12px; font-style: italic; color: #94a3b8;'>{$formattedDate}</div>";
        }

        return $statusBadge;
    }

    private function formatActionColumn($transaction)
    {
        if (!Auth::user()->can('Edit Tagihan')) {
            return '';
        }

        if ($transaction->status === Transaction::STATUS_PAID) {
            return "<span class='badge badge-success'>Lunas</span>";
        } elseif ($transaction->status === Transaction::STATUS_REJECTED) {
            return "<span class='badge badge-danger'>Ditolak</span>";
        }

        $options = [
            Transaction::STATUS_PAID => 'Lunas',
            Transaction::STATUS_REJECTED => 'Cek Ulang'
        ];

        $action = "<select class='form-control status-transaction' name='status' id='status-{$transaction->id}' onchange='updateStatus(this.value, \"{$transaction->id}\")'>
                <option value=''>Pilih Status</option>";

        foreach ($options as $value => $label) {
            $selected = $transaction->status == $value ? 'selected' : '';
            $action .= "<option value='{$value}' {$selected}>{$label}</option>";
        }

        $action .= "</select>
                <input type='hidden' name='note' id='note-{$transaction->id}' value='{$transaction->activeProof?->note}'>
                <button class='btn btn-primary btn-sm mt-2' onclick='saveStatus(\"{$transaction->id}\")'>Simpan</button>";

        return $action;
    }

    private function getArchiveTransactionData()
    {
        $transactions = Transaction::with(['student', 'paymentMethod', 'activeProof.bank', 'transactionProofs.bank', 'admin', 'transactionDetails.bill.billType'])
            ->whereHas('paymentMethod', fn($query) => $query->where('type', PaymentMethod::TYPE_TRANSFER))
            ->where('type', Transaction::TYPE_BILL)
            ->where('status', Transaction::STATUS_PAID)
            ->where('is_deleted_from_archive', false)
            ->hasSchool();

        if ($startDate = request()->start_date) {
            $transactions->whereDate('updated_at', '>=', $startDate);
        }
        if ($endDate = request()->end_date) {
            $transactions->whereDate('updated_at', '<=', $endDate);
        }

        $transactions->latest('updated_at');

        return DataTables::of($transactions)
            ->addColumn('proof', fn($transaction) => $this->formatProofColumn($transaction))
            ->editColumn('pay_amount', function ($transaction) {
                $amountHtml = 'Rp ' . number_format($transaction->pay_amount, 0, ',', '.');
                
                $billsInfo = [];
                foreach ($transaction->transactionDetails as $detail) {
                    $bill = $detail->bill;
                    if ($bill) {
                        $billTypeName = $bill->billType->name ?? 'Tagihan';
                        $monthName = $bill->translated_month ?? $bill->getTranslatedMonthAttribute();
                        $period = $monthName ? "{$monthName} {$bill->year}" : $bill->year;
                        $billsInfo[] = "<span class='text-muted fs-8'>• {$billTypeName} ({$period})</span>";
                    }
                }
                
                if (!empty($billsInfo)) {
                    $amountHtml .= '<br><div class="d-flex flex-column mt-1">' . implode('', $billsInfo) . '</div>';
                }
                
                return $amountHtml;
            })
            ->editColumn('status', fn($transaction) => $this->formatStatusColumn($transaction))
            ->addColumn('action', fn($transaction) => $this->formatArchiveActionColumn($transaction))
            ->addColumn('bank_recipient', function ($transaction) {
                $proof = $transaction->activeProof ?? $transaction->transactionProofs->first();
                $bank = $proof?->bank;
                if (!$bank) return '-';
                return "{$bank->name}<br><small class='text-muted'>No. Rek: {$bank->account_number}</small><br><small class='text-muted'>A.N: {$bank->account_name}</small>";
            })
            ->addColumn('officer', function ($transaction) {
                return $transaction->admin?->name ?? '-';
            })
            ->addColumn('updated_at_formatted', function ($transaction) {
                return $transaction->updated_at ? \Carbon\Carbon::parse($transaction->updated_at)->translatedFormat('d F Y H:i') : '-';
            })
            ->rawColumns(['proof', 'action', 'status', 'bank_recipient', 'pay_amount'])
            ->make(true);
    }

    private function formatArchiveActionColumn($transaction)
    {
        if (!Auth::user()->can('Edit Tagihan')) {
            return '';
        }

        $daysDiff = $transaction->updated_at ? $transaction->updated_at->diffInDays(now()) : 0;
        $canDelete = $daysDiff > 30;

        if ($canDelete) {
            return "<button class='btn btn-danger btn-sm delete-archive-btn' data-id='{$transaction->id}'>
                        <i class='fas fa-trash me-1'></i> Hapus
                    </button>";
        } else {
            return "<button class='btn btn-danger btn-sm' disabled data-bs-toggle='tooltip' title='Hapus dinonaktifkan karena usia arsip kurang dari 30 hari'>
                        <i class='fas fa-trash me-1'></i> Hapus
                    </button>";
        }
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
    public function store(BillPaymentRequest $request)
    {
        $studentId = $request->student_id;
        // Saran: Tambahkan bill_id agar user bisa bayar tagihan LAIN secara bersamaan
        // $billId = $request->bill_id; 
        // $lockKey = "pay_lock_{$studentId}_{$billId}"; 
        
        $lockKey = "student_transaction_{$studentId}";

        // COBA DAPATKAN KUNCI (ATOMIC)
        // block(0) artinya: Coba lock, jika gagal langsung return false (jangan tunggu).
        // owner: method ini memegang kunci selama 5 menit (300 detik) jika terjadi crash.
        $lock = Cache::lock($lockKey, 300);

        if (!$lock->get()) {
            // Jika gagal dapat kunci (artinya ada transaksi lain sedang jalan)
            return redirect()->back()->with('error', 'Transaksi sedang diproses, mohon tunggu sebentar.');
        }

        // --- MULAI AREA AMAN ---
        DB::beginTransaction();

        try {
            $paymentMethodType = $request->payment_method;

            // Validasi Logika Bisnis Tambahan (Double Check Database)
            foreach ($request->bill_ids as $billId) {
                $isPaid = Bill::where('id', $billId)->where('status', 'PAID')->exists();
                if($isPaid) throw new Exception("Tagihan dengan ID {$billId} sudah lunas");
            }

            $transaction = TransactionService::createTransaction($request, $paymentMethodType, Transaction::TYPE_BILL);
            
            if ($transaction->status == Transaction::STATUS_PAID && $transaction?->student?->user?->phone) {
                TransactionService::dispatchNotifications($transaction);
            }
            
            DB::commit();

            // Lepas kunci agar user bisa transaksi lagi
            $lock->release();

            return redirect()->back()->with('success', "Transaksi pembayaran berhasil");

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);

            // Lepas kunci jika error, supaya user tidak terkunci 5 menit
            $lock->release();

            return redirect()->back()->with('error', "Transaksi pembayaran gagal: " . $th->getMessage());
        }
    }

    // public function store(BillPaymentRequest $request)
    // {
    //     $studentId = $request->student_id; // Assuming student_id is part of the request
    //     $cacheKey = "student_transaction_{$studentId}";

    //     // Check if there's an active transaction in the cache
    //     if (Cache::has($cacheKey)) {
    //         return redirect()->back()->with('error', 'Transaksi sedang diproses, silakan coba lagi nanti');
    //     }

    //     // Set a cache entry to lock the transaction
    //     Cache::put($cacheKey, true, now()->addMinutes(5)); // Lock for 5 minutes

    //     DB::beginTransaction();

    //     try {
    //         $paymentMethodType = $request->payment_method;

    //         $transaction = TransactionService::createTransaction($request, $paymentMethodType, Transaction::TYPE_BILL);
    //         if ($transaction->status == Transaction::STATUS_PAID && $transaction?->student?->user?->phone) {
    //             TransactionService::dispatchNotifications($transaction);
    //         }
    //         DB::commit();

    //         // Clear the cache entry
    //         Cache::forget($cacheKey);

    //         return redirect()->back()->with('success', "Transaksi pembayaran berhasil");
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         Log::error($th);

    //         // Ensure the cache entry is cleared in case of an error
    //         Cache::forget($cacheKey);

    //         return redirect()->back()->with('error', "Transaksi pembayaran gagal");
    //     }
    // }

    /**
     * Display the specified resource.
     */

    public function show(string $id)
    {
        if (!Auth::user()->can('Manage Tagihan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $transaction = Transaction::with('student', 'paymentMethod', 'activeProof')
            ->findOrFail($id);
        return view('admins.bill.show', compact('transaction'));
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
    public function update(UpdateTransactionStatusRequest $request, string $id)
    {
        $transaction = Transaction::findOrFail($id);
        $data = $request->validated();
        $data['admin_id'] = Auth::id();
        if ($request->status == Transaction::STATUS_PAID) {
            $data['paid_at'] = now();
        }

        $result = TransactionService::updateStatusPaymentTransfer($data, $transaction);

        if ($result['status']) {
            return $this->postSuccessResponse($result['message'], $result['transaction']);
        } else {
            return $this->failedResponse($result['message']);
        }
    }

    public function destroy(string $id)
    {
        if (!Auth::user()->can('Edit Tagihan')) {
            return response()->json([
                'code' => '403',
                'message' => 'Anda tidak memiliki hak akses untuk menghapus arsip.'
            ], 403);
        }

        $transaction = Transaction::findOrFail($id);

        $daysDiff = $transaction->updated_at ? $transaction->updated_at->diffInDays(now()) : 0;
        if ($daysDiff <= 30) {
            return response()->json([
                'code' => '400',
                'message' => 'Gagal menghapus: Arsip hanya dapat dihapus jika usianya sudah lebih dari 30 hari.'
            ], 400);
        }

        $transaction->update([
            'is_deleted_from_archive' => true
        ]);

        return response()->json([
            'code' => '200',
            'message' => 'Arsip riwayat pembayaran berhasil dihapus.'
        ]);
    }

    public function getBillData()
    {

        $id = request()->student_id;
        $student = Student::find($id);
        if (!$student) {
            return redirect()->route('bill.index')->with('error', 'Data siswa tidak ditemukan.');
        }

        // Mengambil tagihan bulanan
        $billMonth = BillType::with('billItem', 'academicYear')
            ->where('type', BillType::TYPE_MONTHLY)
            ->whereHas('bills', function ($query) use ($id) {
                $query->where('student_id', $id);
            })
            ->latest()
            ->get()
            ->map(function ($item) use ($id) {
                $item->total_unpaid = Bill::where('student_id', $id)
                    ->where('bill_type_id', $item->id)
                    ->sum(\DB::raw('amount - paid_amount'));

                $item->total_paid = Bill::where('student_id', $id)
                    ->where('bill_type_id', $item->id)
                    ->sum('paid_amount');

                return $item;
            });

        // Mengambil tagihan lainnya
        $billOthers = BillType::where('type', BillType::TYPE_OTHER)
            ->whereHas('bills', function ($query) use ($id) {
                $query->where('student_id', $id);
            })
            ->latest()
            ->get()
            ->map(function ($item) use ($id) {
                $item->total_unpaid = Bill::where('student_id', $id)
                    ->where('bill_type_id', $item->id)
                    ->sum(\DB::raw('amount - paid_amount'));
                $item->total_paid = Bill::where('student_id', $id)
                    ->where('bill_type_id', $item->id)
                    ->sum('paid_amount');

                return $item;
            });


        return view('admins.bill.show', compact('student', 'billMonth', 'billOthers'));
    }

    public function summaryBill()
    {
        $requestData = request()->validate([
            'student_id'   => 'required|exists:students,id',
            'bill_type_id' => 'required|exists:bill_types,id',
        ]);

        $student = Student::with(['user', 'classroom.school'])->findOrFail($requestData['student_id']);
        $billType = BillType::findOrFail($requestData['bill_type_id']);

        $bills = Bill::with('transactions')
            ->where('student_id', $student->id)
            ->where('bill_type_id', $billType->id)
            ->orderBy('month')
            ->get();

        $isZarkasi = str_contains(strtoupper($billType->name ?? ''), 'ZARKASI');
        $isAplikasi = str_contains(strtoupper($billType->name ?? ''), 'APLIKASI');
        $isSyahriah = str_contains(strtoupper($billType->name ?? ''), 'SYAHR');

        if ($isZarkasi) {
            $totalBill = 550000;
        } elseif ($isAplikasi) {
            $totalBill = 120000;
        } elseif ($isSyahriah) {
            $totalBill = 6000000;
        } else {
            if ($billType->type === 'MONTHLY') {
                $sampleBill = $bills->firstWhere('amount', '>', 0);
                $sampleAmount = $sampleBill ? $sampleBill->amount : ($billType->billItem->amount ?? 0);
                if ($sampleAmount <= 0) {
                    $sampleAmount = \App\Models\Bill::where('bill_type_id', $billType->id)->where('amount', '>', 0)->value('amount') ?? 0;
                }
                $totalBill = $sampleAmount > 0 ? ($sampleAmount * 12) : $bills->sum('amount');
            } else {
                $totalBill = $bills->sum('amount');
            }
        }

        $totalPaid   = $bills->sum('paid_amount');
        $totalUnpaid = max(0, $totalBill - $totalPaid);

        $summary = [
            'total_bill'   => $totalBill,
            'total_paid'   => $totalPaid,
            'total_unpaid' => $totalUnpaid,
        ];

        $paymentMethods = PaymentMethod::latest()->get();

        return view('admins.bill.summary', compact(
            'student',
            'billType',
            'bills',
            'summary',
            'paymentMethods'
        ));
    }

    // public function summaryBill()
    // {
    //     $requestData = request()->only(['student_id', 'bill_type_id']);

    //     $student = Student::findOrFail($requestData['student_id']);
    //     $billType = BillType::findOrFail($requestData['bill_type_id']);

    //     $billType->load(['bills' => function ($query) use ($student, $billType) {
    //         $query->where('student_id', $student->id)->where('bill_type_id', $billType->id);
    //     }]);

    //     $totalBill = $billType->bills->sum('amount');
    //     $totalPaid = $billType->bills->where('status', Bill::STATUS_PAID)->sum('amount');
    //     $totalUnpaid = $billType->bills->where('status', Bill::STATUS_UNPAID)->sum('amount');

    //     $billType->total_bill = $totalBill ?? 0;
    //     $billType->total_paid = $totalPaid ?? 0;
    //     $billType->total_unpaid = $totalUnpaid ?? 0;

    //     $bills = Bill::with('transactions')->where('student_id', $requestData['student_id'])
    //         ->where('bill_type_id', $requestData['bill_type_id'])
    //         ->orderBy('month', 'asc')
    //         ->get();


    //     $paymentMethods = PaymentMethod::latest()->get();

    //     return view('admins.bill.summary', compact('student', 'billType', 'bills', 'paymentMethods'));
    // }

    public function changeStatus()
    {
        $user = Auth::user();
        $isAuthorized = false;

        if ($user) {
            if ($user->hasRole('Super Admin') || $user->can('Edit Status Tagihan')) {
                $isAuthorized = true;
            } elseif ($user->hasRole('Bendahara')) {
                $username = strtolower($user->username ?? '');
                $name = strtolower($user->name ?? '');
                if (
                    str_contains($username, 'khoirus') || 
                    str_contains($username, 'paramita') ||
                    str_contains($name, 'khoirus') || 
                    str_contains($name, 'paramita')
                ) {
                    $isAuthorized = true;
                }
            }
        }

        if (!$isAuthorized) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $requestData = request()->only(['bill_id', 'status']);

        DB::beginTransaction();
        try {
            $bill = Bill::findOrFail($requestData['bill_id']);
            $oldStatus = $bill->status;
            $newStatus = $requestData['status'];

            if ($oldStatus != $newStatus) {
                $bill->status = $newStatus;
                if ($newStatus == Bill::STATUS_PAID) {
                    $bill->paid_amount = $bill->amount;
                } else {
                    $bill->paid_amount = 0;
                }
                $bill->save();

                // Hapus atau batalkan transaksi jika status dikembalikan ke UNPAID
                if ($newStatus == Bill::STATUS_UNPAID) {
                    $transactionDetails = $bill->transactionDetails;
                    foreach ($transactionDetails as $detail) {
                        $transaction = $detail->transaction;
                        if ($transaction) {
                            // Jika pembayaran menggunakan Saldo, kembalikan saldo siswa (Refund)
                            if ($transaction->paymentMethod?->type == \App\Models\PaymentMethod::TYPE_BALANCE || $detail->saldo_history_id) {
                                $student = $transaction->student;
                                if ($student) {
                                    $student->saldo += $detail->amount ?? $bill->amount;
                                    $student->save();

                                    // Catat riwayat refund saldo
                                    \App\Models\SaldoHistory::create([
                                        'student_id' => $student->id,
                                        'amount' => $detail->amount ?? $bill->amount,
                                        'type' => \App\Models\SaldoHistory::TYPE_IN,
                                        'description' => 'Refund Pembatalan Tagihan Sebesar Rp.' . number_format($detail->amount ?? $bill->amount, 0, ',', '.'),
                                        'status' => \App\Models\SaldoHistory::STATUS_SUCCESS,
                                        'usage' => \App\Models\SaldoHistory::USAGE_TOPUP,
                                        'balance_before' => $student->saldo - ($detail->amount ?? $bill->amount),
                                        'balance_after' => $student->saldo,
                                    ]);
                                }
                            }

                            // Hapus detail transaksi, dan hapus transaksi induk jika tidak memiliki detail lain
                            $detail->delete();
                            if ($transaction->transactionDetails()->whereNull('deleted_at')->count() == 0) {
                                $transaction->delete();
                            }
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Status tagihan berhasil diubah');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return redirect()->back()->with('error', 'Gagal mengubah status tagihan: ' . $th->getMessage());
        }
    }

    public function deleteStudentBill(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'bill_type_id' => 'required',
        ]);

        $student = Student::findOrFail($request->student_id);
        $billType = BillType::findOrFail($request->bill_type_id);
        $billType->bills()->where('student_id', $student->id)->delete();

        return redirect()->back()->with('success', 'Tagihan berhasil dihapus');
    }

    public function downloadTemplate(Request $request)
    {
        $schoolId = $request->school_id;
        $classroomIds = $request->classroom_ids ?? ($request->classroom_id ? [$request->classroom_id] : []);
        $academicYearId = $request->academic_year_id;
        $billTypeIds = $request->bill_type_ids ?? ($request->bill_type_id ? [$request->bill_type_id] : []);

        $school = School::find($schoolId);
        $schoolName = $school ? str_replace(' ', '_', $school->name) : 'Semua_UPT';

        $classroomName = 'Semua_Kelas';
        if (!empty($classroomIds)) {
            $classrooms = Classroom::whereIn('id', (array)$classroomIds)->pluck('name')->toArray();
            if (count($classrooms) === 1) {
                $classroomName = str_replace(' ', '_', $classrooms[0]);
            } elseif (count($classrooms) > 1) {
                $classroomName = count($classrooms) . '_Kelas';
            }
        }

        $fileName = "Template_Pembayaran_{$schoolName}_{$classroomName}.xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\StudentBillTemplateExport($schoolId, $classroomIds, $academicYearId, $billTypeIds),
            $fileName
        );
    }

    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx',
            'academic_year_id' => 'required|exists:academic_years,id',
            'bill_type_id' => 'required|exists:bill_types,id',
        ]);

        $file = $request->file('file');
        $academicYearId = $request->academic_year_id;
        $billTypeId = $request->bill_type_id;

        $billType = BillType::with('academicYear')->where('id', $billTypeId)->firstOrFail();

        if ($billType->academic_year_id && $billType->academic_year_id !== $academicYearId) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis tagihan yang dipilih tidak sesuai dengan Tahun Ajaran yang dipilih.',
            ], 422);
        }

        $academicYearName = $billType->academicYear?->name ?? \App\Models\AcademicYear::find($academicYearId)?->name;

        // Baca file Excel
        $rows = \Maatwebsite\Excel\Facades\Excel::toArray([], $file)[0];

        if (empty($rows) || count($rows) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'File Excel kosong atau tidak memiliki baris data.',
            ], 422);
        }

        $headers = $rows[0];
        $idColIndex = count($headers) - 1;
        foreach ($headers as $idx => $headerName) {
            if (strtolower(trim($headerName ?? '')) === 'id siswa' || strtolower(trim($headerName ?? '')) === 'id_siswa') {
                $idColIndex = $idx;
                break;
            }
        }

        $previewData = [];
        $isValidGlobal = true;

        $paymentColIndices = [];
        for ($c = 3; $c < $idColIndex; $c++) {
            $paymentColIndices[] = $c;
        }
        if (empty($paymentColIndices)) {
            $paymentColIndices = [3];
        }

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $name = $row[1] ?? '';
            $className = $row[2] ?? '';
            $studentId = $row[$idColIndex] ?? null;

            if (empty($name) && empty($studentId)) {
                continue; // Skip baris kosong
            }

            $totalAmount = 0;
            foreach ($paymentColIndices as $colIdx) {
                $rawVal = $row[$colIdx] ?? 0;
                if (is_string($rawVal)) {
                    $cleaned = preg_replace('/[^0-9]/', '', $rawVal);
                    $cellVal = intval($cleaned);
                } else {
                    $cellVal = intval($rawVal);
                }
                if ($cellVal > 0) {
                    $totalAmount += $cellVal;
                }
            }

            $student = null;
            $status = 'VALID';
            $message = '';

            if ($studentId) {
                $student = Student::with('classroom')->find($studentId);
            }

            if (!$student && $name) {
                $student = Student::where('name', 'like', $name)
                    ->whereHas('classroom', function($q) use ($className) {
                        $q->where('name', 'like', $className);
                    })
                    ->first();
            }

            if (!$student) {
                $status = 'INVALID';
                $message = 'Siswa tidak ditemukan';
                $isValidGlobal = false;
            } else {
                if ($totalAmount <= 0) {
                    $status = 'INVALID';
                    $message = 'Nominal bayar harus > 0';
                    $isValidGlobal = false;
                } else {
                    $bill = Bill::where('student_id', $student->id)
                        ->where('bill_type_id', $billTypeId)
                        ->where('academic_year_id', $academicYearId)
                        ->first();
                    
                    if ($bill && $bill->status === Bill::STATUS_PAID) {
                        $status = 'INVALID';
                        $message = 'Tagihan sudah lunas';
                        $isValidGlobal = false;
                    }
                }
            }

            $previewData[] = [
                'student_id' => $student ? $student->id : null,
                'name' => $student ? $student->name : $name,
                'classroom' => $student && $student->classroom ? $student->classroom->name : $className,
                'amount' => $totalAmount,
                'status' => $status,
                'message' => $message,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $previewData,
            'is_valid_global' => $isValidGlobal,
            'bill_type_name' => $billType->formatted_name ?? $billType->name,
            'academic_year_name' => $academicYearName,
        ]);
    }

    public function confirmImport(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'bill_type_id' => 'required|exists:bill_types,id',
            'data' => 'required|array',
            'data.*.student_id' => 'required|exists:students,id',
            'data.*.amount' => 'required|integer|min:1',
        ]);

        $academicYearId = $request->academic_year_id;
        $billTypeId = $request->bill_type_id;
        $billType = BillType::where('id', $billTypeId)->firstOrFail();

        if ($billType->academic_year_id && $billType->academic_year_id !== $academicYearId) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis tagihan yang dipilih tidak sesuai dengan Tahun Ajaran yang dipilih.',
            ], 422);
        }

        $importedData = $request->data;
        $adminId = Auth::id();

        $successCount = 0;
        $failedCount = 0;

        DB::beginTransaction();
        try {
            $paymentMethod = PaymentMethod::where('type', PaymentMethod::TYPE_CASH)->first();
            $paymentMethodId = $paymentMethod ? $paymentMethod->id : PaymentMethod::CASH_PAYMENT;

            foreach ($importedData as $item) {
                $studentId = $item['student_id'];
                $amount = intval($item['amount']);

                $student = Student::with('classroom')->findOrFail($studentId);

                $bill = Bill::where('student_id', $studentId)
                    ->where('bill_type_id', $billTypeId)
                    ->where('academic_year_id', $academicYearId)
                    ->first();

                if (!$bill) {
                    $bill = Bill::create([
                        'bill_type_id' => $billTypeId,
                        'student_id' => $studentId,
                        'classroom_id' => $student->classroom_id ?? '',
                        'academic_year_id' => $academicYearId,
                        'month' => intval(date('m')),
                        'year' => intval(date('Y')),
                        'amount' => $amount,
                        'paid_amount' => 0,
                        'status' => Bill::STATUS_UNPAID,
                    ]);
                }

                if ($bill->status === Bill::STATUS_PAID) {
                    $failedCount++;
                    continue;
                }

                $transactionCount = Transaction::whereDate('created_at', now())->count();
                $paymentCode = 'CHT-IMP-' . now()->format('Ymd') . str_pad($transactionCount + 1, 4, '0', STR_PAD_LEFT);

                $transaction = Transaction::create([
                    'pay_amount' => $amount,
                    'payment_code' => $paymentCode,
                    'student_id' => $studentId,
                    'expiry_time' => Carbon::now()->addMinutes(1440),
                    'status' => Transaction::STATUS_PAID,
                    'paid_at' => now(),
                    'type' => Transaction::TYPE_BILL,
                    'admin_id' => $adminId,
                    'payment_method_id' => $paymentMethodId,
                ]);

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'bill_id' => $bill->id,
                    'amount' => $amount,
                ]);

                $bill->paid_amount = min($bill->amount, $bill->paid_amount + $amount);
                if ($bill->paid_amount >= $bill->amount) {
                    $bill->status = Bill::STATUS_PAID;
                }
                $bill->save();

                try {
                    if ($student->user && $student->user->phone) {
                        TransactionService::dispatchNotifications($transaction);
                    }
                } catch (\Exception $e) {
                    Log::warning("Gagal mengirim WA notifikasi import: " . $e->getMessage());
                }

                $successCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengimport {$successCount} data pembayaran tagihan.",
                'success_count' => $successCount,
                'failed_count' => $failedCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal melakukan import: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Gagal memproses import: " . $e->getMessage(),
            ], 500);
        }
    }
}
