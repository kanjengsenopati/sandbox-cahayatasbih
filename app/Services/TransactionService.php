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
                        // Generate unique payment 3 digits starting from 111 to 299
                        $uniquePayment = rand(111, 299);
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
                    $student->decrement('saldo', $transaction->unique_payment);
                    Log::info("Rollback Kode Unik: Mengurangi saldo siswa {$student->name} ({$student->id}) sebesar Rp.{$transaction->unique_payment} akibat pembatalan transaksi.");
                    \App\Models\SaldoHistory::where('student_id', $student->id)
                        ->where('amount', (int) $transaction->unique_payment)
                        ->where('description', 'like', '%Kode Unik%')
                        ->forceDelete();
                }

                if ($transaction->type == Transaction::TYPE_SALDO) {
                    $student = Student::find($transaction->student_id);
                    $transactionDetail = $transaction->transactionDetails->first();
                    if ($transactionDetail && $transactionDetail->saldoHistory) {
                        $amountToSub = $transactionDetail->saldoHistory->amount;
                        $student->decrement('saldo', $amountToSub);
                    }
                } elseif ($transaction->type == Transaction::TYPE_SAVING) {
                    $student = Student::find($transaction->student_id);
                    $transactionDetail = $transaction->transactionDetails->first();
                    if ($transactionDetail && $transactionDetail->savingHistory) {
                        $amountToSub = $transactionDetail->savingHistory->amount;
                        $student->decrement('saving', $amountToSub);
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
                    }
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

                    if ($deletedCount > 0) {
                        // Only decrement saldo if the rollback block above didn't already handle it
                        // (i.e., when the old status was NOT PAID, meaning the PAID→non-PAID block didn't fire)
                        if ($oldStatus !== Transaction::STATUS_PAID) {
                            $student->decrement('saldo', $transaction->unique_payment * $deletedCount);
                            Log::info("REJECTED Safety Net: Mengurangi saldo siswa {$student->name} ({$student->id}) sebesar Rp." . ($transaction->unique_payment * $deletedCount) . " (kode unik orphan).");
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

            if ($isZarkasi) {
                $m = (int)$month;
                if ($m >= 7 && $m <= 11) {
                    $amount = 100000;
                } elseif ($m == 12) {
                    $amount = 50000;
                } else {
                    $amount = 0;
                }
            } elseif ($isAplikasi) {
                $amount = 10000;
            } elseif ($isSyahriah) {
                $amount = 500000;
            } else {
                $amount = $sampleBill ? $sampleBill->amount : ($billType->billItem->amount ?? 0);
                if ($amount <= 0) {
                    $amount = \App\Models\Bill::where('bill_type_id', $billTypeId)->where('amount', '>', 0)->value('amount') ?? 0;
                }
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
            $paidTransactions = Transaction::with(['transactionDetails', 'student'])
                ->where('student_id', $studentId)
                ->where('type', Transaction::TYPE_BILL)
                ->whereIn('status', [Transaction::STATUS_PAID, 'paid', 'PAID', 'approved', 'APPROVED', 'SUCCESS', 'success', 'LUNAS', 'lunas'])
                ->get();

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

                    if ($bill) {
                        $detailAmount = intval($detail->amount ?? 0);
                        $paidVal = $detailAmount > 0 ? $detailAmount : ($bill->amount > 0 ? $bill->amount : 10000);
                        if ($bill->paid_amount < $bill->amount) {
                            $bill->paid_amount = min($bill->amount, $bill->paid_amount + $paidVal);
                            if ($bill->paid_amount >= $bill->amount) {
                                $bill->status = Bill::STATUS_PAID;
                            }
                            $bill->save();
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error("[syncStudentBillsFromPaidTransactions] Error for student {$studentId}: " . $e->getMessage());
        }
    }
}
