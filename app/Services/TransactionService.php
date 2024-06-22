<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\User;
use App\Models\Student;
use App\Models\BillItem;
use App\Models\GymClass;
use App\Models\TopupBank;
use Xendit\Configuration;
use App\Models\Membership;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\SaldoHistory;
use App\Models\PaymentMethod;
use Xendit\Invoice\InvoiceApi;
use App\Models\GymClassHistory;
use App\Models\GymClassBundling;
use App\Models\MembershipHistory;
use App\Models\TransactionDetail;
use App\Models\ApplicationSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendToPushNotificationJob;
use App\Models\GymClassBundlingHistory;
use Xendit\Invoice\CreateInvoiceRequest;
use App\Jobs\SendToWhatsappNotificationJob;
use App\Models\Contact;
use App\Models\PersonalTrainerPacketSession;
use App\Models\PersonalTrainerPacketSessionHistory;

class TransactionService
{
    public static function changeStatusToPaid($transaction)
    {
        foreach ($transaction->transactionDetails as $detail) {
            if ($detail->bill !== null) {
                $detail->bill->update([
                    'status' => Bill::STATUS_PAID
                ]);
            }
        }
    }


    public static function payWithBalance($student, $pay_amount, $transaction, $request)
    {
        $student->update([
            'saldo' => $student->saldo - $pay_amount
        ]);
        // tambahin history
        $saldoHistory = SaldoHistory::create([
            'student_id' => $student->id,
            'amount' => $pay_amount,
            'type' => SaldoHistory::TYPE_OUT,
            'description' => 'Pembayaran Tagihan Sebesar Rp.' . number_format($pay_amount, 0, ',', '.'),
            'status' => SaldoHistory::STATUS_SUCCESS,
            'usage' => SaldoHistory::USAGE_BILL
        ]);

        // update transaction status
        $transaction->update([
            'status' => Transaction::STATUS_PAID,
            'paid_at' => Carbon::now()
        ]);

        // create transaction detail with saldo history
        foreach ($request->bill_ids as $billId) {
            $transaction->transactionDetails()->create([
                'bill_id' => $billId,
                'saldo_history_id' => $saldoHistory->id
            ]);
        }

        // update bill status with loop in transaction details
        self::changeStatusToPaid($transaction);
    }

    public static function payWithCash($transaction)
    {
        $transaction->update([
            'status' => Transaction::STATUS_PAID,
            'paid_at' => Carbon::now(),
            'admin_id' => Auth::id(),
            'type' => Transaction::TYPE_BILL
        ]);

        self::changeStatusToPaid($transaction);
    }

    public static function createInvoice($transaction)
    {
        // Fetching application settings
        $appSetting = ApplicationSetting::latest()->first();

        // Fetching payment expire time
        $expiredTimeInMinutes = $appSetting->getPaymentExpireTimeInMinutesAttribute();

        // Setting Xendit API Key
        Configuration::setXenditKey(env('XENDIT_API_KEY'));

        // Creating Xendit Invoice
        $invoiceData = self::prepareInvoiceData($transaction, $appSetting, $expiredTimeInMinutes);
        $invoice = self::sendCreateInvoiceRequest($invoiceData);

        // Updating transaction data
        self::updateTransactionData($transaction, $invoice, $expiredTimeInMinutes);

        // Refreshing transaction
        $transaction->refresh();

        return $invoice;
    }

    private static function prepareInvoiceData($transaction, $appSetting, $expiredTimeInMinutes)
    {
        $app_fee = $transaction->type == Transaction::TYPE_BILL ? $appSetting->bill_fee : ($transaction->pay_amount * $appSetting->saldo_fee / 100);
        $amount = $transaction->pay_amount + $app_fee + $appSetting->payment_fee;

        return [
            'external_id' => $transaction->payment_code,
            'description' => 'Transaksi ' . $transaction->payment_code,
            'amount' => $amount,
            'invoice_duration' => $expiredTimeInMinutes * 60,
            'currency' => 'IDR',
            'reminder_time' => 1,
            'payer_email' => env('XENDIT_PAYER_EMAIL'),
            'local' => 'id'
        ];
    }

    private static function sendCreateInvoiceRequest($invoiceData)
    {
        $apiInstance = new InvoiceApi();
        return $apiInstance->createInvoice(new CreateInvoiceRequest($invoiceData));
    }

    private static function updateTransactionData($transaction, $invoice, $expiredTimeInMinutes)
    {
        $appSetting = ApplicationSetting::latest()->first();

        $transaction->update([
            'xendit_fee' => $appSetting->payment_fee,
            'app_fee' => $transaction->type == Transaction::TYPE_BILL ? $appSetting->bill_fee : ($transaction->pay_amount * $appSetting->saldo_fee / 100),
            'xendit_id' => $invoice['id'],
            'expiry_time' => Carbon::now()->addMinutes($expiredTimeInMinutes),
            'payment_link' => $invoice['invoice_url']
        ]);
    }

