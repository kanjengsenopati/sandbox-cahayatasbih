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
        $student = $this->resolveActiveStudent();
        if (!$student) return response()->json(['message' => 'Student not found'], 404);

        // Merge student_id into request for validation and TransactionService
        if (!$request->has('student_id')) {
            $request->merge(['student_id' => $student->id]);
        }

        try {
            $request->validate([
                'bill_ids' => 'required|array',
                'bill_ids.*' => 'exists:bills,id',
                'payment_method_id' => 'required|exists:payment_methods,id',
                'student_id' => 'required|exists:students,id'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Checkout Validation Failed', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            throw $e;
        }

        $paymentMethod = \App\Models\PaymentMethod::findOrFail($request->payment_method_id);
        
        $transaction = \App\Services\TransactionService::createTransaction($request, $paymentMethod->type, Transaction::TYPE_BILL);
        
        if ($transaction instanceof \Illuminate\Http\JsonResponse) {
            return $transaction;
        }

        // Transaction details are already created inside TransactionService::createTransaction for TYPE_BILL
        // if bill_ids is present in the request. Let's verify.
        // Yes, line 246 in TransactionService.php: $pay_amount = $request->bill_ids != null ? self::getTotalPayAmount($request->bill_ids) : $request->amount;
        // And line 259: $transaction = Transaction::create(array_merge($transactionData, $request->validated()));
        // Wait, TransactionService DOES NOT create TransactionDetails automatically in createTransaction.
        // It's usually done in the controller or in payWithBalance/payWithCash.
        
        // Actually, looking at TransactionService::createTransaction again...
        // It doesn't create TransactionDetail. I should do it here.

        foreach ($request->bill_ids as $billId) {
            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'bill_id' => $billId,
            ]);
        }

        return response()->json([
            'message' => 'Checkout successful',
            'transaction' => $transaction,
            'payment_url' => $paymentMethod->type == \App\Models\PaymentMethod::TYPE_XENDIT ? $transaction->payment_link : null
        ]);
    }
}
