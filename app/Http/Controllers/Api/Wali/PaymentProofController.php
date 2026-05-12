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
                    'proof_path' => $path,
                    'status' => 'PENDING',
                ]
            );

            return response()->json([
                'message' => 'Payment proof uploaded successfully',
                'proof' => $proof
            ]);
        }

        return response()->json(['message' => 'Failed to upload proof'], 400);
    }
}
