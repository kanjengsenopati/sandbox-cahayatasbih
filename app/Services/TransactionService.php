<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\User;
use App\Models\Student;
use App\Models\BillItem;
use App\Models\GymClass;
use Xendit\Configuration;
use App\Models\Membership;
use App\Models\Transaction;
use App\Models\SaldoHistory;
use Xendit\Invoice\InvoiceApi;
use App\Models\GymClassHistory;
use App\Models\GymClassBundling;
use App\Models\MembershipHistory;
use App\Models\ApplicationSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\GymClassBundlingHistory;
use Xendit\Invoice\CreateInvoiceRequest;
use App\Models\PersonalTrainerPacketSession;
use App\Models\PersonalTrainerPacketSessionHistory;

class TransactionService
{
    public static function changeStatusToPaid($transaction)
    {
        foreach ($transaction->transactionDetails as $detail) {
            $detail->bill->update([
                'status' => Bill::STATUS_PAID
            ]);
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

        // update bill status with loop in transaction details
        // self::changeStatusToPaid($transaction);
    }

    public static function storeProgram($transaction)
    {
        foreach ($transaction->transaction_details ?? [] as $transactionDetail) {
            match ($transactionDetail->parentable_type) {
                Membership::class => MembershipHistory::create([
                    'transaction_detail_id' => $transactionDetail->id,
                    'user_id' => $transaction->user_id,
                    'membership_id' => $transactionDetail->parentable_id,
                    'gym_place_id' => $transactionDetail->parent->gym_place_id,
                    'is_active' => true,
                    'start_active_date' => Carbon::now(),
                    'remaining_session' => $transactionDetail->parent->total_session,
                    'expiry_date' => $transactionDetail->parent->is_lifetime ? null : Carbon::now()->addDays($transactionDetail->parent->period),
                    'is_active' => true
                ]),
                GymClass::class => GymClassHistory::create([
                    'transaction_detail_id' => $transactionDetail->id,
                    'user_id' => $transaction->user_id,
                    'gym_class_id' => $transactionDetail->parentable_id,
                    'is_active' => true
                ]),
                GymClassBundling::class => self::storeBundling($transactionDetail),
                PersonalTrainerPacketSession::class => PersonalTrainerPacketSessionHistory::create([
                    'transaction_detail_id' => $transactionDetail->id,
                    'user_id' => $transaction->user_id,
                    'personal_trainer_packet_session_id' => $transactionDetail->parentable_id,
                    'personal_trainer_id' => $transactionDetail->personal_trainer_id,
                    'is_active' => true,
                    'start_active_date' => Carbon::now(),
                    'expiry_date' => Carbon::now()->addDays($transactionDetail->parent->training_period),
                    'is_active' => true,
                    'remaining_session' => $transactionDetail->parent->total_session
                ]),
                default => null
            };
        }
    }

    public static function createInvoice($transaction)
    {
        Configuration::setXenditKey(env('XENDIT_API_KEY'));
        $apiInstance = new InvoiceApi();
        $createInvoice = new CreateInvoiceRequest([
            'external_id' => $transaction->payment_code,
            'description' => 'Transaksi ' . $transaction->payment_code,
            'amount' => $transaction->pay_amount,
            'invoice_duration' => 18000,
            'currency' => 'IDR',
            'reminder_time' => 1,
            'payer_email' => env('XENDIT_PAYER_EMAIL'),
            'local' => 'id'
        ]);
        $invoice = $apiInstance->createInvoice($createInvoice);
        $transaction->update([
            'xendit_id' => $invoice['id'],
            'expiry_time' => Carbon::now()->addSeconds(18000),
            'payment_link' => $invoice['invoice_url']
        ]);

        $transaction->refresh();
        return $invoice;
    }



    public static function storeBundling($transactionDetail)
    {
        GymClassBundlingHistory::create([
            'transaction_detail_id' => $transactionDetail->id,
            'user_id' => $transactionDetail->transaction->user_id,
            'gym_class_bundling_id' => $transactionDetail->parentable_id,
            'is_active' => true,
            'start_active_date' => Carbon::now(),
            'expiry_date' => Carbon::now()->addDays($transactionDetail->parent->period),
            'is_active' => true,
            'personal_trainer_expiry_date' => Carbon::now()->addDays($transactionDetail->parent->period_personal_trainer),
            'personal_trainer_remaining_session' => $transactionDetail->parent->total_session,
            'personal_trainer_id' => $transactionDetail->personal_trainer_id
        ]);
        PersonalTrainerPacketSessionHistory::create([
            'transaction_detail_id' => $transactionDetail->id,
            'user_id' => $transactionDetail->transaction->user_id,
            'gym_class_bundling_id' => $transactionDetail->parentable_id,
            'is_active' => true,
            'start_active_date' => Carbon::now(),
            'expiry_date' => Carbon::now()->addDays($transactionDetail->parent->period_personal_trainer),
            'is_active' => true,
            'remaining_session' => $transactionDetail->parent->total_session,
            'personal_trainer_id' => $transactionDetail->personal_trainer_id
        ]);
        MembershipHistory::create([
            'transaction_detail_id' => $transactionDetail->id,
            'user_id' => $transactionDetail->transaction->user_id,
            'membership_id' => $transactionDetail->parentable_id,
            'gym_place_id' => $transactionDetail->parent->gym_place_id,
            'gym_class_bundling_id' => $transactionDetail->parentable_id,
            'is_active' => true,
            'start_active_date' => Carbon::now(),
            'remaining_session' => null,
            'expiry_date' => Carbon::now()->addDays($transactionDetail->parent->period),
        ]);
    }
}
