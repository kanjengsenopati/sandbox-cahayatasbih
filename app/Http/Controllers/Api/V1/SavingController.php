<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\SavingHistory;
use App\Models\ApplicationSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use App\Http\Requests\Api\V1\TopupSavingRequest;

class SavingController extends Controller
{
    public function index(Request $request)
    {
        $studentId = $request->student_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $saving = Student::findOrFail($studentId);

        $historySavingQuery = SavingHistory::with('admin')
            ->where('student_id', $studentId);

        if ($startDate) {
            $historySavingQuery->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $historySavingQuery->whereDate('created_at', '<=', $endDate);
        }

        $historySaving = $historySavingQuery->latest()->paginate(15);

        return $this->getSuccessResponse([
            'saving' => $saving->saving,
            'history' => $historySaving
        ]);
    }

    public function topup(TopupSavingRequest $request)
    {
        DB::beginTransaction();

        try {
            $paymentMethod = PaymentMethod::find($request->payment_method_id);
            $paymentMethodType = $paymentMethod->type;

            $saldoPending = SavingHistory::where('student_id', $request->student_id)
                ->where('status', SavingHistory::STATUS_PENDING)
                ->where('type', SavingHistory::TYPE_IN)
                ->sum('amount');

            if ($saldoPending > 0) {
                return $this->failedResponse("Maaf Anda masih memiliki Tabungan yang belum dibayar sebesar Rp. " . number_format($saldoPending, 0, ',', '.'));
            }

            $saldoHistory = $this->createSavingHistory($request);

            $paymentCode = 'CHT-TAB-' . Str::random(3) . time();

            $transaction = $this->createTransaction($request, $paymentCode);

            $this->createTransactionDetail($transaction, $saldoHistory);

            TransactionService::createInvoice($transaction);

            if ($paymentMethodType == PaymentMethod::TYPE_XENDIT) {
                $response = $this->postSuccessResponse("Berhasil melakukan transaksi pembayaran", $transaction->payment_link);
            } else {
                $response = $this->postSuccessResponse('Berhasil Membayar Tagihan', ['transaction' => $transaction]);
            }

            DB::commit();

            return $response;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to topup saving: ' . $e->getMessage());
            return $this->failedResponse('Maaf, terjadi kesalahan saat memproses permintaan Anda.');
        }
    }

    private function createSavingHistory($request)
    {
        return SavingHistory::create([
            'student_id' => $request->student_id,
            'amount' => $request->amount,
            'type' => SavingHistory::TYPE_IN,
            'status' => SavingHistory::STATUS_PENDING,
            'description' => "Topup Tabungan sebesar Rp. " . number_format($request->amount, 0, ',', '.'),
        ]);
    }

    private function createTransaction($request, $paymentCode)
    {
        $expiryTimeInMinutes = ApplicationSetting::latest()->first()->getPaymentExpireTimeInMinutesAttribute();
        $transactionData = [
            'pay_amount' => $request->amount,
            'payment_code' => $paymentCode,
            'student_id' => $request->student_id,
            'expiry_time' => Carbon::now()->addMinutes($expiryTimeInMinutes),
            'status' => Transaction::STATUS_PENDING,
            'paid_at' => null,
            'type' => Transaction::TYPE_SAVING
        ];

        return Transaction::create(array_merge($transactionData, $request->validated()));
    }

    private function createTransactionDetail($transaction, $savingHistory)
    {
        $transaction->transactionDetails()->create([
            'saving_history_id' => $savingHistory->id
        ]);
    }
}
