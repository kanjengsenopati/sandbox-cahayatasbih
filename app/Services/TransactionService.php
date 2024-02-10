<?php

namespace App\Services;

use App\Models\ApplicationSetting;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\GymClass;
use App\Models\GymClassBundling;
use App\Models\GymClassBundlingHistory;
use App\Models\GymClassHistory;
use App\Models\Membership;
use App\Models\MembershipHistory;
use App\Models\PersonalTrainerPacketSession;
use App\Models\PersonalTrainerPacketSessionHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;
use Xendit\Configuration;

class TransactionService
{
    public static function changeStatusToPaid($bill)
    {
        Bill::findOrFail($bill)->update([
            'status' => Bill::STATUS_PAID,
        ]);
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
