<?php

namespace App\Jobs;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use App\Models\TransactionProof;
use App\Models\ApplicationSetting;
use Illuminate\Support\Facades\Log;
use App\Services\GeminiVisionService;
use App\Services\TransactionService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessPaymentProofJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $proof;

    /**
     * Create a new job instance.
     */
    public function __construct(TransactionProof $proof)
    {
        $this->proof = $proof;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $appSetting = ApplicationSetting::first();
        if (!$appSetting || !$appSetting->payment_auto_check) {
            return; // Auto checking is disabled
        }

        $proof = $this->proof;
        $transaction = $proof->transaction;

        if (!$transaction) {
            Log::error('ProcessPaymentProofJob: Transaction not found for proof ' . $proof->id);
            return;
        }

        try {
            // Extract nominal using Gemini
            $extractedAmount = GeminiVisionService::extractNominalFromImage($proof->proof_image);

            if ($extractedAmount === null) {
                // OCR Failed to read
                $proof->update([
                    'ocr_status' => 'failed',
                    'ocr_amount' => null,
                ]);
                return;
            }

            // OCR Success
            $proof->update([
                'ocr_status' => 'processed',
                'ocr_amount' => $extractedAmount,
            ]);

            $expectedAmount = (float) $transaction->pay_amount;

            // Prepare request data for TransactionService
            $requestData = [];

            // Compare amounts (allowing small tolerance e.g., rounding)
            if (abs($extractedAmount - $expectedAmount) <= 10) {
                // MATCH -> PAID
                $requestData = [
                    'status' => Transaction::STATUS_PAID,
                ];
            } else {
                // MISMATCH -> REJECTED
                $requestData = [
                    'status' => Transaction::STATUS_REJECTED,
                    'note' => "Nominal transfer (Rp " . number_format($extractedAmount, 0, ',', '.') . ") tidak sesuai dengan tagihan (Rp " . number_format($expectedAmount, 0, ',', '.') . "). Harap cek ulang.",
                ];
            }

            // Mock the request for the service
            request()->merge($requestData);

            // Using TransactionService to properly handle side-effects (bills, saldos, notifications)
            $result = TransactionService::updateStatusPaymentTransfer($requestData, $transaction);

            if (!$result['status']) {
                Log::error('ProcessPaymentProofJob: Failed to update transaction status.', ['result' => $result]);
            }

        } catch (\Exception $e) {
            Log::error('ProcessPaymentProofJob Exception: ' . $e->getMessage());
            $proof->update([
                'ocr_status' => 'failed'
            ]);
        }
    }
}
