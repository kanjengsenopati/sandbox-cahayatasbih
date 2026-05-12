<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\Bill;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends BaseWaliApiController
{
    public function store(Request $request)
    {
        $request->validate([
            'bill_ids' => 'required|array',
            'bill_ids.*' => 'exists:bills,id'
        ]);

        $bills = Bill::whereIn('id', $request->bill_ids)->get();
        $totalAmount = $bills->sum('amount');
        
        $uniqueDigits = rand(100, 999);
        $payAmount = $totalAmount + $uniqueDigits;
        
        $student = $this->resolveActiveStudent();
        if (!$student) return response()->json(['message' => 'Student not found'], 404);

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

        return response()->json([
            'message' => 'Checkout successful',
            'transaction' => $transaction
        ]);
    }
}
