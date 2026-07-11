<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\Transaction;
use Illuminate\Http\Request;

class BillTransactionController extends BaseWaliApiController
{
    public function index(Request $request)
    {
        $student = $this->resolveActiveStudent();
        if (!$student) return response()->json(['data' => []]);

        $query = Transaction::with(['paymentMethod', 'transactionDetails.bill.billType'])
            ->where('student_id', $student->id)
            ->where('type', Transaction::TYPE_BILL)
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

        $items = $paginated->getCollection()->map(function($item) {
            $billNames = $item->transactionDetails
                ->map(fn($d) => $d->bill->billType->name ?? 'Tagihan')
                ->unique()
                ->join(', ');

            return [
                'id' => $item->id,
                'student_id' => $item->student_id,
                'pay_amount' => $item->pay_amount,
                'payment_code' => $item->payment_code,
                'status' => $item->status,
                'type' => $item->type,
                'created_at' => $item->paid_at ?? $item->created_at,
                'bill_names' => $billNames ?: 'Pembayaran Tagihan',
                'payment_method_name' => $item->paymentMethod->name ?? 'Metode Lain',
                'payment_method_type' => $item->paymentMethod->type ?? 'OTHER'
            ];
        });

        $paginated->setCollection($items);

        return response()->json($paginated);
    }
}
