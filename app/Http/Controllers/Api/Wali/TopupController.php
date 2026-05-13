<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TopupController extends BaseWaliApiController
{
    public function store(Request $request)
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
            return $transaction;
        }

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

        return response()->json([
            'message' => 'Top Up request created successfully',
            'transaction' => $transaction,
            'payment_url' => $paymentMethod->type == PaymentMethod::TYPE_XENDIT ? $transaction->payment_link : null
        ]);
    }
}
