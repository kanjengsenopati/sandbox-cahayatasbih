<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Bill;
use App\Models\User;
use App\Models\Contact;
use App\Models\Student;

use App\Models\Membership;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\SaldoHistory;
use App\Models\PaymentMethod;
use App\Models\SavingHistory;

use App\Models\GymClassHistory;
use App\Models\GymClassBundling;
use App\Models\PpdbRegistration;
use App\Models\TransactionProof;
use App\Models\MembershipHistory;
use App\Models\TransactionDetail;
use App\Models\ApplicationSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Jobs\SendToPushNotificationJob;

use App\Jobs\SendToWhatsappNotificationJob;

class TransactionService
{
    protected static $cachedPreloadedRates = null;

    public static function getCachedPreloadedRates()
    {
        if (self::$cachedPreloadedRates === null) {
            self::$cachedPreloadedRates = Cache::remember('preloaded_payment_rates', 3600, function () {
                return \App\Models\PaymentRate::with(['paymentRateItems', 'paymentRateStudents', 'paymentRateClassrooms'])
                    ->whereNull('deleted_at')
                    ->get();
            });
        }
        return self::$cachedPreloadedRates;
    }

    public static function changeStatusToPaid($transaction)
    {
        if ($transaction->type == Transaction::TYPE_BILL) {

            // check apakah transaction detail sudah ada
            if ($transaction->transactionDetails->count() > 0) {
                foreach ($transaction->transactionDetails as $detail) {
                    $bill = $detail->bill;
                    if (!$bill && !empty($detail->bill_id)) {
                        $realBillId = self::ensureBillRecord($transaction->student_id, $detail->bill_id);
                        if ($realBillId && $realBillId !== $detail->bill_id) {
                            $detail->update(['bill_id' => $realBillId]);
                            $bill = Bill::find($realBillId);
                        }
                    }
                    if ($bill) {
                        $paidVal = $detail->amount ?? $bill->remaining_amount;
                        $bill->paid_amount = min($bill->amount, $bill->paid_amount + $paidVal);
                        if ($bill->paid_amount >= $bill->amount) {
                            $bill->status = Bill::STATUS_PAID;
                        } else {
                            $bill->status = Bill::STATUS_UNPAID;
                        }
                        $bill->save();
                    }
                }
            } else {
                // Proses create bill on transaction detail hanya jika belum ada
                $customAmounts = request()->custom_amounts ?? [];
                $billIds = request()->bill_ids ?? [];
                foreach ($billIds as $billId) {
                    $realBillId = self::ensureBillRecord($transaction->student_id, $billId);
                    // Cek apakah detail dengan bill_id ini sudah ada
                    $exists = $transaction->transactionDetails()
                        ->where('bill_id', $realBillId)
                        ->exists();

                    if (!$exists) {
                        $customAmount = isset($customAmounts[$billId]) ? intval($customAmounts[$billId]) : null;
                        TransactionDetail::create([
                            'transaction_id' => $transaction->id,
                            'bill_id' => $realBillId,
                            'amount' => $customAmount,
                        ]);
                    }
                }
                $transaction->refresh();

                // Update status bill setelah transaction details dibuat
                foreach ($transaction->transactionDetails as $detail) {
                    $bill = $detail->bill;
                    if (!$bill && !empty($detail->bill_id)) {
                        $realBillId = self::ensureBillRecord($transaction->student_id, $detail->bill_id);
                        if ($realBillId && $realBillId !== $detail->bill_id) {
                            $detail->update(['bill_id' => $realBillId]);
                            $bill = Bill::find($realBillId);
                        }
                    }
                    if ($bill) {
                        $paidVal = $detail->amount ?? $bill->remaining_amount;
                        $bill->paid_amount = min($bill->amount, $bill->paid_amount + $paidVal);
                        if ($bill->paid_amount >= $bill->amount) {
                            $bill->status = Bill::STATUS_PAID;
                        } else {
                            $bill->status = Bill::STATUS_UNPAID;
                        }
                        $bill->save();
                    }
                }
            }

            // === UNIT TRANSFER HOOK ===
            // Cek apakah ada bill yang terkait dengan konfigurasi pindah unit.
            // Jika ya, otomatis pindahkan siswa ke sekolah & kelas tujuan.
            self::handleUnitTransferIfApplicable($transaction);
        } elseif ($transaction->type == Transaction::TYPE_SALDO) {
            // check apakah transaction detail sudah ada
            if ($transaction->transactionDetails->count() > 0) {
                foreach ($transaction->transactionDetails as $detail) {
                    $detail->saldoHistory->update([
                        'status' => SaldoHistory::STATUS_SUCCESS
                    ]);
                }
                $transaction->student->increment('saldo', $transaction->pay_amount);
            }
        } elseif ($transaction->type == Transaction::TYPE_SAVING) {
            $transaction->student->update([
                'saving' => $transaction->student->saving + $transaction->pay_amount
            ]);
            // check apakah transaction detail sudah ada
            if ($transaction->transactionDetails->count() > 0) {
                foreach ($transaction->transactionDetails as $detail) {
                    $detail->savingHistory->update([
                        'status' => SaldoHistory::STATUS_SUCCESS
                    ]);
                }
            } else {
                // create saving history
                $savingHistory = SaldoHistory::create([
                    'student_id' => $transaction->student->id,
                    'amount' => $transaction->pay_amount,
                    'type' => SaldoHistory::TYPE_IN,
                    'description' => 'Top Up Tabungan Sebesar Rp.' . number_format($transaction->pay_amount, 0, ',', '.'),
                    'status' => SaldoHistory::STATUS_SUCCESS,
                    'usage' => SaldoHistory::USAGE_TOPUP
                ]);
            }
        }
    }


    public static function payWithBalance($student, $pay_amount, $transaction, $request)
    {
        // Hitung saldo sebelum transaksi
        $balanceBefore = $student->saldo;

        // Kurangi saldo siswa dengan jumlah pembayaran
        $student->update([
            'saldo' => $student->saldo - $pay_amount
        ]);

        // Hitung saldo setelah transaksi
        $balanceAfter = $student->saldo;

        // Tambahkan history saldo dengan balance_before dan balance_after
        $saldoHistory = SaldoHistory::create([
            'student_id' => $student->id,
            'amount' => $pay_amount,
            'type' => SaldoHistory::TYPE_OUT,
            'description' => 'Pembayaran Tagihan Sebesar Rp.' . number_format($pay_amount, 0, ',', '.'),
            'status' => SaldoHistory::STATUS_SUCCESS,
            'usage' => SaldoHistory::USAGE_BILL,
            'balance_before' => $balanceBefore ?? 0, // Saldo sebelum transaksi
            'balance_after' => $balanceAfter ?? 0, // Saldo setelah transaksi
        ]);

        // Update status transaksi
        $transaction->update([
            'status' => Transaction::STATUS_PAID,
            'paid_at' => Carbon::now(),
            'payment_method_id' => PaymentMethod::where('type', PaymentMethod::TYPE_BALANCE)->first()?->id,
        ]);

        // Loop untuk menambahkan detail transaksi jika belum ada atau update saldo_history_id
        $customAmounts = $request->custom_amounts ?? [];
        if ($request->bill_ids) {
            foreach ($request->bill_ids as $billId) {
                $realBillId = self::ensureBillRecord($student->id, $billId);
                $detail = $transaction->transactionDetails()
                    ->where('bill_id', $realBillId)
                    ->first();

                if ($detail) {
                    $detail->update(['saldo_history_id' => $saldoHistory->id]);
                } else {
                    $customAmount = isset($customAmounts[$billId]) ? intval($customAmounts[$billId]) : null;
                    $transaction->transactionDetails()->create([
                        'bill_id' => $realBillId,
                        'amount' => $customAmount,
                        'saldo_history_id' => $saldoHistory->id,
                    ]);
                }
            }
        }

        // Update status tagihan menjadi "Paid"
        self::changeStatusToPaid($transaction);
    }

