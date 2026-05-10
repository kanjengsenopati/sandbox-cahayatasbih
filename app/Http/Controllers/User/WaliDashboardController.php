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
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        
        // Mocking a Request object for TransactionService if needed, or ensuring request has the right keys
        $request->merge(['pay_amount' => $request->amount]);
        
        $transaction = TransactionService::createTransaction($request, $paymentMethod->type, Transaction::TYPE_SALDO);
        
        // Check if transaction is an instance of Transaction or a Response
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

        $bills = Bill::with('billType.billItem', 'billType.academicYear')
            ->where('student_id', $activeStudent->id)
            ->where('status', 'UNPAID')
            ->orderBy('due_date', 'asc')
            ->get();

        return view('users.dashboard.bills', compact('activeStudent', 'bills'));
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
