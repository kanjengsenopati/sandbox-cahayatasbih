<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\Transaction;
use App\Models\TransactionProof;
use App\Models\BillTypeBank;
use App\Models\TopupBank;
use Illuminate\Http\Request;

class PaymentController extends BaseWaliApiController
{
    public function show($id)
    {
        $transaction = Transaction::with(['transactionDetails.bill.billType', 'student.classroom.school'])->findOrFail($id);
        
        $banks = collect();
        if ($transaction->type == Transaction::TYPE_BILL) {
            $billTypeId = $transaction->transactionDetails->first()?->bill?->bill_type_id;
            if ($billTypeId) {
                $banks = BillTypeBank::with('bank')->where('bill_type_id', $billTypeId)->get()->pluck('bank');
            }
        } elseif ($transaction->type == Transaction::TYPE_SALDO) {
            $banks = TopupBank::with('bank')
                ->where('type', TopupBank::TYPE_SALDO)
                ->where('school_id', $transaction->student?->classroom?->school_id ?? null)
                ->get()
                ->pluck('bank');
        } elseif ($transaction->type == Transaction::TYPE_SAVING) {
            $banks = TopupBank::with('bank')
                ->where('type', TopupBank::TYPE_SAVING)
                ->where('school_id', $transaction->student?->classroom?->school_id ?? null)
                ->get()
                ->pluck('bank');
        }

        $proof = TransactionProof::where('transaction_id', $id)->first();

        return response()->json([
            'transaction' => $transaction,
            'banks' => $banks,
            'proof' => $proof
        ]);
    }
}