    public static function payWithCash($transaction)
    {
        $transaction->update([
            'status' => Transaction::STATUS_PAID,
            'paid_at' => Carbon::now(),
            'admin_id' => Auth::id() ?? null,
        ]);

        self::changeStatusToPaid($transaction);
    }



    public static function createTransaction($request, $paymentMethodType, $type)
    {
        // Menggunakan mutex untuk mencegah dual transaksi
        $lock = Cache::lock('transaction_lock_' . $request->student_id, 10); // Mengunci selama 10 detik

        if ($lock->get()) {
            try {
                return DB::transaction(function () use ($request, $paymentMethodType, $type) {
                    $appSetting = ApplicationSetting::latest()->first();
                    $expiryTimeInMinutes = $appSetting ? $appSetting->getPaymentExpireTimeInMinutesAttribute() : 1440; // Default to 24 hours (1440 minutes)

                    // Menghitung jumlah transaksi yang ada
                    $transactionCount = Transaction::whereDate('created_at', now())->count();
                    $paymentCode = 'CHT-' . now()->format('Ymd') . str_pad($transactionCount + 1, 3, '0', STR_PAD_LEFT);
                    if ($request->custom_amounts) {
                        $pay_amount = array_sum($request->custom_amounts);
                    } else {
                        $pay_amount = $request->bill_ids != null ? self::getTotalPayAmount($request->bill_ids, $request->student_id) : $request->amount;
                    }

                    $transactionData = [
                        'pay_amount' => $pay_amount,
                        'payment_code' => $paymentCode,
                        'student_id' => $request->student_id,
                        'expiry_time' => Carbon::now()->addMinutes($expiryTimeInMinutes),
                        'status' => Transaction::STATUS_PENDING,
                        'paid_at' => null,
                        'type' => $type ?? Transaction::TYPE_BILL,
                        'admin_id' => Auth::id() ?? null,
                    ];

                    // Use validated() for FormRequest, fall back to all() for plain Request
                    $validatedData = method_exists($request, 'validated') && $request instanceof \Illuminate\Foundation\Http\FormRequest
                        ? $request->validated()
                        : $request->only(['student_id', 'payment_method_id', 'amount', 'bill_ids', 'pay_amount']);
                    
                    // Auto-cancel previous pending transactions of the same type
                    $pendingTransactions = Transaction::where('student_id', $request->student_id)
                        ->where('type', $type ?? Transaction::TYPE_BILL)
                        ->whereIn('status', [Transaction::STATUS_PENDING, Transaction::STATUS_PENDING_PAYMENT])
                        ->get();

                    foreach ($pendingTransactions as $pendingTx) {
                        $pendingTx->update(['status' => Transaction::STATUS_CANCELLED]);

                        // Cancel related Saldo/Saving History by deleting them
                        if ($pendingTx->type === Transaction::TYPE_SALDO || $pendingTx->type === Transaction::TYPE_SAVING) {
                            $details = \App\Models\TransactionDetail::where('transaction_id', $pendingTx->id)->get();
                            foreach ($details as $detail) {
                                if ($detail->saldo_history_id) {
                                    \App\Models\SaldoHistory::where('id', $detail->saldo_history_id)->delete();
                                }
                                if ($detail->saving_history_id) {
                                    \App\Models\SavingHistory::where('id', $detail->saving_history_id)->delete();
                                }
                            }
                        }
                    }

                    $transaction = Transaction::create(array_merge($validatedData, $transactionData));

                    if (($type ?? Transaction::TYPE_BILL) == Transaction::TYPE_BILL && $request->bill_ids) {
                        $customAmounts = $request->custom_amounts ?? [];
                        $studentId = $request->student_id ?? $transaction->student_id;
                        foreach ($request->bill_ids as $billId) {
                            $realBillId = self::ensureBillRecord($studentId, $billId);
                            $customAmount = isset($customAmounts[$billId]) ? intval($customAmounts[$billId]) : null;
                            TransactionDetail::create([
                                'transaction_id' => $transaction->id,
                                'bill_id' => $realBillId,
                                'amount' => $customAmount,
                            ]);
                        }
                    }

                    // Logika untuk jenis pembayaran
                    if ($paymentMethodType == PaymentMethod::TYPE_TRANSFER) {
                        // Generate unique 3 digits code (101-999) tanpa bentrok dengan pending payment lain
                        $uniquePayment = self::generateUniqueDigitsForAmount($transaction->pay_amount);
                        $transaction->update([
                            'status' => Transaction::STATUS_PENDING_PAYMENT,
                            'paid_at' => null,
                            'unique_payment' => $uniquePayment,
                            'pay_amount' => $transaction->pay_amount + $uniquePayment
                        ]);
                        // Tambahkan biaya aplikasi ke transaksi
                        TransactionService::updateAppFee($transaction);
                        // Load data bank dari billType
                        if ($transaction->type == Transaction::TYPE_BILL) {
                            $transaction->load('transactionDetails.bill.banks');
                        } else {
                            // Load hubungan yang diperlukan untuk TYPE_SALDO dan TYPE_SAVING
                            $transaction->load('student.classroom.school.topupBank.bank');
                            if ($transaction->type == Transaction::TYPE_SALDO) {
                                $transaction['banks'] = $transaction?->student?->classroom?->school?->saldoBank?->pluck('bank');
                            } elseif ($transaction->type == Transaction::TYPE_SAVING) {
                                $transaction['banks'] = $transaction?->student?->classroom?->school?->savingBank?->pluck('bank');
                            }
                        }

                        // Kirim notifikasi ke pengguna melalui WhatsApp
                        $messageWhatsapp = SendNotifWaService::sendMessagePendingTransferPayment($transaction);
                        dispatch(new SendToWhatsappNotificationJob($transaction->student?->user?->phone, $messageWhatsapp));
                        // Kirim ke semua superadmin dan bendahara
                        $contacts = Contact::where('type', Contact::TYPE_BENDAHARA)->orWhere('type', Contact::TYPE_SUPERADMIN)->get();
                        foreach ($contacts as $contact) {
                            dispatch(new SendToWhatsappNotificationJob($contact->phone, $messageWhatsapp));
                        }
                    // Xendit payment gateway telah dihapus.
                    // Metode pembayaran yang tersisa: Transfer, Balance, Cash.
                    } elseif ($paymentMethodType == PaymentMethod::TYPE_BALANCE) {
                        $payAmount = $transaction->pay_amount;
                        // Pessimistic lock: cegah double debit pada concurrent requests
                        $student = Student::where('id', $request->student_id)->lockForUpdate()->first();
                        if (!$student || $student->saldo < $payAmount) {
                            throw new \Exception('Saldo tidak mencukupi');
                        }
                        TransactionService::payWithBalance($student, $payAmount, $transaction, $request);
                    } elseif ($paymentMethodType == PaymentMethod::TYPE_CASH) {
                        $transaction->update(['payment_method_id' => PaymentMethod::where('type', $paymentMethodType)->first()->id]);
                        TransactionService::payWithCash($transaction);
                    }



                    return $transaction;
                });
            } finally {
                $lock->release();
            }
        } else {
            return response()->json(['status' => 'error', 'message' => 'Transaksi sedang diproses, silakan coba lagi.'], 429);
        }
    }



    public static function getTotalPayAmount($billIds, $studentId = null)
    {
        $total = 0;
        foreach ((array)$billIds as $billId) {
            $realId = $studentId ? self::ensureBillRecord($studentId, $billId) : $billId;
            $bill = Bill::find($realId);
            if ($bill) {
                $total += $bill->remaining_amount;
            }
        }
        return $total;
    }

