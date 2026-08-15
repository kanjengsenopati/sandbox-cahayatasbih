<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Information;
use App\Models\Student;
use App\Models\SaldoHistory;
use App\Models\SavingHistory;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use App\Models\BillType;
use App\Models\TransactionDetail;
use App\Models\TransactionProof;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class WaliDashboardController extends Controller
{
    protected function resolveActiveStudent()
    {
        $user = Auth::guard('wali')->user();
        if (!$user) return null;
        
        $activeStudentId = session('active_student_id');
        
        return Student::where('user_id', $user->id)
            ->when($activeStudentId, function ($query) use ($activeStudentId) {
                return $query->where('id', $activeStudentId);
            })
            ->first() ?: Student::where('user_id', $user->id)->first();
    }

    public function index()
    {
        $informations = Information::with(
            'informationCategory'
        )->where('is_active', true)->latest()->take(5)->get();
        $students = Student::where('user_id', Auth::guard('wali')->user()->id)->orderBy('name', 'asc')->get();
        return view('users.dashboard.index', compact('informations', 'students'));
    }

    public function app()
    {
        return view('users.dashboard.pwa-app-fix');
    }

    public function topup()
    {
        $activeStudent = $this->resolveActiveStudent();

        if (!$activeStudent) return redirect()->route('wali.app');

        $paymentMethods = PaymentMethod::where('is_active', true)
            ->where('type', PaymentMethod::TYPE_TRANSFER)
            ->get();

        return view('users.dashboard.topup', compact('activeStudent', 'paymentMethods'));
    }

    public function storeTopup(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'student_id' => 'required|exists:students,id'
        ]);

        $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);
        
        $request->merge(['pay_amount' => $request->amount]);
        
        $transaction = TransactionService::createTransaction($request, $paymentMethod->type, Transaction::TYPE_SALDO);
        
        // Create SaldoHistory with PENDING status
        $saldoHistory = \App\Models\SaldoHistory::create([
            'student_id' => $request->student_id,
            'amount' => $request->amount,
            'type' => \App\Models\SaldoHistory::TYPE_IN,
            'description' => 'Top Up Saldo Saku Sebesar Rp.' . number_format($request->amount, 0, ',', '.'),
            'status' => \App\Models\SaldoHistory::STATUS_PENDING,
            'usage' => \App\Models\SaldoHistory::USAGE_TOPUP
        ]);

        // Link transaction to saldo history
        \App\Models\TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'saldo_history_id' => $saldoHistory->id
        ]);

        return redirect()->route('wali.history')->with('success', 'Permintaan Top Up berhasil dibuat. Silakan selesaikan pembayaran.');
    }

    public function history(Request $request)
    {
        $activeStudent = $this->resolveActiveStudent();

        if (!$activeStudent) return redirect()->route('wali.app');

        $filter = $request->get('filter', 'this_month'); // today, this_week, this_month, custom
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $saldoQuery = SaldoHistory::with(['pointOfSaleTransaction.pointOfSaleTransactionDetails.item', 'pointOfSaleTransaction.admins'])
            ->where('student_id', $activeStudent->id)
            ->where('status', 'SUCCESS');

        if ($filter == 'today') {
            $saldoQuery->whereDate('created_at', Carbon::today());
        } elseif ($filter == 'this_week') {
            $saldoQuery->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($filter == 'this_month') {
            $saldoQuery->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
        } elseif ($filter == 'custom' && $startDate && $endDate) {
            $saldoQuery->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()]);
        }

        $saldoIn = (clone $saldoQuery)->where('type', 'IN')->sum('amount');
        $saldoOut = (clone $saldoQuery)->where('type', 'OUT')->sum('amount');
        
        $saldoHistories = $saldoQuery->latest()->paginate(5)->appends($request->all());
        $totalSaldo = $activeStudent->saldo; // Assuming saldo is a column on Student, or we can just pass the raw object

        $savingHistories = SavingHistory::where('student_id', $activeStudent->id)->latest()->take(50)->get();
        $billTransactions = Transaction::where('student_id', $activeStudent->id)
            ->where('type', Transaction::TYPE_BILL)
            ->latest()
            ->take(50)
            ->get();
            
        $posTransactions = \App\Models\PointOfSaleTransaction::with(['pointOfSaleTransactionDetails.item', 'admins'])
            ->where('student_id', $activeStudent->id)
            ->latest()
            ->take(50)
            ->get();

        return view('users.dashboard.history', compact('activeStudent', 'saldoHistories', 'savingHistories', 'billTransactions', 'posTransactions', 'saldoIn', 'saldoOut', 'totalSaldo', 'filter', 'startDate', 'endDate'));
    }

    public function bills(Request $request)
    {
        $activeStudent = $this->resolveActiveStudent();

        if (!$activeStudent) return redirect()->route('wali.app');

        $studentSchoolName = $activeStudent->classroom->school->name ?? '';

        $academicYearReq = $request->get('academic_year_id');
        if ($academicYearReq === 'all') {
            $academicYearId = null;
        } elseif (!empty($academicYearReq)) {
            $academicYearId = $academicYearReq;
        } else {
            $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
            $academicYearId = $activeYear ? $activeYear->id : null;
        }

        $academicYears = \App\Models\AcademicYear::where(function($query) {
            $query->where('is_active', true)
                  ->orWhereHas('billTypes', function ($q) {
                      $q->where('is_visible', true);
                  });
        })->orderBy('start_year', 'desc')->get();

        $groupedBills = Bill::with(['billType.billItem', 'billType.academicYear'])
            ->where('student_id', $activeStudent->id)
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->get()
            ->filter(function ($bill) use ($activeStudent, $studentSchoolName) {
                if (($bill->billType->is_visible ?? true) === false) {
                    return false;
                }

                $billAY = $bill->billType->academicYear ?? $bill->academicYear;

                // ATURAN KHUSUS SISWA KELAS 12 MA:
                if (TransactionService::isClass12MA($activeStudent)) {
                    // 1. Hide tagihan jika Tahun Ajaran < 2026/2027
                    if (TransactionService::isBillBeforeAcademicYear2026($billAY)) {
                        return false;
                    }
                    // 2. HANYA munculkan Syahriah, Biaya Aplikasi, dan LKS
                    $btName = $bill->billType->name ?? '';
                    if (!TransactionService::isAllowedBillTypeForClass12MA($btName)) {
                        return false;
                    }
                }

                // Pre-entry year check
                $entryYear = $activeStudent->getEntryYear();
                $billAYName = $bill->billType->academicYear->name ?? '';
                if ($entryYear && $billAYName) {
                    $ayStartYear = (int) explode('/', $billAYName)[0];
                    if ($ayStartYear < $entryYear) return false;
                }

                // UPT matching check
                $billTypeName = $bill->billType->name ?? '';
                $billItemName = $bill->billType->billItem->name ?? '';
                return TransactionService::isBillTypeMatchingStudentSchoolUnit($billTypeName, $studentSchoolName)
                    || TransactionService::isBillTypeMatchingStudentSchoolUnit($billItemName, $studentSchoolName);
            })
            ->groupBy('bill_type_id')
            ->map(function ($items) {
                $first = $items->first();
                return [
                    'bill_type_id' => $first->bill_type_id,
                    'bill_type_name' => $first->billType->name ?? 'Tagihan',
                    'academic_year' => $first->billType->academicYear->name ?? '-',
                    'total' => $items->sum('amount'),
                    'paid' => $items->sum('paid_amount'),
                    'unpaid' => $items->sum('remaining_amount'),
                    'items_count' => $items->count(),
                    'unpaid_count' => $items->where('status', 'UNPAID')->count(),
                ];
            });

        $unpaidBills = $groupedBills->filter(fn($g) => $g['unpaid'] > 0)->values();
        $paidBills = $groupedBills->filter(fn($g) => $g['unpaid'] == 0)->values();

        return view('users.dashboard.bills', compact('activeStudent', 'unpaidBills', 'paidBills', 'academicYears', 'academicYearId'));
    }

    public function billDetail($id)
    {
        $activeStudent = $this->resolveActiveStudent();

        $billType = BillType::with(['billItem', 'academicYear'])->findOrFail($id);
        
        $bills = Bill::where('student_id', $activeStudent->id)
            ->where('bill_type_id', $id)
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $summary = [
            'total' => $bills->sum('amount'),
            'paid' => $bills->sum('paid_amount'),
            'unpaid' => $bills->sum('remaining_amount'),
        ];

        return view('users.dashboard.bill-detail', compact('activeStudent', 'billType', 'bills', 'summary'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'bill_ids' => 'required|array',
            'bill_ids.*' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (str_starts_with($value, 'generated_') || str_starts_with($value, 'auto_')) {
                        return;
                    }
                    if (!\App\Models\Bill::where('id', $value)->exists()) {
                        $fail("Tagihan dengan ID {$value} tidak ditemukan.");
                    }
                }
            ]
        ]);

        $student = $this->resolveActiveStudent();
        if (!$student) {
            return redirect()->route('wali.app')->with('error', 'Santri tidak ditemukan');
        }

        $realBillIds = [];
        foreach ($request->bill_ids as $bId) {
            $realBillIds[] = TransactionService::ensureBillRecord($student->id, $bId);
        }

        $bills = Bill::whereIn('id', $realBillIds)->get();
        $totalAmount = $bills->sum('amount');
        
        $uniqueDigits = TransactionService::generateUniqueDigitsForAmount($totalAmount);
        $payAmount = $totalAmount + $uniqueDigits;
        
        $student = $this->resolveActiveStudent();

        if (!$student) {
            return redirect()->route('wali.app')->with('error', 'Santri tidak ditemukan');
        }

        $paymentCode = 'INV-' . strtoupper(Str::random(8));

        $transaction = Transaction::create([
            'student_id' => $student->id,
            'payment_code' => $paymentCode,
            'pay_amount' => $payAmount,
            'unique_payment' => $uniqueDigits,
            'status' => 'PENDING_PAYMENT',
            'type' => 'BILL',
            'expiry_time' => Carbon::now()->addDays(1),
        ]);

        foreach ($bills as $bill) {
            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'bill_id' => $bill->id,
            ]);
        }

        return redirect()->route('wali.payment', $transaction->id);
    }

    public function payment($id)
    {
        $transaction = Transaction::with(['transactionDetails.bill.billType', 'student'])->findOrFail($id);
        
        $banks = [];
        if ($transaction->type == \App\Models\Transaction::TYPE_BILL) {
            $billTypeId = $transaction->transactionDetails->first()?->bill?->bill_type_id;
            if ($billTypeId) {
                $banks = \App\Models\BillTypeBank::with('bank')->where('bill_type_id', $billTypeId)->get();
            }
        } elseif ($transaction->type == \App\Models\Transaction::TYPE_SALDO) {
            $banks = \App\Models\TopupBank::with('bank')->where('type', \App\Models\TopupBank::TYPE_SALDO)->get();
        } elseif ($transaction->type == \App\Models\Transaction::TYPE_SAVING) {
            $banks = \App\Models\TopupBank::with('bank')->where('type', \App\Models\TopupBank::TYPE_SAVING)->get();
        }

        $proof = TransactionProof::where('transaction_id', $id)->first();

        return view('users.dashboard.payment', compact('transaction', 'banks', 'proof'));
    }

    public function uploadProof(Request $request, $id)
    {
        $request->validate([
            'proof' => 'required|image|max:2048'
        ]);

        $transaction = Transaction::findOrFail($id);
        
        if ($request->hasFile('proof')) {
            $path = $request->file('proof')->store('proofs', 'public');
            
            $bankId = $request->bank_id;
            if (!$bankId) {
                $bank = $transaction->banks->first();
                $bankId = $bank ? $bank->id : (\App\Models\Bank::first()?->id);
            }

            TransactionProof::updateOrCreate(
                ['transaction_id' => $id],
                [
                    'bank_id' => $bankId,
                    'student_id' => $transaction->student_id,
                    'proof_image' => $path,
                    'status' => TransactionProof::STATUS_WAITING_CONFIRMATION,
                    'is_active' => true,
                ]
            );

            // Update transaction status to trigger admin verification
            $transaction->update([
                'status' => Transaction::STATUS_PENDING_CONFIRMATION
            ]);

            return redirect()->back()->with('success', 'Bukti pembayaran berhasil diunggah. Tunggu konfirmasi admin.');
        }

        return redirect()->back()->with('error', 'Gagal mengunggah bukti.');
    }

    public function limit()
    {
        $activeStudent = $this->resolveActiveStudent();

        if (!$activeStudent) return redirect()->route('wali.app');

        return view('users.dashboard.limit', compact('activeStudent'));
    }

    public function updateLimit(Request $request)
    {
        $request->validate(['daily_limit' => 'required|numeric|min:0']);
        $student = $this->resolveActiveStudent();
        
        $student->update(['daily_limit' => $request->daily_limit]);
        return redirect()->route('wali.app')->with('success', 'Limit jajan harian berhasil diperbarui');
    }

    public function profile()
    {
        $user = Auth::guard('wali')->user();
        $students = Student::where('user_id', $user->id)->get();
        return view('users.dashboard.profile', compact('user', 'students'));
    }

    public function switchStudent($id)
    {
        session(['active_student_id' => $id]);
        return redirect()->back();
    }

    public function tahfidz()
    {
        $activeStudent = $this->resolveActiveStudent();

        if (!$activeStudent) return redirect()->route('wali.app');

        $tahfidzHistory = \App\Models\Tahfidz::where('student_id', $activeStudent->id)
            ->latest('deposit_date')
            ->get();
            
        $totalPage = $tahfidzHistory->sum('number_of_pages');

        return view('users.dashboard.tahfidz', compact('activeStudent', 'tahfidzHistory', 'totalPage'));
    }

    public function grades()
    {
        $activeStudent = $this->resolveActiveStudent();

        if (!$activeStudent) return redirect()->route('wali.app');

        $grades = \App\Models\StudyGrade::with(['study', 'academicYear', 'semester'])
            ->where('student_id', $activeStudent->id)
            ->get()
            ->groupBy(['academic_year_id', 'semester_id']);

        return view('users.dashboard.grades', compact('activeStudent', 'grades'));
    }

    public function schedule()
    {
        $activeStudent = $this->resolveActiveStudent();

        if (!$activeStudent) return redirect()->route('wali.app');

        $schedules = \App\Models\Schedule::where(function ($q) use ($activeStudent) {
            $q->where('type', \App\Models\Schedule::TYPE_ALL)
                ->orWhere(function ($q) use ($activeStudent) {
                    $q->where('type', \App\Models\Schedule::TYPE_SCHOOL)
                        ->where('school_id', $activeStudent->school_id);
                });
        })
        ->orderBy('date', 'desc')
        ->get();

        return view('users.dashboard.schedule', compact('activeStudent', 'schedules'));
    }

    public function newsDetail($id)
    {
        $information = Information::with('informationCategory')->findOrFail($id);
        return view('users.dashboard.news-detail', compact('information'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::guard('wali')->user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|unique:users,phone,' . $user->id,
            'avatar' => 'nullable|image|max:1024',
        ]);

        $data = $request->only(['name', 'email', 'phone']);
        
        if ($request->hasFile('avatar')) {
            $data['avatar'] = 'storage/' . $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);
        return redirect()->back()->with('success', 'Profil berhasil diperbarui');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|min:8|confirmed',
        ]);

        Auth::guard('wali')->user()->update([
            'password' => Hash::make($request->new_password)
        ]);

        return redirect()->back()->with('success', 'Password berhasil diperbarui');
    }

    public function downloadReceipt($id)
    {
        $activeStudent = $this->resolveActiveStudent();
        
        $bill = Bill::where('student_id', $activeStudent->id)->findOrFail($id);
        
        if ($bill->status !== Bill::STATUS_PAID) {
            return back()->with('error', 'Kuitansi hanya tersedia untuk tagihan yang sudah lunas.');
        }

        $transaction = $bill->getPaidTransaction();
        if (!$transaction) {
             return back()->with('error', 'Data transaksi pembayaran tidak ditemukan.');
        }

        $data = Transaction::with([
            'student.classroom.school',
            'transactionDetails.bill.billType',
            'transactionDetails.saldoHistory',
            'transactionDetails.savingHistory',
            'paymentMethod',
            'admin'
        ])->findOrFail($transaction->id);

        $pdf = Pdf::loadView('admins.pdf.transaction-invoice', compact('data'));
        
        // Example: Kuitansi_SPP_Juli_2026_Ahmad.pdf
        $monthName = \Carbon\Carbon::createFromDate($bill->year, $bill->month, 1)->translatedFormat('F');
        $fileName = 'Kuitansi_' . str_replace(' ', '_', $bill->billType->name) . '_' . $monthName . '_' . $bill->year . '_' . str_replace(' ', '_', $activeStudent->name) . '.pdf';
        
        return $pdf->download($fileName);
    }
}
