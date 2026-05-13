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

        $transaction = Transaction::findOrFail($id);
        
        if ($request->hasFile('proof')) {
            $path = $request->file('proof')->store('proofs', 'public');
            
            $proof = TransactionProof::updateOrCreate(
                ['transaction_id' => $id],
                [
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

            return response()->json([
                'message' => 'Payment proof uploaded successfully',
                'proof' => $proof
            ]);
        }

        return response()->json(['message' => 'Failed to upload proof'], 400);
    }
}
