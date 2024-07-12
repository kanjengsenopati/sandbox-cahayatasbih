<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Contact;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\SaldoHistory;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\SavingHistory;
use App\Models\PpdbRegistration;
use App\Models\TransactionProof;
use App\Models\ApplicationSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\SendNotifWaService;
use App\Services\TransactionService;
use App\Jobs\SendToPushNotificationJob;
use App\Jobs\SendToWhatsappNotificationJob;
use App\Http\Requests\Admin\UploadProofRequest;
use App\Http\Requests\Api\V1\TransactionRequest;
use App\Http\Requests\Admin\ReuploadProofRequest;

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

            $transaction = TransactionService::createTransaction($request, $paymentMethodType, Transaction::TYPE_BILL);

            // if ($paymentMethodType == PaymentMethod::TYPE_XENDIT) {
            //     TransactionService::createInvoice($transaction);
            // } elseif ($paymentMethodType == PaymentMethod::TYPE_BALANCE) {
            //     $payAmount = $transaction->pay_amount;
            //     $student = Student::find($request->student_id);

            //     if ($student->saldo < $payAmount) {
            //         DB::rollBack();
            //         return $this->failedResponse("Maaf Saldo Santri tidak mencukupi");
            //     }

            //     TransactionService::payWithBalance($student, $payAmount, $transaction, $request);
            // } elseif ($paymentMethodType == PaymentMethod::TYPE_TRANSFER) {
            //     //generate unique payment 3 digit
            //     $uniquePayment = rand(100, 999);
            //     $transaction->update([
            //         'status' => Transaction::STATUS_PENDING_PAYMENT,
            //         'paid_at' => null,
            //         'unique_payment' => $uniquePayment,
            //         'pay_amount' => $transaction->pay_amount + $uniquePayment
            //     ]);
            //     // add app fee to transaction
            //     TransactionService::updateAppFee($transaction);
            //     // load data all bank from billType
            //     $transaction->load('transactionDetails.bill.banks');
            //     // send notification to user via whatsapp
            //     $messageWhatsapp = SendNotifWaService::sendMessagePendingTransferPayment($transaction);
            //     dispatch(new SendToWhatsappNotificationJob($transaction->student?->user?->phone, $messageWhatsapp));
            // }

            // create transaction details
            // if ($request->bill_ids) {
            foreach ($request->bill_ids as $billId) {
                $transaction->transactionDetails()->create([
                    'bill_id' => $billId,
                ]);
            }
            // }


            DB::commit();

            if ($paymentMethodType == PaymentMethod::TYPE_XENDIT) {
                return $this->postSuccessResponse("Berhasil melakukan transaksi pembayaran", $transaction->payment_link);
            } else {
                return $this->postSuccessResponse('Berhasil Membayar Tagihan', ['transaction' => $transaction]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return $this->failedResponse("Gagal melakukan transaksi pembayaran");
        }
    }

    private function getTotalPayAmount($billIds)
    {
        return Bill::whereIn('id', $billIds)->sum('amount');
    }

    private function dispatchNotifications($transaction)
    {
        $title = 'Yeay!, Pembayaran Berhasil';
        $body = 'Pembayaran di Pondok Pesantren Cahaya Tasbih berhasil! Terima kasih telah membayar tagihan';
        $messageWhatsapp = SendNotifWaService::sendMessageBillNotification($transaction);
        dispatch(new SendToPushNotificationJob($title, $body, $transaction->student->user, $transaction));
        dispatch(new SendToWhatsappNotificationJob($transaction->student->user->phone, $messageWhatsapp));
    }

    private function dispatchNotificationsUser($transaction)
    {
        $title = 'Yeay!, Pembayaran Berhasil';
        $body = 'Pembayaran di Pondok Pesantren Cahaya Tasbih berhasil! Terima kasih telah membayar tagihan';
        dispatch(new SendToPushNotificationJob($title, $body, $transaction->user, $transaction));
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
                } elseif ($transaction->type == Transaction::TYPE_SAVING) {
                    foreach ($transaction->transactionDetails as $detail) {
                        $detail->savingHistory->update([
                            'status' => SavingHistory::STATUS_SUCCESS
                        ]);
                    }

                    $student = Student::find($transaction->student_id);
                    $student->update([
                        'saving' => $student->saving + $transaction->pay_amount
                    ]);
                } elseif ($transaction->type == Transaction::TYPE_PPDB) {
                    foreach ($transaction->transactionDetails as $detail) {
                        $detail->ppdbRegistration->update([
                            'status' => PpdbRegistration::STATUS_PAID,
                            'payment_status' => PpdbRegistration::STATUS_PAID
                        ]);
                    }
                } else {
                    foreach ($transaction->transactionDetails as $detail) {
                        $detail->bill->update([
                            'status' => Bill::STATUS_PAID
                        ]);
                    }
                }
                if ($transaction->type == Transaction::TYPE_PPDB) {
                    $this->dispatchNotificationsUser($transaction);
                } else {
                    $this->dispatchNotifications($transaction);
                }
            }

            DB::commit();
            return $this->postSuccessResponse('Callback berhasil diterima', ['transaction' => $transaction]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return $this->failedResponse($th->getMessage(), [], 500);
        }
    }

    public function uploadProof(UploadProofRequest $request)
    {
        $transaction = Transaction::find($request->transaction_id);

        if ($transaction->status != Transaction::STATUS_PENDING_PAYMENT) {
            return $this->failedResponse('Transaksi tidak dalam status menunggu konfirmasi', 400);
        }

        $proofImage = 'storage/' . $request->file('proof_image')->store('images/proofs', 'public');

        $transaction->transactionProofs()->create([
            'bank_id' => $request->bank_id,
            'student_id' => $transaction->student_id,
            'proof_image' => $proofImage,
            'status' => TransactionProof::STATUS_WAITING_CONFIRMATION,
        ]);

        // update transaction status
        $transaction->update([
            'status' => Transaction::STATUS_PENDING_CONFIRMATION
        ]);

        // add notif whatsapp
        $messageWhatsapp = SendNotifWaService::sendMessagePendingTransferPayment($transaction);
        dispatch(new SendToWhatsappNotificationJob($transaction->student?->user?->phone, $messageWhatsapp));
        $contacts = Contact::where('type', Contact::TYPE_BENDAHARA)->orWhere('type', Contact::TYPE_SUPERADMIN)->get();
        foreach ($contacts as $contact) {
            dispatch(new SendToWhatsappNotificationJob($contact->phone, $messageWhatsapp));
        }

        return $this->postSuccessResponse('Berhasil mengupload bukti pembayaran', ['transaction' => $transaction]);
    }

    public function proof($id)
    {
        $proof = TransactionProof::with('bank', 'student', 'transaction')->find($id);

        if (!$proof) {
            return $this->failedResponse('Bukti pembayaran tidak ditemukan', 404);
        }

        return $this->getSuccessResponse($proof);
    }

    public function reuploadProof(ReuploadProofRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $proof = TransactionProof::find($id);

            if (!$proof) {
                return $this->failedResponse('Bukti pembayaran tidak ditemukan', 404);
            }

            if ($proof->status == TransactionProof::STATUS_CONFIRMED) {
                return $this->failedResponse('Bukti pembayaran sudah dikonfirmasi', 400);
            }

            // Nonaktifkan bukti pembayaran sebelumnya
            $proof->transaction->activeProof()->update(['is_active' => false]);

            $proofImage = 'storage/' . $request->file('proof_image')->store('images/proofs', 'public');

            // create new proof
            $newProof = TransactionProof::create([
                'transaction_id' => $proof->transaction_id,
                'bank_id' => $request->bank_id,
                'student_id' => $proof->student_id,
                'proof_image' => $proofImage,
                'status' => TransactionProof::STATUS_WAITING_CONFIRMATION,
            ]);


            // update transaction status
            $proof->transaction->update([
                'status' => Transaction::STATUS_PENDING_CONFIRMATION
            ]);

            // add notif whatsapp
            $messageWhatsapp = SendNotifWaService::sendMessagePendingTransferPayment($proof->transaction);
            dispatch(new SendToWhatsappNotificationJob($proof->transaction->student?->user?->phone, $messageWhatsapp));

            $contacts = Contact::where('type', Contact::TYPE_BENDAHARA)->orWhere('type', Contact::TYPE_SUPERADMIN)->get();
            foreach ($contacts as $contact) {
                dispatch(new SendToWhatsappNotificationJob($contact->phone, $messageWhatsapp));
            }
            DB::commit();

            return $this->postSuccessResponse('Berhasil mengupdate bukti pembayaran', ['proof' => $newProof]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Tambahkan logging jika terjadi kesalahan
            Log::error('Error updating proof: ' . $e->getMessage());

            return $this->failedResponse('Terjadi kesalahan saat mengupdate bukti pembayaran', 500);
        }
    }

    public function show($id)
    {
        $transaction = Transaction::with('transactionDetails.bill.banks', 'activeProof.bank', 'student', 'paymentMethod')->find($id);

        if (!$transaction) {
            return $this->failedResponse('Transaksi tidak ditemukan', 404);
        }

        // check tipe transaksi
        if ($transaction->type == Transaction::TYPE_BILL) {
            $transaction->load('transactionDetails.bill.banks');
        } else {
            // Load necessary relationships for TYPE_SALDO and TYPE_SAVING
            $transaction->load('student.classroom.school.topupBank.bank');

            if ($transaction->type == Transaction::TYPE_SALDO) {
                $transaction['banks'] = $transaction?->student?->classroom->school?->saldoBank
                    ->pluck('bank');
            } elseif ($transaction->type == Transaction::TYPE_SAVING) {
                $transaction['banks'] = $transaction?->student?->classroom?->school?->savingBank
                    ->pluck('bank');
            }
        }

        return $this->getSuccessResponse($transaction);
    }
}
