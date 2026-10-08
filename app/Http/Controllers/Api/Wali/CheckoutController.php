<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\Bill;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CheckoutController extends BaseWaliApiController
{
    public function store(Request $request)
    {
        $student = $this->resolveActiveStudent();
        if (!$student) return response()->json(['message' => 'Student not found'], 404);

        // Merge student_id into request for validation and TransactionService
        if (!$request->has('student_id')) {
            $request->merge(['student_id' => $student->id]);
        }

        try {
            $request->validate([
                'bill_ids' => 'required|array',
                'bill_ids.*' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        if (str_starts_with($value, 'generated_') || str_starts_with($value, 'auto_')) {
                            return;
                        }
                        if (!\App\Models\Bill::where('id', $value)->exists()) {
                            $fail("Tagihan dengan ID {$value} tidak ditemukan.");
                        }
                    }
                ],
                'payment_method_id' => 'required|exists:payment_methods,id',
                'student_id' => 'required|exists:students,id'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Checkout Validation Failed', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            throw $e;
        }

        $lockKey = "checkout_bill_student_" . $student->id;
        $lock = Cache::lock($lockKey, 10);

        if (!$lock->get()) {
            return response()->json(['message' => 'Sistem sedang memproses transaksi Anda. Harap tunggu sebentar.'], 409);
        }

        try {
            // Bersihkan transaksi kadaluwarsa (> 2 jam tanpa bukti) agar tagihan bisa diproses kembali
            Transaction::where('student_id', $student->id)
                ->where('type', Transaction::TYPE_BILL)
                ->whereIn('status', [Transaction::STATUS_PENDING, Transaction::STATUS_PENDING_PAYMENT])
                ->whereDoesntHave('activeProof')
                ->where(function($q) {
                    $q->where('created_at', '<=', Carbon::now()->subHours(2))
                      ->orWhere(function($sub) {
                          $sub->whereNotNull('expiry_time')->where('expiry_time', '<=', Carbon::now());
                      });
                })
                ->update(['status' => Transaction::STATUS_CANCELLED]);

            // Cek apakah item tagihan yang dipilih sudah berada dalam transaksi aktif (menunggu bukti atau verifikasi)
            $billIds = $request->bill_ids ?? [];
            $realBillIds = [];
            foreach ($billIds as $bId) {
                if (str_starts_with($bId, 'generated_') || str_starts_with($bId, 'auto_')) {
                    $parts = explode('_', $bId);
                    if (count($parts) >= 5) {
                        $btId = $parts[1];
                        $m = $parts[3];
                        $y = $parts[4];
                        $existing = \App\Models\Bill::where('student_id', $student->id)
                            ->where('bill_type_id', $btId)
                            ->where('month', $m)
                            ->where('year', $y)
                            ->first();
                        if ($existing) {
                            $realBillIds[] = $existing->id;
                        }
                    }
                } else {
                    $realBillIds[] = $bId;
                }
            }

            if (!empty($realBillIds)) {
                $lockedDetail = TransactionDetail::whereIn('bill_id', $realBillIds)
                    ->whereNull('deleted_at')
                    ->whereHas('transaction', function ($q) {
                        $q->whereNull('deleted_at')
                          ->whereIn('status', [
                              Transaction::STATUS_PENDING,
                              Transaction::STATUS_PENDING_PAYMENT,
                              Transaction::STATUS_PENDING_CONFIRMATION,
                          ]);
                    })
                    ->with(['bill.billType', 'transaction'])
                    ->first();

                if ($lockedDetail) {
                    $billName = $lockedDetail->bill?->billType?->name ?? 'Tagihan';
                    $isWaitingVerif = ($lockedDetail->transaction?->status === Transaction::STATUS_PENDING_CONFIRMATION);
                    $statusText = $isWaitingVerif 
                        ? 'sedang menunggu verifikasi bendahara' 
                        : 'sedang menunggu unggah bukti bayar';
                    return response()->json([
                        'message' => "Tagihan '{$billName}' {$statusText}. Harap selesaikan pembayaran sebelumnya atau batalkan terlebih dahulu.",
                        'pending_transaction_id' => $lockedDetail->transaction_id
                    ], 422);
                }
            }

            $paymentMethod = \App\Models\PaymentMethod::findOrFail($request->payment_method_id);
            
            if ($paymentMethod->type === \App\Models\PaymentMethod::TYPE_BALANCE) {
                if (!$student->isPwaSaldoPaymentAllowed()) {
                    $appSetting = \App\Models\ApplicationSetting::first();
                    $msg = !empty($appSetting->pwa_saldo_payment_disabled_message) 
                        ? $appSetting->pwa_saldo_payment_disabled_message 
                        : 'Pembayaran tagihan menggunakan Saldo di PWA Wali Santri sedang dinonaktifkan untuk rombel / jenjang ini.';
                    return response()->json([
                        'status' => false,
                        'message' => $msg
                    ], 403);
                }
            }
            
            // createTransaction menggunakan DB::transaction internal — menjaga ACID tanpa nested lock
            $transaction = \App\Services\TransactionService::createTransaction($request, $paymentMethod->type, Transaction::TYPE_BILL);
            
            if ($transaction instanceof \Illuminate\Http\JsonResponse) {
                return $transaction;
            }

            return response()->json([
                'message' => 'Checkout successful',
                'transaction' => $transaction,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Checkout Error: ' . $e->getMessage(), [
                'student_id' => $student->id,
                'exception' => $e
            ]);
            return response()->json([
                'message' => 'Gagal memproses transaksi. Silakan coba lagi.',
                'error' => $e->getMessage()
            ], 500);
        } finally {
            optional($lock)->release();
        }
    }
}