    public static function dispatchNotifications($transaction)
    {
        $messageWhatsapp = SendNotifWaService::sendMessageBillNotification($transaction);
        \App\Services\NotificationService::sendFromTemplate('payment_success', $transaction->student->user, [], $transaction);
        dispatch(new SendToWhatsappNotificationJob($transaction->student->user->phone, $messageWhatsapp));
        $contacts = Contact::where('type', Contact::TYPE_BENDAHARA)->orWhere('type', Contact::TYPE_SUPERADMIN)->get();
        if ($contacts->isNotEmpty()) {
            foreach ($contacts as $contact) {
                dispatch(new SendToWhatsappNotificationJob($contact->phone, $messageWhatsapp));
            }
        }
    }

    public static function createPaymentPpdb($request, $paymentMethodType, $registerFee, $ppdbRegistration)
    {
        $appSetting = ApplicationSetting::latest()->first();
        $expiryTimeInMinutes = $appSetting ? $appSetting->getPaymentExpireTimeInMinutesAttribute() : 1440;
        $paymentCode = 'PPDB-' . Str::random(2) . time();
        // get total pay amount from register fee + payment_fee + bill_fee
        $payAmount = $registerFee + ($appSetting ? $appSetting->payment_fee : 0) + ($appSetting ? $appSetting->bill_fee : 0);

        $transaction = Transaction::create([
            'payment_method_id' => $paymentMethodType->id,
            'pay_amount' => $payAmount,
            'payment_code' => $paymentCode,
            'expiry_time' => Carbon::now()->addMinutes($expiryTimeInMinutes),
            'status' => Transaction::STATUS_PENDING,
            'paid_at' => null,
            'type' => 'PPDB',
            'user_id' => auth('wali')->user()->id,
            'app_fee' => $appSetting ? $appSetting->bill_fee : 0,
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'ppdb_registration_id' => $ppdbRegistration->id,
        ]);



        return $transaction;
    }

    public static function updateAppFee($transaction)
    {
        $appSetting = ApplicationSetting::latest()->first();
        $expiredTimeInMinutes = $appSetting ? $appSetting->getPaymentExpireTimeInMinutesAttribute() : 1440;

        // Hitung app_fee berdasarkan tipe transaksi dan bulatkan ke atas
        $app_fee = 0;
        if ($appSetting) {
            $app_fee = $transaction->type == Transaction::TYPE_BILL
                ? $appSetting->bill_fee
                : ceil($transaction->pay_amount * $appSetting->saldo_fee / 100);
        }

        // Perbarui transaksi dengan app_fee yang telah dibulatkan dan informasi lainnya
        $transaction->update([
            'app_fee' => $app_fee,
            'expiry_time' => Carbon::now()->addMinutes($expiredTimeInMinutes),
            'pay_amount' => $transaction->pay_amount + $app_fee
        ]);
    }

