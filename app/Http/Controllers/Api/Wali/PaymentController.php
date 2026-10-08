<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\Transaction;
use App\Models\TransactionProof;
use App\Models\TransactionDetail;
use App\Models\BillTypeBank;
use App\Models\TopupBank;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends BaseWaliApiController
{
    public function show($id)
    {
        $transaction = Transaction::with(['transactionDetails.bill.billType', 'student.classroom.school', 'activeProof'])->findOrFail($id);
        
        // Auto-cancel if bill payment has expired (> 2 hours without proof or passed expiry_time)
        if ($transaction->type === Transaction::TYPE_BILL 
            && in_array($transaction->status, [Transaction::STATUS_PENDING, Transaction::STATUS_PENDING_PAYMENT]) 
            && !$transaction->activeProof) {
            
            $isOverTwoHours = $transaction->created_at && Carbon::parse($transaction->created_at)->lte(Carbon::now()->subHours(2));
            $isPastExpiry = $transaction->expiry_time && Carbon::parse($transaction->expiry_time)->lte(Carbon::now());
            
            if ($isOverTwoHours || $isPastExpiry) {
                $transaction->update(['status' => Transaction::STATUS_CANCELLED]);
            }
        }

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

        $proof = $transaction->activeProof ?: TransactionProof::where('transaction_id', $id)->latest()->first();

        return response()->json([
            'transaction' => $transaction,
            'banks' => $banks,
            'proof' => $proof
        ]);
    }

    public function cancelTransaction($id)
    {
        $student = $this->resolveActiveStudent();
        if (!$student) {
            return response()->json(['message' => 'Data santri tidak ditemukan.'], 404);
        }

        $transaction = Transaction::where('student_id', $student->id)->findOrFail($id);

        if (!in_array($transaction->status, [
            Transaction::STATUS_PENDING,
            Transaction::STATUS_PENDING_PAYMENT,
            Transaction::STATUS_REJECTED
        ])) {
            return response()->json([
                'message' => 'Hanya transaksi yang belum dibayar atau ditolak yang dapat dibatalkan.'
            ], 400);
        }

        // Deactivate active proof if any
        if ($transaction->activeProof) {
            $transaction->activeProof->update(['is_active' => false]);
            $transaction->activeProof->delete();
        }

        // Update status to CANCELLED to rollback locked bills
        $transaction->update([
            'status' => Transaction::STATUS_CANCELLED
        ]);

        // Clean up saldo or saving history if needed
        if ($transaction->type === Transaction::TYPE_SALDO || $transaction->type === Transaction::TYPE_SAVING) {
            $details = TransactionDetail::where('transaction_id', $transaction->id)->get();
            foreach ($details as $detail) {
                if ($detail->saldo_history_id) {
                    \App\Models\SaldoHistory::where('id', $detail->saldo_history_id)->delete();
                }
                if ($detail->saving_history_id) {
                    \App\Models\SavingHistory::where('id', $detail->saving_history_id)->delete();
                }
            }
        }

        return response()->json([
            'message' => 'Transaksi berhasil dibatalkan. Tagihan dapat dibayarkan kembali.',
            'transaction' => $transaction
        ]);
    }
}