    public static function createTransaction($request, $paymentMethodType, $type)
    {
        $appSetting = ApplicationSetting::latest()->first();
        $expiryTimeInMinutes = $appSetting->getPaymentExpireTimeInMinutesAttribute();
        $paymentCode = 'CHT-' . Str::random(3) . time();

        $transactionData = [
            'pay_amount' => $request->bill_ids != null ? self::getTotalPayAmount($request->bill_ids) : $request->amount,
            'payment_code' => $paymentCode,
            'student_id' => $request->student_id,
            'expiry_time' => Carbon::now()->addMinutes($expiryTimeInMinutes),
            'status' => Transaction::STATUS_PENDING,
            'paid_at' => null,
            'type' => $type ?? Transaction::TYPE_BILL,
        ];

        $transaction = Transaction::create(array_merge($transactionData, $request->validated()));

        if ($paymentMethodType == PaymentMethod::TYPE_TRANSFER) {
            // Generate unique payment 3 digits
            $uniquePayment = str_pad(rand(1, 300), 3, '0', STR_PAD_LEFT);
            $transaction->update([
                'status' => Transaction::STATUS_PENDING_PAYMENT,
                'paid_at' => null,
                'unique_payment' => $uniquePayment,
                'pay_amount' => $transaction->pay_amount + $uniquePayment
            ]);
            // add app fee to transaction
            TransactionService::updateAppFee($transaction);
            // load data all bank from billType
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

            // send notification to user via whatsapp
            $messageWhatsapp = SendNotifWaService::sendMessagePendingTransferPayment($transaction);
            dispatch(new SendToWhatsappNotificationJob($transaction->student?->user?->phone, $messageWhatsapp));
            // send to all superadmin and bendahara
            $contacts = Contact::where('type', Contact::TYPE_BENDAHARA)->orWhere('type', Contact::TYPE_SUPERADMIN)->get();
            foreach ($contacts as $contact) {
                dispatch(new SendToWhatsappNotificationJob($contact->phone, $messageWhatsapp));
            }
        } elseif ($paymentMethodType == PaymentMethod::TYPE_XENDIT) {
            TransactionService::createInvoice($transaction);
        } elseif ($paymentMethodType == PaymentMethod::TYPE_BALANCE) {
            $payAmount = $transaction->pay_amount;
            $student = Student::find($request->student_id);
            if ($student->saldo < $payAmount) {
                abort(400, 'Saldo tidak mencukupi');
            }

            TransactionService::payWithBalance($student, $payAmount, $transaction, $request);
        }


        if ($transaction->status == Transaction::STATUS_PAID && $paymentMethodType == PaymentMethod::TYPE_XENDIT) {
            TransactionService::changeStatusToPaid($transaction);
        }

        return $transaction;
    }

    public static function getTotalPayAmount($billIds)
    {
        return Bill::whereIn('id', $billIds)->sum('amount');
    }

    public static function dispatchNotifications($transaction)
    {
        $title = 'Yeay!, Pembayaran Berhasil';
        $body = 'Pembayaran di Pondok Pesantren Cahaya Tasbih berhasil! Terima kasih telah membayar tagihan';
        $messageWhatsapp = SendNotifWaService::sendMessageBillNotification($transaction);
        dispatch(new SendToPushNotificationJob($title, $body, $transaction->student->user, $transaction));
        dispatch(new SendToWhatsappNotificationJob($transaction->student->user->phone, $messageWhatsapp));
    }

    public static function createPaymentPpdb($request, $paymentMethodType, $registerFee, $ppdbRegistration)
    {
        $appSetting = ApplicationSetting::latest()->first();
        $expiryTimeInMinutes = $appSetting->getPaymentExpireTimeInMinutesAttribute();
        $paymentCode = 'PPDB-' . Str::random(2) . time();
        // get total pay amount from register fee + payment_fee + bill_fee
        $payAmount = $registerFee + $appSetting->payment_fee + $appSetting->bill_fee;

        $transaction = Transaction::create([
            'payment_method_id' => $paymentMethodType->id,
            'pay_amount' => $payAmount,
            'payment_code' => $paymentCode,
            'expiry_time' => Carbon::now()->addMinutes($expiryTimeInMinutes),
            'status' => Transaction::STATUS_PENDING,
            'paid_at' => null,
            'type' => 'PPDB',
            'user_id' => auth('wali')->user()->id,
            'xendit_fee' => $appSetting->payment_fee,
            'app_fee' => $appSetting->bill_fee,
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'ppdb_registration_id' => $ppdbRegistration->id,
        ]);

        if ($transaction->status == Transaction::STATUS_PAID && $paymentMethodType == PaymentMethod::TYPE_XENDIT) {
            TransactionService::changeStatusToPaid($transaction);
        }

        return $transaction;
    }

    public static function updateAppFee($transaction)
    {
        $appSetting = ApplicationSetting::latest()->first();
        $expiredTimeInMinutes = $appSetting->getPaymentExpireTimeInMinutesAttribute();

        // Hitung app_fee berdasarkan tipe transaksi dan bulatkan ke atas
        $app_fee = $transaction->type == Transaction::TYPE_BILL
            ? $appSetting->bill_fee
            : ceil($transaction->pay_amount * $appSetting->saldo_fee / 100);

        // Perbarui transaksi dengan app_fee yang telah dibulatkan dan informasi lainnya
        $transaction->update([
            'app_fee' => $app_fee,
            'expiry_time' => Carbon::now()->addMinutes($expiredTimeInMinutes),
            'pay_amount' => $transaction->pay_amount + $app_fee
        ]);
    }
}
