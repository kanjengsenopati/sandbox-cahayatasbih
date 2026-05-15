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
            ->latest();
            
        if ($request->filter == 'today') {
            $query->whereDate('created_at', now());
        } elseif ($request->filter == 'week') {
            $query->where('created_at', '>=', now()->startOfWeek());
        } elseif ($request->filter == 'month') {
            $query->where('created_at', '>=', now()->startOfMonth());
        } elseif ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }
        
        $paginated = $query->paginate(10);

        // Lazy cleanup for ghost pending transactions
        $histories = $paginated->getCollection();
        foreach ($histories as $key => $history) {
            if ($history->status === SavingHistory::STATUS_PENDING) {
                $transactionDetail = $history->transaction_details()->with('transaction.activeProof')->first();
                if ($transactionDetail && $transactionDetail->transaction) {
                    $tx = $transactionDetail->transaction;
                    if ($tx->status === \App\Models\Transaction::STATUS_CANCELLED) {
                        $history->delete();
                        $histories->forget($key);
                    } elseif ($tx->activeProof && $tx->activeProof->status === \App\Models\TransactionProof::STATUS_REJECTED) {
                        $history->update(['status' => SavingHistory::STATUS_FAILED]);
                    }
                }
            }
        }
        $paginated->setCollection($histories->values());

        return response()->json($paginated);
    }
}
