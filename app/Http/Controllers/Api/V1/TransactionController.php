<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Services\TransactionService;

class TransactionController extends Controller
{
    public function callbackXendit(Request $request)
    {
        DB::beginTransaction();
        try {
            $transaction = Transaction::whereXenditId($request->id)->whereNotNull('xendit_id')->first();
            if (!$transaction) {
                return $this->failedResponse('not found', 400);
            }
            if (!$transaction->status == Transaction::STATUS_PAID) {
                return $this->failedResponse('already PAID', 400);
            }
            if (!$transaction->status == Transaction::STATUS_CANCELLED) {
                return $this->failedResponse('already CANCELLED', 400);
            }
            $status = match ($request->status) {
                Transaction::STATUS_PAID => Transaction::STATUS_PAID,
                Transaction::STATUS_EXPIRED => Transaction::STATUS_EXPIRED,
                default => $transaction->status
            };
            $transaction->update([
                'status' => $status,
                'paid_at' => $status == Transaction::STATUS_PAID ? Carbon::now() : null
            ]);
            // update bill status with loop in transaction details
            if ($status == Transaction::STATUS_PAID) {
                foreach ($transaction->transactionDetails as $detail) {
                    $detail->bill->update([
                        'status' => Bill::STATUS_PAID
                    ]);
                }
            }

            $transaction->refresh();
            DB::commit();
            return $this->postSuccessResponse('Callback berhasil diterima', ['transaction' => $transaction]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return $this->failedResponse($th->getMessage(), [], 500);
        }
    }
}
