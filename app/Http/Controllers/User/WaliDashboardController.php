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
use Carbon\Carbon;
use Illuminate\Support\Str;

class WaliDashboardController extends Controller
{
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
        $informations = Information::with(
            'informationCategory'
        )->where('is_active', true)->latest()->take(5)->get();
        
        $user = Auth::guard('wali')->user();
        $students = Student::where('user_id', $user->id)->orderBy('name', 'asc')->get();
        
        // Handle active student for PWA view
        $activeStudentId = session('active_student_id');
        $activeStudent = $students->where('id', $activeStudentId)->first() ?: $students->first();
        
        return view('users.dashboard.app', compact('informations', 'students', 'activeStudent'));
    }

    public function topup()
    {
        $activeStudentId = session('active_student_id');
        $user = Auth::guard('wali')->user();
        $activeStudent = Student::where('user_id', $user->id)
            ->where('id', $activeStudentId ?: Student::where('user_id', $user->id)->first()?->id)
            ->first();

        if (!$activeStudent) return redirect()->route('wali.app');

        $paymentMethods = PaymentMethod::where('is_active', true)
            ->whereIn('type', [PaymentMethod::TYPE_XENDIT, PaymentMethod::TYPE_TRANSFER])
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
        
        if ($transaction instanceof \Illuminate\Http\JsonResponse) {
            return redirect()->back()->with('error', $transaction->getData()->message);
        }

        if ($paymentMethod->type == PaymentMethod::TYPE_XENDIT) {
            return redirect($transaction->payment_link);
        }

        return redirect()->route('wali.history')->with('success', 'Permintaan Top Up berhasil dibuat. Silakan selesaikan pembayaran.');
    }

    public function history()
    {
        $activeStudentId = session('active_student_id');
        $user = Auth::guard('wali')->user();
        $activeStudent = Student::where('user_id', $user->id)
            ->where('id', $activeStudentId ?: Student::where('user_id', $user->id)->first()?->id)
            ->first();

        if (!$activeStudent) return redirect()->route('wali.app');

        $saldoHistories = SaldoHistory::where('student_id', $activeStudent->id)->latest()->take(50)->get();
        $savingHistories = SavingHistory::where('student_id', $activeStudent->id)->latest()->take(50)->get();

        return view('users.dashboard.history', compact('activeStudent', 'saldoHistories', 'savingHistories'));
    }

    public function bills()
    {
        $activeStudentId = session('active_student_id');
        $user = Auth::guard('wali')->user();
        $activeStudent = Student::where('user_id', $user->id)
            ->where('id', $activeStudentId ?: Student::where('user_id', $user->id)->first()?->id)
            ->first();

        if (!$activeStudent) return redirect()->route('wali.app');

        $groupedBills = Bill::with(['billType.billItem', 'billType.academicYear'])
            ->where('student_id', $activeStudent->id)
            ->get()
            ->groupBy('bill_type_id')
            ->map(function ($items) {
                $first = $items->first();
                return [
                    'bill_type_id' => $first->bill_type_id,
                    'bill_type_name' => $first->billType->name ?? 'Tagihan',
                    'academic_year' => $first->billType->academicYear->name ?? '-',
                    'total' => $items->sum('amount'),
                    'paid' => $items->where('status', 'PAID')->sum('amount'),
                    'unpaid' => $items->where('status', 'UNPAID')->sum('amount'),
                    'items_count' => $items->count(),
                    'unpaid_count' => $items->where('status', 'UNPAID')->count(),
                ];
            });

        return view('users.dashboard.bills', compact('activeStudent', 'groupedBills'));
    }

    public function billDetail($id)
    {
        $activeStudentId = session('active_student_id');
        $user = Auth::guard('wali')->user();
        $activeStudent = Student::where('user_id', $user->id)
            ->where('id', $activeStudentId ?: Student::where('user_id', $user->id)->first()?->id)
            ->first();

        $billType = BillType::with(['billItem', 'academicYear'])->findOrFail($id);
        
        $bills = Bill::where('student_id', $activeStudent->id)
            ->where('bill_type_id', $id)
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $summary = [
            'total' => $bills->sum('amount'),
            'paid' => $bills->where('status', 'PAID')->sum('amount'),
            'unpaid' => $bills->where('status', 'UNPAID')->sum('amount'),
        ];

        return view('users.dashboard.bill-detail', compact('activeStudent', 'billType', 'bills', 'summary'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'bill_ids' => 'required|array',
            'bill_ids.*' => 'exists:bills,id'
        ]);

        $bills = Bill::whereIn('id', $request->bill_ids)->get();
        $totalAmount = $bills->sum('amount');
        
        $uniqueDigits = rand(100, 999);
        $payAmount = $totalAmount + $uniqueDigits;
        
        $activeStudentId = session('active_student_id');
        $student = Student::find($activeStudentId);

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
        
        $billTypeId = $transaction->transactionDetails->first()?->bill?->bill_type_id;
        $banks = [];
        if ($billTypeId) {
            $banks = \App\Models\BillTypeBank::with('bank')->where('bill_type_id', $billTypeId)->get();
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
            
            TransactionProof::updateOrCreate(
                ['transaction_id' => $id],
                [
                    'proof_path' => $path,
                    'status' => 'PENDING',
                ]
            );

            return redirect()->back()->with('success', 'Bukti pembayaran berhasil diunggah. Tunggu konfirmasi admin.');
        }

        return redirect()->back()->with('error', 'Gagal mengunggah bukti.');
    }

    public function limit()
    {
        $activeStudentId = session('active_student_id');
        $user = Auth::guard('wali')->user();
        $activeStudent = Student::where('user_id', $user->id)
            ->where('id', $activeStudentId ?: Student::where('user_id', $user->id)->first()?->id)
            ->first();

        if (!$activeStudent) return redirect()->route('wali.app');

        return view('users.dashboard.limit', compact('activeStudent'));
    }

    public function updateLimit(Request $request)
    {
        $request->validate(['daily_limit' => 'required|numeric|min:0']);
        $activeStudentId = session('active_student_id');
        $user = Auth::guard('wali')->user();
        $student = Student::where('user_id', $user->id)->where('id', $activeStudentId)->firstOrFail();
        
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
}
