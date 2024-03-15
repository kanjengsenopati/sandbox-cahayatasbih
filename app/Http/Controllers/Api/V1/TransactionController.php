<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\SaldoHistory;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use App\Jobs\SendToPushNotificationJob;
use App\Http\Requests\Api\V1\TransactionRequest;
use App\Jobs\SendToWhatsappNotificationJob;

class TransactionController extends Controller
{

    public function listPaymentMethod()
    {
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        return $this->getSuccessResponse($paymentMethods);
    }

    public function pay(TransactionRequest $request)
    {
        DB::beginTransaction();

        try {
            $paymentMethodType = PaymentMethod::find($request->payment_method_id)->type;

            $transaction = $this->createTransaction($request, $paymentMethodType);

            if ($paymentMethodType == PaymentMethod::TYPE_XENDIT) {
                TransactionService::createInvoice($transaction);
            } elseif ($paymentMethodType == PaymentMethod::TYPE_BALANCE) {
                $payAmount = $transaction->pay_amount;
                $student = Student::find($request->student_id);

                if ($student->saldo < $payAmount) {
                    DB::rollBack();
                    return $this->failedResponse("Maaf Saldo Santri tidak mencukupi");
                }

                TransactionService::payWithBalance($student, $payAmount, $transaction, $request);
            }

            DB::commit();

            if ($paymentMethodType == PaymentMethod::TYPE_XENDIT) {
                return $this->postSuccessResponse("Berhasil melakukan transaksi pembayaran", $transaction->payment_link);
            } else {
                return $this->postSuccessResponse('Berhasil Membayar Tagihan', ['transaction' => $transaction]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            dd($th);
            return $this->failedResponse("Gagal melakukan transaksi pembayaran");
        }
    }

    private function createTransaction(TransactionRequest $request, $paymentMethodType)
    {
        $paymentCode = 'CHT-' . Str::random(3) . time();

        $transactionData = [
            'pay_amount' => $this->getTotalPayAmount($request->bill_ids),
            'payment_code' => $paymentCode,
            'student_id' => $request->student_id,
            'expiry_time' => Carbon::now()->addDay(),
            'status' => Transaction::STATUS_PENDING,
            'paid_at' => null,
        ];

        $transaction = Transaction::create(array_merge($transactionData, $request->validated()));

        foreach ($request->bill_ids as $billId) {
            $transaction->transactionDetails()->create([
                'bill_id' => $billId,
            ]);
        }

        if ($transaction->status == Transaction::STATUS_PAID && $paymentMethodType == PaymentMethod::TYPE_XENDIT) {
            TransactionService::changeStatusToPaid($transaction);
        }

        return $transaction;
    }

    private function getTotalPayAmount($billIds)
    {
        return Bill::whereIn('id', $billIds)->sum('amount');
    }

    private function dispatchNotifications($transaction)
    {
        $title = 'Yeay!, Pembayaran Berhasil';
        $body = 'Pembayaran di Pondok Pesantren Cahaya Tasbih berhasil! Nikmati Kemudahan Pembayaran. Terima kasih atas kepercayaan Anda.';
        $invoiceLink = route('transaction.invoice', $transaction->id);
        $messageWhatsapp = "🌟 *Pembayaran di Pondok Pesantren Cahaya Tasbih berhasil!* 🌟\n" .
            "Nikmati Kemudahan Pembayaran. Terima kasih atas kepercayaan Anda.\n" .
            "ℹ️ Untuk bukti pembayaran dapat diakses di link berikut: [Bukti Pembayaran]($invoiceLink)";

        dispatch(new SendToPushNotificationJob($title, $body, $transaction->student->user, $transaction));
        dispatch(new SendToWhatsappNotificationJob($transaction->student->user->phone, $messageWhatsapp));
    }

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
            $transaction->refresh();

            if ($status == Transaction::STATUS_PAID) {
                if ($transaction->type == Transaction::TYPE_SALDO) {
                    $student = Student::find($transaction->student_id);
                    $student->update([
                        'saldo' => $student->saldo + $transaction->pay_amount
                    ]);
                    // change status to saldo history
                    $transaction->transactionDetails->first()->saldoHistory->update([
                        'status' => SaldoHistory::STATUS_SUCCESS
                    ]);
                } else {
                    foreach ($transaction->transactionDetails as $detail) {
                        $detail->bill->update([
                            'status' => Bill::STATUS_PAID
                        ]);
                    }
                }
                $this->dispatchNotifications($transaction);
            }

            DB::commit();
            return $this->postSuccessResponse('Callback berhasil diterima', ['transaction' => $transaction]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return $this->failedResponse($th->getMessage(), [], 500);
        }
    }
}