    public static function updateStatusPaymentTransfer($data, $transaction)
    {
        DB::beginTransaction();
        try {
            $oldStatus = $transaction->status;
            $transaction->update($data);

            // Rollback logic: Transition from PAID to non-PAID status
            if ($oldStatus === Transaction::STATUS_PAID && $transaction->status !== Transaction::STATUS_PAID) {
                if ($transaction->unique_payment > 0) {
                    $student = Student::find($transaction->student_id);
                    if ($student) {
                        $affected = Student::where('id', $student->id)
                            ->where('saldo', '>=', $transaction->unique_payment)
                            ->decrement('saldo', $transaction->unique_payment);
                        
                        if ($affected) {
                            Log::info("Rollback Kode Unik: Mengurangi saldo siswa {$student->name} ({$student->id}) sebesar Rp.{$transaction->unique_payment} akibat pembatalan transaksi.");
                        } else {
                            Log::warning("Rollback Kode Unik: Saldo siswa {$student->name} ({$student->id}) tidak mencukupi untuk dikurangi Rp.{$transaction->unique_payment}.");
                        }
                    }
                    \App\Models\SaldoHistory::where('student_id', $transaction->student_id)
                        ->where('amount', (int) $transaction->unique_payment)
                        ->where('description', 'like', '%Kode Unik%')
                        ->forceDelete();
                }

                if ($transaction->type == Transaction::TYPE_SALDO) {
                    $student = Student::find($transaction->student_id);
                    $transactionDetail = $transaction->transactionDetails->first();
                    if ($transactionDetail && $transactionDetail->saldoHistory && $student) {
                        $amountToSub = $transactionDetail->saldoHistory->amount;
                        $affected = Student::where('id', $student->id)
                            ->where('saldo', '>=', $amountToSub)
                            ->decrement('saldo', $amountToSub);
                        
                        if (!$affected && $student->saldo < $amountToSub) {
                            throw new \Exception("Gagal membatalkan transaksi: Saldo santri saat ini (Rp " . number_format($student->saldo, 0, ',', '.') . ") tidak mencukupi untuk ditarik kembali sebesar Rp " . number_format($amountToSub, 0, ',', '.') . ".");
                        }
                    }
                } elseif ($transaction->type == Transaction::TYPE_SAVING) {
                    $student = Student::find($transaction->student_id);
                    $transactionDetail = $transaction->transactionDetails->first();
                    if ($transactionDetail && $transactionDetail->savingHistory && $student) {
                        $amountToSub = $transactionDetail->savingHistory->amount;
                        $affected = Student::where('id', $student->id)
                            ->where('saving', '>=', $amountToSub)
                            ->decrement('saving', $amountToSub);
                        
                        if (!$affected && $student->saving < $amountToSub) {
                            throw new \Exception("Gagal membatalkan transaksi: Tabungan santri saat ini tidak mencukupi untuk ditarik kembali.");
                        }
                    }
                } elseif ($transaction->type == Transaction::TYPE_BILL) {
                    $transaction->transactionDetails->each(function ($detail) {
                        $bill = $detail->bill;
                        if ($bill) {
                            $paidVal = $detail->amount ?? $bill->remaining_amount;
                            $bill->paid_amount = max(0, $bill->paid_amount - $paidVal);
                            $bill->status = Bill::STATUS_UNPAID;
                            $bill->save();
                        }
                    });
                }
            }

            // Normal processing transition to PAID
            if ($oldStatus !== Transaction::STATUS_PAID && $transaction->status == Transaction::STATUS_PAID) {
                // change transaction status to paid
                $transaction->update([
                    'status' => Transaction::STATUS_PAID,
                    'paid_at' => Carbon::now(),
                ]);
                if ($transaction->activeProof !== null) {
                    $transaction->activeProof->update([
                        'status' => TransactionProof::STATUS_CONFIRMED
                    ]);
                }
                // check unique payment
                if ($transaction->unique_payment > 0) {
                    $student = Student::find($transaction->student_id);

                    // Buat history untuk unique payment
                    SaldoService::addHistory(
                        $student,
                        $transaction->unique_payment,
                        SaldoHistory::TYPE_IN,
                        SaldoHistory::USAGE_TOPUP,
                        SaldoHistory::STATUS_SUCCESS,
                        'Pengembalian Kode Unik Transaksi Sebesar Rp.' . number_format($transaction->unique_payment, 0, ',', '.')
                    );

                    // Update saldo siswa secara atomic untuk unique payment
                    $student->increment('saldo', $transaction->unique_payment);
                }
                // change bill status to paid
                if ($transaction->type == Transaction::TYPE_BILL) {
                    $transaction->transactionDetails->each(function ($detail) use ($transaction) {
                        $bill = $detail->bill;
                        if (!$bill && !empty($detail->bill_id)) {
                            $realBillId = self::ensureBillRecord($transaction->student_id, $detail->bill_id);
                            if ($realBillId && $realBillId !== $detail->bill_id) {
                                $detail->update(['bill_id' => $realBillId]);
                                $bill = Bill::find($realBillId);
                            }
                        }
                        if ($bill) {
                            $paidVal = $detail->amount ?? $bill->remaining_amount;
                            $bill->paid_amount = min($bill->amount, $bill->paid_amount + $paidVal);
                            if ($bill->paid_amount >= $bill->amount) {
                                $bill->status = Bill::STATUS_PAID;
                            } else {
                                $bill->status = Bill::STATUS_UNPAID;
                            }
                            $bill->save();
                        }
                    });

                    // === UNIT TRANSFER HOOK ===
                    self::handleUnitTransferIfApplicable($transaction);
                }
                // Proses transaksi berdasarkan tipe
                if ($transaction->type == Transaction::TYPE_SALDO) {
                    $student = Student::find($transaction->student_id);
                    $transactionDetail = $transaction?->transactionDetails?->first();

                    // Hitung nominal pokok topup (tanpa kode unik)
                    $mainAmount = $transaction->unique_payment > 0 
                        ? ($transaction->pay_amount - $transaction->unique_payment) 
                        : ($transaction->amount ?? $transaction->pay_amount);

                    if ($transactionDetail && $transactionDetail->saldoHistory) {
                        $saldoBefore = $student->saldo;
                        $amountToAdd = $transactionDetail->saldoHistory->amount;

                        // Update saldo siswa secara atomic
                        $student->increment('saldo', $amountToAdd);

                        // Update status saldo history jika ada
                        $transactionDetail->saldoHistory->update([
                            'status' => SaldoHistory::STATUS_SUCCESS,
                            'balance_before' => $saldoBefore ?? 0,
                            'balance_after' => $student->saldo ?? 0,
                        ]);
                    } else {
                        // Fallback auto-recovery: jika TransactionDetail/SaldoHistory belum ada (kasus PWA lama)
                        $saldoBefore = $student->saldo;
                        $student->increment('saldo', $mainAmount);

                        $txTimestamp = $transaction->created_at ?? \Carbon\Carbon::now();
                        $saldoHistory = SaldoHistory::create([
                            'student_id' => $student->id,
                            'amount' => $mainAmount,
                            'type' => SaldoHistory::TYPE_IN,
                            'description' => 'Top Up Saldo Saku Sebesar Rp.' . number_format($mainAmount, 0, ',', '.'),
                            'status' => SaldoHistory::STATUS_SUCCESS,
                            'usage' => SaldoHistory::USAGE_TOPUP,
                            'balance_before' => $saldoBefore ?? 0,
                            'balance_after' => $student->saldo ?? 0,
                            'created_at' => $txTimestamp,
                            'updated_at' => $txTimestamp,
                        ]);

                        if ($transactionDetail) {
                            $transactionDetail->update(['saldo_history_id' => $saldoHistory->id]);
                        } else {
                            TransactionDetail::create([
                                'transaction_id' => $transaction->id,
                                'saldo_history_id' => $saldoHistory->id,
                                'created_at' => $txTimestamp,
                                'updated_at' => $txTimestamp,
                            ]);
                        }
                    }

                    // Recalculate running balance to guarantee chronological precision
                    SaldoRecalculatorService::recalculateForStudent($student->id);
                } elseif ($transaction->type == Transaction::TYPE_SAVING) {
                    foreach ($transaction->transactionDetails as $detail) {
                        $detail->savingHistory->update([
                            'status' => SavingHistory::STATUS_SUCCESS
                        ]);
                    }

                    $student = Student::find($transaction->student_id);
                    $student->update([
                        'saving' => $student->saving + $transaction->transactionDetails->first()->savingHistory->amount
                    ]);
                } elseif ($transaction->type == Transaction::TYPE_PPDB) {
                    foreach ($transaction->transactionDetails as $detail) {
                        $detail->ppdbRegistration->update([
                            'status' => PpdbRegistration::STATUS_PAID,
                            'payment_status' => PpdbRegistration::STATUS_PAID
                        ]);
                    }
                }
                self::dispatchNotifications($transaction);
            }

            // Normal processing transition to REJECTED (or if status is REJECTED)
            if ($transaction->status == Transaction::STATUS_REJECTED) {
                if ($transaction->activeProof) {
                    $transaction->activeProof->update([
                        'status' => TransactionProof::STATUS_REJECTED,
                        'note' => ($data['note'] ?? null) ?: "Kode Unik Tidak Sama, pastikan nominal transfer sesuai dengan yang tertera (3 digit kode unik wajib sama)",
                    ]);
                }

                // Safety net: Clean up any orphaned kode unik SaldoHistory records
                // This handles cases where kode unik was added during a PAID transition
                // but the PAID→non-PAID rollback didn't clean it up properly
                if ($transaction->unique_payment > 0) {
                    $student = Student::find($transaction->student_id);
                    $deletedCount = \App\Models\SaldoHistory::where('student_id', $student->id)
                        ->where('amount', (int) $transaction->unique_payment)
                        ->where('description', 'like', '%Kode Unik%')
                        ->count();

                    if ($deletedCount > 0 && $student) {
                        // Only decrement saldo if the rollback block above didn't already handle it
                        // (i.e., when the old status was NOT PAID, meaning the PAID→non-PAID block didn't fire)
                        if ($oldStatus !== Transaction::STATUS_PAID) {
                            Student::where('id', $student->id)
                                ->where('saldo', '>=', $transaction->unique_payment)
                                ->decrement('saldo', $transaction->unique_payment);
                            Log::info("REJECTED Safety Net: Mengurangi saldo siswa {$student->name} ({$student->id}) sebesar Rp.{$transaction->unique_payment} (kode unik orphan).");
                        }
                        \App\Models\SaldoHistory::where('student_id', $student->id)
                            ->where('amount', (int) $transaction->unique_payment)
                            ->where('description', 'like', '%Kode Unik%')
                            ->forceDelete();
                        Log::info("REJECTED Cleanup: Force-deleted {$deletedCount} kode unik SaldoHistory record(s) for student {$student->name} ({$student->id}).");
                    }
                }

                // Delete related history so no history appears in UI
                if ($transaction->type == Transaction::TYPE_SALDO) {
                    $transaction->transactionDetails->each(function ($detail) {
                        $detail->saldoHistory?->delete();
                    });
                } elseif ($transaction->type == Transaction::TYPE_SAVING) {
                    $transaction->transactionDetails->each(function ($detail) {
                        $detail->savingHistory?->delete();
                    });
                }
                // send notification to whatsapp
                $messageWhatsapp = SendNotifWaService::sendMessageRejectedPayment($transaction);
                \App\Services\NotificationService::sendFromTemplate('payment_rejected', $transaction->student->user, [], $transaction);
                dispatch(new SendToWhatsappNotificationJob($transaction->student->user->phone, $messageWhatsapp));
                $contacts = Contact::where('type', Contact::TYPE_BENDAHARA)->orWhere('type', Contact::TYPE_SUPERADMIN)->get();
                foreach ($contacts as $contact) {
                    dispatch(new SendToWhatsappNotificationJob($contact->phone, $messageWhatsapp));
                }
            } elseif ($transaction->activeProof && $transaction->status !== Transaction::STATUS_PAID) {
                $transaction->activeProof->update([
                    'status' => $data['status'],
                    'note' => null,
                ]);
            }
            DB::commit();
            return [
                'status' => true,
                'message' => "Berhasil mengubah status transaksi",
                'transaction' => $transaction
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return [
                'status' => false,
                'message' => "Gagal mengubah status transaksi"
            ];
        }
    }

    /**
     * Automasi Pindah Unit Pendidikan.
     *
     * Jika bill yang dibayar terkait dengan UnitTransferConfig yang aktif,
     * maka siswa otomatis dipindahkan ke sekolah & kelas tujuan.
     * NIS dan barcode TIDAK berubah (Single Profile).
     */
    public static function handleUnitTransferIfApplicable($transaction)
    {
        try {
            $transaction->load('transactionDetails.bill');

            foreach ($transaction->transactionDetails as $detail) {
                $bill = $detail->bill;
                if (!$bill) continue;

                // Cari konfigurasi transfer yang aktif berdasarkan bill_type_id
                $config = \App\Models\UnitTransferConfig::where('bill_type_id', $bill->bill_type_id)
                    ->where('is_active', true)
                    ->first();

                if (!$config) continue;

                $student = Student::find($transaction->student_id);
                if (!$student) continue;

                // Guard: Pastikan siswa masih di sekolah asal (idempotent)
                if ($student->school_id != $config->from_school_id) {
                    Log::info("[UnitTransfer] Skipped: Student #{$student->id} tidak di sekolah asal (expected: {$config->from_school_id}, actual: {$student->school_id})");
                    continue;
                }

                // Eksekusi perpindahan
                $oldSchoolId = $student->school_id;
                $oldClassroomId = $student->classroom_id;

                $student->update([
                    'school_id' => $config->to_school_id,
                    'classroom_id' => $config->to_classroom_id,
                ]);

                Log::info("[UnitTransfer] Student #{$student->id} ({$student->name}) dipindahkan: School {$oldSchoolId} -> {$config->to_school_id}, Classroom {$oldClassroomId} -> {$config->to_classroom_id}");

                // Cukup proses 1 konfigurasi transfer per transaksi
                break;
            }
        } catch (\Throwable $e) {
            // Log tapi jangan gagalkan transaksi utama
            Log::error("[UnitTransfer] Error: " . $e->getMessage(), [
                'transaction_id' => $transaction->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public static function ensureBillRecord($studentId, $billIdOrDescriptor)
    {
        if (empty($billIdOrDescriptor)) {
            return $billIdOrDescriptor;
        }

        if (strpos($billIdOrDescriptor, 'auto_') === false && strpos($billIdOrDescriptor, 'generated_') === false) {
            $existing = \App\Models\Bill::find($billIdOrDescriptor);
            if ($existing) {
                return $existing->id;
            }
        }

        // Descriptor format:
        // 1. auto_{billTypeId}_{month}_{year}
        // 2. generated_{billTypeId}_{studentId}_{month}_{year}
        $parts = explode('_', $billIdOrDescriptor);
        if (count($parts) >= 4) {
            $prefix = $parts[0];
            if ($prefix === 'generated' && count($parts) >= 5) {
                $billTypeId = $parts[1];
                $month = (string) $parts[3];
                $year = (string) $parts[4];
            } elseif ($prefix === 'auto' || $prefix === 'generated') {
                $billTypeId = $parts[1];
                $month = (string) $parts[2];
                $year = (string) $parts[3];
            } else {
                return $billIdOrDescriptor;
            }

            $billType = \App\Models\BillType::with('billItem')->find($billTypeId);
            $student = \App\Models\Student::find($studentId);

            if (!$billType || !$student) {
                return $billIdOrDescriptor;
            }

            $upperBtName = strtoupper($billType->name ?? '');
            $isZarkasi = str_contains($upperBtName, 'ZARKASI');
            $isAplikasi = str_contains($upperBtName, 'APLIKASI');
            $isSyahriah = str_contains($upperBtName, 'SYAHR');

            $existingBillQuery = \App\Models\Bill::where('student_id', $studentId)
                ->where('month', (string)$month)
                ->whereNull('deleted_at');

            if ($isZarkasi || $isAplikasi || $isSyahriah) {
                $existingBillQuery->whereHas('billType', function ($q) use ($isZarkasi, $isAplikasi, $isSyahriah) {
                    $q->where(function ($qq) use ($isZarkasi, $isAplikasi, $isSyahriah) {
                        if ($isZarkasi) $qq->orWhere('name', 'like', '%ZARKASI%');
                        if ($isAplikasi) $qq->orWhere('name', 'like', '%APLIKASI%');
                        if ($isSyahriah) $qq->orWhere('name', 'like', '%SYAHR%');
                    });
                });
            } else {
                $existingBillQuery->where('bill_type_id', $billTypeId);
            }

            $existingBill = $existingBillQuery->first();

            if ($existingBill) {
                return $existingBill->id;
            }

            $sampleBillQuery = \App\Models\Bill::where('student_id', $studentId)
                ->where('amount', '>', 0);

            if ($isZarkasi || $isAplikasi || $isSyahriah) {
                $sampleBillQuery->whereHas('billType', function ($q) use ($isZarkasi, $isAplikasi, $isSyahriah) {
                    $q->where(function ($qq) use ($isZarkasi, $isAplikasi, $isSyahriah) {
                        if ($isZarkasi) $qq->orWhere('name', 'like', '%ZARKASI%');
                        if ($isAplikasi) $qq->orWhere('name', 'like', '%APLIKASI%');
                        if ($isSyahriah) $qq->orWhere('name', 'like', '%SYAHR%');
                    });
                });
            } else {
                $sampleBillQuery->where('bill_type_id', $billTypeId);
            }

            $sampleBill = $sampleBillQuery->first();

            $amount = self::resolveStudentRateForBillType($student, $billType, $month, $year);
            if ($amount <= 0 && $sampleBill) {
                $amount = $sampleBill->amount;
            }

            $targetBillTypeId = $sampleBill ? $sampleBill->bill_type_id : $billTypeId;
            $classroomId = $sampleBill ? $sampleBill->classroom_id : $student->classroom_id;
            $academicYearId = $sampleBill ? $sampleBill->academic_year_id : $billType->academic_year_id;

            $newBill = \App\Models\Bill::create([
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'bill_type_id' => $targetBillTypeId,
                'student_id' => $studentId,
                'classroom_id' => $classroomId,
                'academic_year_id' => $academicYearId,
                'month' => $month,
                'year' => $year,
                'amount' => $amount,
                'paid_amount' => 0,
                'status' => 'UNPAID',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $newBill->id;
        }

        return $billIdOrDescriptor;
    }

    public static function syncStudentBillsFromPaidTransactions($studentId)
    {
        if (empty($studentId)) return;

        try {
            $paidTransactions = Transaction::with(['transactionDetails.bill', 'student'])
                ->where('student_id', $studentId)
                ->where('type', Transaction::TYPE_BILL)
                ->whereIn('status', [Transaction::STATUS_PAID, 'paid', 'PAID', 'approved', 'APPROVED', 'SUCCESS', 'success', 'LUNAS', 'lunas'])
                ->get();

            if ($paidTransactions->isEmpty()) return;

            DB::transaction(function () use ($paidTransactions, $studentId) {
                foreach ($paidTransactions as $tx) {
                    foreach ($tx->transactionDetails as $detail) {
                        $billId = $detail->bill_id;
                        if (empty($billId)) continue;

                        $bill = $detail->bill;
                        $isVirtual = str_starts_with($billId, 'generated_') || str_starts_with($billId, 'auto_');

                        if (!$bill || $isVirtual) {
                            $realBillId = self::ensureBillRecord($studentId, $billId);
                            if ($realBillId && $realBillId !== $billId) {
                                $detail->update(['bill_id' => $realBillId]);
                                $bill = Bill::find($realBillId);
                            }
                        }

                        if ($bill && $bill->status !== Bill::STATUS_PAID) {
                            $detailAmount = intval($detail->amount ?? 0);
                            $paidVal = $detailAmount > 0 ? $detailAmount : ($bill->amount > 0 ? $bill->amount : 10000);
                            if ($bill->paid_amount < $bill->amount) {
                                $newPaid = min($bill->amount, $bill->paid_amount + $paidVal);
                                $newStatus = ($newPaid >= $bill->amount) ? Bill::STATUS_PAID : $bill->status;
                                if ($bill->paid_amount != $newPaid || $bill->status != $newStatus) {
                                    $bill->paid_amount = $newPaid;
                                    $bill->status = $newStatus;
                                    $bill->save();
                                }
                            }
                        }
                    }
                }
            }, 5);
        } catch (\Throwable $e) {
            Log::error("[syncStudentBillsFromPaidTransactions] Error for student {$studentId}: " . $e->getMessage());
        }
    }

    public static function resolveStudentRateForBillType($studentId, $billTypeId, $month, $year, $preloadedRates = null)
    {
        $student = is_object($studentId) ? $studentId : Student::with(['user', 'classroom'])->find($studentId);
        if (!$student) return 0;

        $billType = is_object($billTypeId) ? $billTypeId : \App\Models\BillType::with('billItem')->find($billTypeId);
        if (!$billType) return 0;

        if ($preloadedRates === null) {
            $preloadedRates = self::getCachedPreloadedRates();
        }

        $ratesForBt = $preloadedRates->where('bill_type_id', $billType->id);

        $transferRate = $ratesForBt->first(function ($r) use ($student) {
            return $r->type === \App\Models\PaymentRate::TYPE_TRANSFER &&
                   $r->paymentRateStudents->contains('student_id', $student->id);
        });
        if ($transferRate) {
            $item = $transferRate->paymentRateItems->first(fn($i) => $i->month == $month && $i->year == $year);
            if ($item) return (int) $item->amount;
            if ($transferRate->amount > 0) return (int) ($transferRate->amount / 12);
        }

        if ($student->classroom_id) {
            $regularRates = $ratesForBt->filter(function ($r) use ($student) {
                return $r->type === \App\Models\PaymentRate::TYPE_REGULAR &&
                       $r->paymentRateClassrooms->whereNull('deleted_at')->contains('classroom_id', $student->classroom_id);
            });

            foreach ($regularRates as $rate) {
                if (!empty($rate->gender)) {
                    $genders = array_map('trim', explode(',', $rate->gender));
                    if (!in_array($student->gender, $genders)) continue;
                }
                if (!empty($rate->jamaah_status)) {
                    $statuses = array_map('trim', explode(',', $rate->jamaah_status));
                    $studentStatus = $student->user?->jamaah_status ?? 'NON_JAMAAH';
                    if (!in_array($studentStatus, $statuses)) continue;
                }
                
                if (!empty($rate->alumni_status)) {
                    if (!isset($isAlumni)) {
                        $isAlumni = false;
                        if ($student->classroom && $student->classroom->school && str_contains(strtoupper($student->classroom->school->name), 'MA')) {
                            $hasSmpHistory = \App\Models\StudentClassroomHistory::where('student_id', $student->id)
                                ->whereHas('classroom.school', function($q) {
                                    $q->where('name', 'like', '%SMP%');
                                })->exists();
                            $isAlumni = $hasSmpHistory;
                        }
                    }

                    $statuses = array_map('trim', explode(',', $rate->alumni_status));
                    $studentAlumniStatus = $isAlumni ? 'ALUMNI_SMP_MA' : 'NON_ALUMNI';
                    if (!in_array($studentAlumniStatus, $statuses)) continue;
                }

                if (!empty($rate->student_sub_status_id)) {
                    if ($student->student_sub_status_id !== $rate->student_sub_status_id) {
                        continue;
                    }
                }

                $item = $rate->paymentRateItems->first(fn($i) => $i->month == $month && $i->year == $year);
                if ($item) return (int) $item->amount;
                if ($rate->amount > 0) return (int) ($rate->amount / 12);
            }
        }

        // If rates exist for this bill type, but none match the student's classroom/id, return 0 (rate belum di-generate ke kelas siswa)
        return 0;
    }

    public static function resolveStudentRateItemForBillType($studentId, $billTypeId, $month, $year, $preloadedRates = null)
    {
        $student = is_object($studentId) ? $studentId : Student::with(['user', 'classroom'])->find($studentId);
        if (!$student) return null;

        $billType = is_object($billTypeId) ? $billTypeId : \App\Models\BillType::with('billItem')->find($billTypeId);
        if (!$billType) return null;

        if ($preloadedRates === null) {
            $preloadedRates = self::getCachedPreloadedRates();
        }

        $ratesForBt = $preloadedRates->where('bill_type_id', $billType->id);

        $transferRate = $ratesForBt->first(function ($r) use ($student) {
            return $r->type === \App\Models\PaymentRate::TYPE_TRANSFER &&
                   $r->paymentRateStudents->contains('student_id', $student->id);
        });
        if ($transferRate) {
            $item = $transferRate->paymentRateItems->first(fn($i) => $i->month == $month && $i->year == $year);
            if ($item) return $item;
        }

        if ($student->classroom_id) {
            $regularRates = $ratesForBt->filter(function ($r) use ($student) {
                return $r->type === \App\Models\PaymentRate::TYPE_REGULAR &&
                       $r->paymentRateClassrooms->whereNull('deleted_at')->contains('classroom_id', $student->classroom_id);
            });

            foreach ($regularRates as $rate) {
                if (!empty($rate->gender)) {
                    $genders = array_map('trim', explode(',', $rate->gender));
                    if (!in_array($student->gender, $genders)) continue;
                }
                if (!empty($rate->jamaah_status)) {
                    $statuses = array_map('trim', explode(',', $rate->jamaah_status));
                    $studentStatus = $student->user?->jamaah_status ?? 'NON_JAMAAH';
                    if (!in_array($studentStatus, $statuses)) continue;
                }
                
                if (!empty($rate->alumni_status)) {
                    if (!isset($isAlumni)) {
                        $isAlumni = false;
                        if ($student->classroom && $student->classroom->school && str_contains(strtoupper($student->classroom->school->name), 'MA')) {
                            $hasSmpHistory = \App\Models\StudentClassroomHistory::where('student_id', $student->id)
                                ->whereHas('classroom.school', function($q) {
                                    $q->where('name', 'like', '%SMP%');
                                })->exists();
                            $isAlumni = $hasSmpHistory;
                        }
                    }

                    $statuses = array_map('trim', explode(',', $rate->alumni_status));
                    $studentAlumniStatus = $isAlumni ? 'ALUMNI_SMP_MA' : 'NON_ALUMNI';
                    if (!in_array($studentAlumniStatus, $statuses)) continue;
                }

                if (!empty($rate->student_sub_status_id)) {
                    if ($student->student_sub_status_id !== $rate->student_sub_status_id) {
                        continue;
                    }
                }

                $item = $rate->paymentRateItems->first(fn($i) => $i->month == $month && $i->year == $year);
                if ($item) return $item;
            }
        }

        return null;
    }

    public static function hasActiveRateForStudent($studentInput, $billTypeInput, $preloadedRates = null): bool
    {
        $student = is_object($studentInput) ? $studentInput : Student::with(['user', 'classroom'])->find($studentInput);
        if (!$student) return false;

        $billType = is_object($billTypeInput) ? $billTypeInput : \App\Models\BillType::find($billTypeInput);
        if (!$billType) return false;

        if ($preloadedRates === null) {
            $preloadedRates = self::getCachedPreloadedRates();
        }

        $ratesForBt = $preloadedRates->where('bill_type_id', $billType->id);
        if ($ratesForBt->isEmpty()) return false;

        // 1. Check Transfer Rate
        $hasTransfer = $ratesForBt->contains(function ($r) use ($student) {
            return $r->type === \App\Models\PaymentRate::TYPE_TRANSFER &&
                   $r->paymentRateStudents->whereNull('deleted_at')->contains('student_id', $student->id);
        });
        if ($hasTransfer) return true;

        // 2. Check Regular Rate with matching classroom
        if (!$student->classroom_id) return false;

        return $ratesForBt->contains(function ($r) use ($student) {
            if ($r->type !== \App\Models\PaymentRate::TYPE_REGULAR) return false;

            // Pastikan PaymentRateClassroom aktif (non-deleted) dan sesuai kelas siswa saat ini
            $classMatch = $r->paymentRateClassrooms
                ->whereNull('deleted_at')
                ->contains('classroom_id', $student->classroom_id);
            if (!$classMatch) return false;

            if (!empty($r->gender)) {
                $genders = array_map('trim', explode(',', $r->gender));
                if (!in_array($student->gender, $genders)) return false;
            }

            if (!empty($r->jamaah_status)) {
                $statuses = array_map('trim', explode(',', $r->jamaah_status));
                $studentStatus = $student->user?->jamaah_status ?? 'NON_JAMAAH';
                if (!in_array($studentStatus, $statuses)) return false;
            }

            if (!empty($r->alumni_status)) {
                if (!isset($isAlumni)) {
                    $isAlumni = false;
                    if ($student->classroom && $student->classroom->school && str_contains(strtoupper($student->classroom->school->name), 'MA')) {
                        $hasSmpHistory = \App\Models\StudentClassroomHistory::where('student_id', $student->id)
                            ->whereHas('classroom.school', function($q) {
                                $q->where('name', 'like', '%SMP%');
                            })->exists();
                        $isAlumni = $hasSmpHistory;
                    }
                }

                $statuses = array_map('trim', explode(',', $rate->alumni_status));
                $studentAlumniStatus = $isAlumni ? 'ALUMNI_SMP_MA' : 'NON_ALUMNI';
                if (!in_array($studentAlumniStatus, $statuses)) return false;
            }

            if (!empty($r->student_sub_status_id)) {
                if ($student->student_sub_status_id !== $r->student_sub_status_id) {
                    return false;
                }
            }


            return true;
        });
    }

    public static function ensureStudentBillsSyncedFromRate($studentId, $academicYearId = null)
    {
        if (empty($studentId)) return;
        $student = Student::with(['user', 'classroom.school'])->find($studentId);
        if (!$student) return;

        $studentSchoolName = $student->classroom?->school?->name ?? '';
        $entryYear = $student->getEntryYear();

        $billTypesQuery = \App\Models\BillType::with(['billItem', 'academicYear'])->whereNull('deleted_at');
        if ($academicYearId) {
            $billTypesQuery->where('academic_year_id', $academicYearId);
        }
        $billTypes = $billTypesQuery->get();

        // Eager load all existing bills and rates into memory
        $existingBills = Bill::where('student_id', $student->id)
            ->whereNull('deleted_at')
            ->get()
            ->keyBy(fn($b) => "{$b->bill_type_id}_{$b->month}_{$b->year}");

        $preloadedRates = self::getCachedPreloadedRates();

        $now = now();
        $newBillsToInsert = [];

        foreach ($billTypes as $bt) {
            // Isolasi UPT: Jangan generate bill dari Pos/UPT lain (misal DEMO/MA/Pondok untuk siswa SMP)
            if (!self::isBillTypeMatchingStudentSchoolUnit($bt, $studentSchoolName)) {
                continue;
            }

            // Isolasi Tahun Masuk: Jangan generate bill untuk Tahun Ajaran sebelum siswa masuk
            if ($bt->academicYear) {
                $ayStart = $bt->academicYear->getStartYearSafe();
                if ($ayStart !== null && $ayStart < $entryYear) {
                    continue;
                }
            }

            // Pre-check ALL-OR-NOTHING: Hanya generate jika siswa memiliki active rate mapping.
            // Menghindari partial amount=0 akibat resolusi rate gagal di tengah proses.
            if (!self::hasActiveRateForStudent($student, $bt, $preloadedRates)) {
                continue;
            }

            if ($bt->type === 'MONTHLY') {
                $months = array_merge(range(7, 12), range(1, 6));
                $startYear = $bt->academicYear?->start_year ?? date('Y');
                $endYear = $bt->academicYear?->end_year ?? ($startYear + 1);

                foreach ($months as $m) {
                    $y = ($m >= 7) ? $startYear : $endYear;
                    $key = "{$bt->id}_{$m}_{$y}";

                    $existingBill = $existingBills->get($key);
                    $expectedItem = self::resolveStudentRateItemForBillType($student, $bt, $m, $y, $preloadedRates);
                    $expectedAmount = $expectedItem ? (int) $expectedItem->amount : self::resolveStudentRateForBillType($student, $bt, $m, $y, $preloadedRates);
                    $expectedItemId = $expectedItem?->id;

                    if (!$existingBill) {
                        $newBillsToInsert[] = [
                            'id' => \Illuminate\Support\Str::uuid()->toString(),
                            'bill_type_id' => $bt->id,
                            'student_id' => $student->id,
                            'classroom_id' => $student->classroom_id,
                            'academic_year_id' => $bt->academic_year_id,
                            'month' => $m,
                            'year' => $y,
                            'amount' => $expectedAmount,
                            'paid_amount' => 0,
                            'status' => Bill::STATUS_UNPAID,
                            'payment_rate_item_id' => $expectedItemId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    } elseif ($existingBill->status === Bill::STATUS_UNPAID && $existingBill->paid_amount == 0 && ($existingBill->amount != $expectedAmount || $existingBill->payment_rate_item_id != $expectedItemId)) {
                        $existingBill->update([
                            'amount' => $expectedAmount,
                            'payment_rate_item_id' => $expectedItemId,
                        ]);
                    }
                }
            }
        }

        if (!empty($newBillsToInsert)) {
            DB::transaction(function () use ($newBillsToInsert) {
                foreach (array_chunk($newBillsToInsert, 100) as $chunk) {
                    Bill::insert($chunk);
                }
            }, 5);
        }
    }

    public static function isBillTypeMatchingStudentSchoolUnit($billTypeInput, $studentSchoolName)
    {
        $bName = '';
        $posName = '';

        if (is_object($billTypeInput)) {
            $bName = strtoupper($billTypeInput->name ?? '');
            $posName = strtoupper($billTypeInput->billItem?->name ?? '');
        } else {
            $bName = strtoupper($billTypeInput ?? '');
        }

        $sName = strtoupper($studentSchoolName ?? '');

        // VALIDASI VIA DATABASE RELATION (Single Source of Truth):
        // Jika BillType ini memiliki PaymentRate yang sudah di-mapping ke kelas tertentu,
        // maka HANYA izinkan siswa yang kelasnya ada di mapping tersebut.
        if (is_object($billTypeInput) && $billTypeInput->id) {
            $billTypeId = $billTypeInput->id;
            $preloadedRates = self::getCachedPreloadedRates();
            $ratesForBt = $preloadedRates->where('bill_type_id', $billTypeId);

            // Hanya berlaku jika ada REGULAR rates yang sudah di-mapping ke kelas
            $regularRates = $ratesForBt->where('type', \App\Models\PaymentRate::TYPE_REGULAR);
            if ($regularRates->isNotEmpty()) {
                $mappedClassroomIds = $regularRates->flatMap(function ($r) {
                    return $r->paymentRateClassrooms->whereNull('deleted_at')->pluck('classroom_id');
                })->unique();

                // Jika ada mapping kelas, cek classroom siswa via studentSchoolName parameter
                // Note: studentSchoolName adalah nama sekolah, bukan classroom_id
                // Jadi validasi ini hanya menambah layer keamanan, tidak mengganti string matching
                if ($mappedClassroomIds->isNotEmpty()) {
                    // Ambil school_ids dari classroom yang di-mapping
                    $mappedSchoolIds = \App\Models\Classroom::whereIn('id', $mappedClassroomIds)
                        ->pluck('school_id')
                        ->unique();

                    if ($mappedSchoolIds->isNotEmpty()) {
                        // Cek apakah nama sekolah siswa cocok dengan salah satu sekolah yang di-mapping
                        $mappedSchoolNames = \App\Models\School::whereIn('id', $mappedSchoolIds)->pluck('name');
                        $schoolMatch = $mappedSchoolNames->contains(function ($name) use ($sName) {
                            return strtoupper($name) === $sName;
                        });

                        if (!$schoolMatch && !empty($sName)) {
                            return false;
                        }
                    }
                }
            }
        }

        $hasSmp = str_contains($bName, 'SMP') || str_contains($posName, 'SMP');
        $hasMa = str_contains($bName, 'MA') || str_contains($bName, 'ALIYAH') || str_contains($posName, 'MA') || str_contains($posName, 'ALIYAH');
        $hasPondok = str_contains($bName, 'PONDOK') || str_contains($bName, 'PPTQ') || str_contains($bName, 'SYAHRIAH PONDOK') || str_contains($posName, 'PONDOK') || str_contains($posName, 'PPTQ');
        $hasDemo = str_contains($bName, 'DEMO') || str_contains($bName, 'REGISTRASI') || str_contains($bName, 'NEW SPP') || str_contains($posName, 'DEMO');

        $isStudentSmp = str_contains($sName, 'SMP');
        $isStudentMa = str_contains($sName, 'MA') || str_contains($sName, 'ALIYAH');
        $isStudentPondok = str_contains($sName, 'PONDOK') || str_contains($sName, 'PPTQ');
        $isStudentDemo = str_contains($sName, 'DEMO');

        // Isolasi Ketat UPT DEMO (Registrasi / New SPP / Pos Demo): Hanya untuk siswa UPT SEKOLAH DEMO
        if ($hasDemo || $posName === 'DEMO') {
            return $isStudentDemo;
        }

        // Isolasi Ketat: Syahriah/Tagihan Pondok KHUSUS untuk Lembaga/UPT PPTQ CAHAYA TASBIH (PONDOK)
        if ($hasPondok) {
            return $isStudentPondok;
        }

        // Isolasi Ketat: Tagihan SMP khusus UPT SMP, Tagihan MA khusus UPT MA
        if ($hasSmp) {
            return $isStudentSmp;
        }
        if ($hasMa) {
            return $isStudentMa;
        }

        return true;
    }

    public static function isClass12MA($student): bool
    {
        if (!$student) return false;
        $classroom = $student->classroom ?? ($student->relationLoaded('classroom') ? $student->classroom : null);
        if (!$classroom && isset($student->classroom_id)) {
            $classroom = \App\Models\Classroom::with('school')->find($student->classroom_id);
        }
        if (!$classroom) return false;

        $clsName = strtoupper($classroom->name ?? '');
        $schName = strtoupper($classroom->school->name ?? '');

        $isClass12 = (str_contains($clsName, '12') || str_contains($clsName, 'XII'));
        $isMA = (str_contains($schName, 'MA') || str_contains($schName, 'MADRASAH ALIYAH'));

        return $isClass12 && $isMA;
    }

    /**
     * @deprecated Tidak lagi digunakan. Filtering jenis tagihan kelas 12 MA
     * sekarang ditangani oleh guard SST (hasActiveRateForStudent) yang lebih robust.
     * Pendekatan whitelist hardcoded menyebabkan tagihan sah (mis. ZARKASI)
     * tidak muncul di UI meskipun sudah di-generate oleh admin.
     * Dipertahankan untuk backward compatibility — jangan panggil dari kode baru.
     */
    public static function isAllowedBillTypeForClass12MA(string $billTypeName): bool
    {
        $upper = strtoupper($billTypeName);
        return str_contains($upper, 'ZARKASI')
            || str_contains($upper, 'SYAHR') 
            || str_contains($upper, 'APLIKASI') 
            || str_contains($upper, 'LKS');
    }

    public static function isBillBeforeAcademicYear2026($academicYear): bool
    {
        if (!$academicYear) return false;
        $startYear = method_exists($academicYear, 'getStartYearSafe') 
            ? $academicYear->getStartYearSafe() 
            : null;
        
        if ($startYear === null && isset($academicYear->name)) {
            $parts = explode('/', $academicYear->name);
            if (count($parts) > 0 && is_numeric($parts[0])) {
                $startYear = (int)$parts[0];
            }
        }

        return $startYear !== null && $startYear < 2026;
    }

    public static function cleanupGhostBillsForStudent($studentId)
    {
        if (empty($studentId)) return;
        $student = Student::with(['classroom.school'])->find($studentId);
        if (!$student) return;

        $studentSchoolName = $student->classroom?->school?->name ?? '';
        $entryYear = $student->getEntryYear();

        $unpaidBills = Bill::with(['billType.billItem', 'billType.academicYear', 'academicYear'])
            ->where('student_id', $studentId)
            ->where('status', Bill::STATUS_UNPAID)
            ->where('paid_amount', 0)
            ->whereNull('deleted_at')
            ->get();

        $idsToDelete = [];
        $billTypeIdsToDeleteFully = [];
        foreach ($unpaidBills as $bill) {
            $shouldDelete = false;

            // 1. Cross-UPT Check
            $bType = $bill->billType;
            if (!self::isBillTypeMatchingStudentSchoolUnit($bType ?? $bill->billType?->name, $studentSchoolName)) {
                $shouldDelete = true;
            }

            // 2. Academic Year Filter Check (Before Entry Year)
            $ay = $bill->billType?->academicYear ?? $bill->academicYear;
            if (!$shouldDelete && $ay) {
                $ayStartYear = $ay->getStartYearSafe();
                if ($ayStartYear !== null && $ayStartYear < $entryYear) {
                    $shouldDelete = true;
                }
            }

            // 3. Single Source of Truth Rate Check: Jika Admin belum me-mapping kelas/siswa pada Payment Rate untuk BillType ini, hapus tagihan ghost
            if (!$shouldDelete && $bType) {
                if (!self::hasActiveRateForStudent($student, $bType)) {
                    $shouldDelete = true;
                }
            }

            if ($shouldDelete) {
                if ($bType && $bType->type === 'MONTHLY') {
                    $billTypeIdsToDeleteFully[] = $bill->bill_type_id;
                }
                $idsToDelete[] = $bill->id;
            }
        }

        // Prevent partial delete: Jika satu bulan dari tagihan MONTHLY harus dihapus,
        // hapus semua bulan yang belum dibayar untuk bill type tersebut.
        if (!empty($billTypeIdsToDeleteFully)) {
            $additionalBills = $unpaidBills->whereIn('bill_type_id', $billTypeIdsToDeleteFully)->pluck('id')->toArray();
            $idsToDelete = array_unique(array_merge($idsToDelete, $additionalBills));
        }

        if (!empty($idsToDelete)) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($idsToDelete) {
                Bill::whereIn('id', $idsToDelete)->update(['deleted_at' => now(), 'updated_at' => now()]);
            }, 5);
            Log::info("[GhostCleanup] Soft-deleted " . count($idsToDelete) . " UNPAID ghost bills for Student ID: {$studentId} (EntryYear: {$entryYear}, School: {$studentSchoolName})");
        }
    }

    /**
     * Generate 3-digit unique payment code (101-999) guaranteed not to collide
     * with any active PENDING_PAYMENT transaction of the same total amount.
     */
    public static function generateUniqueDigitsForAmount($baseAmount): int
    {
        $attempts = 0;
        do {
            $unique = rand(101, 999);
            $targetPayAmount = $baseAmount + $unique;

            $exists = Transaction::where('status', Transaction::STATUS_PENDING_PAYMENT)
                ->where('pay_amount', $targetPayAmount)
                ->whereNull('deleted_at')
                ->exists();

            $attempts++;
        } while ($exists && $attempts < 100);

        return $unique;
    }
}

