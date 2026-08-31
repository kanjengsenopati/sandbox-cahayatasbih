<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\SaldoHistory;
use Illuminate\Http\Request;

class SaldoHistoryController extends BaseWaliApiController
{
    public function index(Request $request)
    {
        $student = $this->resolveActiveStudent();
        if (!$student || !$student->isPwaSaldoVisible()) return response()->json(['data' => []]);
        
        $query = SaldoHistory::where('student_id', $student->id)
            ->where(function ($q) {
                $q->whereNotIn('usage', [SaldoHistory::USAGE_POS, SaldoHistory::USAGE_BILL])
                  ->orWhereDoesntHave('pointOfSaleTransaction');
            })
            ->whereNotIn('status', [SaldoHistory::STATUS_FAILED])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');
            
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
            $transactionDetail = $history->transaction_details()->first();
            if ($transactionDetail) {
                $history->transaction_id = $transactionDetail->transaction_id;
            }

            if ($history->status === SaldoHistory::STATUS_PENDING || $history->status === SaldoHistory::STATUS_FAILED) {
                // If orphaned (no link to transaction detail), it's a ghost record
                if (!$transactionDetail) {
                    $history->delete();
                    $histories->forget($key);
                    continue;
                }

                $tx = \App\Models\Transaction::withTrashed()->with('activeProof')->find($transactionDetail->transaction_id);
                
                // If transaction is missing, cancelled, rejected, or deleted by admin
                if (!$tx || $tx->status === \App\Models\Transaction::STATUS_CANCELLED || $tx->status === \App\Models\Transaction::STATUS_REJECTED || $tx->trashed()) {
                    $history->delete();
                    $histories->forget($key);
                } else {
                    // Sync REJECTED status from proof
                    if ($history->status === SaldoHistory::STATUS_PENDING && $tx->activeProof && $tx->activeProof->status === \App\Models\TransactionProof::STATUS_REJECTED) {
                        $history->update(['status' => SaldoHistory::STATUS_FAILED]);
                        $history->status = SaldoHistory::STATUS_FAILED;
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
