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
            DB::beginTransaction();
            $paymentMethod = \App\Models\PaymentMethod::findOrFail($request->payment_method_id);
            
            if ($paymentMethod->type === \App\Models\PaymentMethod::TYPE_BALANCE) {
                if (!$student->isPwaSaldoPaymentAllowed()) {
                    DB::rollBack();
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
            
            $transaction = \App\Services\TransactionService::createTransaction($request, $paymentMethod->type, Transaction::TYPE_BILL);
            
            if ($transaction instanceof \Illuminate\Http\JsonResponse) {
                DB::rollBack();
                return $transaction;
            }

            // Check if transaction detail was already created (e.g. if paid via saldo)
            $existingDetailsCount = TransactionDetail::where('transaction_id', $transaction->id)->count();
            
            if ($existingDetailsCount == 0) {
                $customAmounts = $request->custom_amounts ?? [];
                foreach ($request->bill_ids as $billId) {
                    $customAmount = isset($customAmounts[$billId]) ? intval($customAmounts[$billId]) : null;
                    TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'bill_id' => $billId,
                        'amount' => $customAmount,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Checkout successful',
                'transaction' => $transaction,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Checkout Error: ' . $e->getMessage(), [
                'student_id' => $student->id,
                'exception' => $e
            ]);
            return response()->json([
                'message' => 'Gagal memproses transaksi. Silakan coba lagi.',
                'error' => $e->getMessage()
            ], 500);
        } finally {
            $lock->release();
        }
    }
}
