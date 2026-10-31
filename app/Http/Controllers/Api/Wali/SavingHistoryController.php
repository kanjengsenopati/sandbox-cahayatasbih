<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\SavingHistory;
use Illuminate\Http\Request;

class SavingHistoryController extends BaseWaliApiController
{
    public function index(Request $request)
    {
        $student = $this->resolveActiveStudent();
        if (!$student) return response()->json(['data' => []]);
        
        $query = SavingHistory::where('student_id', $student->id)
            ->whereNotIn('status', [SavingHistory::STATUS_FAILED])
            ->latest();
            
        if ($request->filter == 'today') {
            $query->where('created_at', '>=', now()->startOfDay());
        } elseif ($request->filter == 'week') {
            $query->where('created_at', '>=', now()->subDays(7)->startOfDay());
        } elseif ($request->filter == 'month') {
            $query->where('created_at', '>=', now()->subDays(30)->startOfDay());
        } elseif ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }
        
        $paginated = $query->paginate(10);

        // Lazy cleanup for ghost pending transactions
        $histories = $paginated->getCollection();
        foreach ($histories as $key => $history) {
            if ($history->status === SavingHistory::STATUS_PENDING || $history->status === SavingHistory::STATUS_FAILED) {
                $transactionDetail = $history->transaction_details()->first();
                
                // If orphaned (no link to transaction detail), it's a ghost record
                if (!$transactionDetail) {
                    $history->delete();
                    $histories->forget($key);
                    continue;
                }

                $tx = \App\Models\Transaction::withTrashed()->with('activeProof')->find($transactionDetail->transaction_id);
                
                // If transaction is missing, cancelled, or deleted by admin
                if (!$tx || $tx->status === \App\Models\Transaction::STATUS_CANCELLED || $tx->trashed()) {
                    $history->delete();
                    $histories->forget($key);
                } else {
                    // Sync real status from linked transaction
                    if ($tx->status === \App\Models\Transaction::STATUS_PENDING_CONFIRMATION) {
                        $history->status = \App\Models\Transaction::STATUS_PENDING_CONFIRMATION;
                    } elseif (in_array($tx->status, [\App\Models\Transaction::STATUS_PENDING_PAYMENT, \App\Models\Transaction::STATUS_PENDING])) {
                        $history->status = \App\Models\Transaction::STATUS_PENDING_PAYMENT;
                    } elseif ($tx->status === \App\Models\Transaction::STATUS_PAID) {
                        $history->status = 'SUCCESS';
                    } elseif ($tx->status === \App\Models\Transaction::STATUS_REJECTED || ($tx->activeProof && $tx->activeProof->status === \App\Models\TransactionProof::STATUS_REJECTED)) {
                        $history->status = 'REJECTED';
                        $history->rejection_note = $tx->activeProof?->note ?? 'Bukti pembayaran ditolak oleh Petugas Tabungan';
                    }

                    // Attach note if it exists
                    if ($tx->activeProof && $tx->activeProof->note) {
                        $history->note = $tx->activeProof->note;
                    }
                }
            }
        }
        $paginated->setCollection($histories->values());

        return response()->json($paginated);
    }
}
