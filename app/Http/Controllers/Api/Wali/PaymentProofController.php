<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\Transaction;
use App\Models\TransactionProof;
use Illuminate\Http\Request;

class PaymentProofController extends BaseWaliApiController
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'proof' => 'required|image|max:2048'
        ]);

        try {
            $transaction = Transaction::findOrFail($id);
            
            if ($request->hasFile('proof')) {
                $path = $request->file('proof')->store('proofs', 'public');
                
                // Get bank_id from request or default to transaction's first bank
                $bankId = $request->bank_id;
                if (!$bankId) {
                    $bank = $transaction->banks->first();
                    $bankId = $bank ? $bank->id : null;
                }

                // Fallback to first available bank if still null (to prevent DB crash)
                if (!$bankId) {
                    $firstBank = \App\Models\Bank::first();
                    $bankId = $firstBank ? $firstBank->id : null;
                }

                $proof = TransactionProof::updateOrCreate(
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

                // Sync related history back to PENDING
                if ($transaction->type == Transaction::TYPE_SALDO) {
                    $transaction->transactionDetails->each(function ($detail) {
                        $detail->saldoHistory?->update(['status' => \App\Models\SaldoHistory::STATUS_PENDING]);
                    });
                } elseif ($transaction->type == Transaction::TYPE_SAVING) {
                    $transaction->transactionDetails->each(function ($detail) {
                        $detail->savingHistory?->update(['status' => \App\Models\SavingHistory::STATUS_PENDING]);
                    });
                }

                return response()->json([
                    'message' => 'Payment proof uploaded successfully',
                    'proof' => $proof
                ]);
            }

            return response()->json(['message' => 'No proof file found in request'], 400);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Upload Proof Error: ' . $e->getMessage(), [
                'transaction_id' => $id,
                'exception' => $e
            ]);
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
