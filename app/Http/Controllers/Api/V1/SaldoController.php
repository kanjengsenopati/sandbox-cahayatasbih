<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\SaldoHistory;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\ApplicationSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use App\Http\Requests\Api\V1\SettLimitRequest;
use App\Http\Requests\Api\V1\TopupSaldoRequest;

class SaldoController extends Controller
{
    public function index()
    {
        $studentId = request()->student_id;
        $startDate = request()->start_date;
        $endDate = request()->end_date;

        $student = Student::with('classroom', 'school')->findOrFail($studentId);

        $totalSpending = SaldoHistory::where('student_id', $studentId)
            ->where('type', SaldoHistory::TYPE_OUT)
            ->where('status', SaldoHistory::STATUS_SUCCESS);

        $saldoHistoryQuery = SaldoHistory::where('student_id', $studentId);

        if ($startDate && $endDate) {
            $totalSpending->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate);
            $saldoHistoryQuery->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate);
        }

        $totalSpending = $totalSpending->sum('amount');
        $saldoHistory = $saldoHistoryQuery->latest()->paginate(10);

        return $this->postSuccessResponse("Berhasil mengambil data", [
            'student' => $student,
            'total_spending' => $totalSpending,
            'saldo_history' => $saldoHistory
        ]);
    }

    public function show($id)
    {
        $saldoHistory = SaldoHistory::findOrFail($id);
        if ($saldoHistory->usage == SaldoHistory::USAGE_BILL) {
            $saldoHistory->load('transaction_details.bill.billType');
        } elseif ($saldoHistory->usage == SaldoHistory::USAGE_POS) {
            $saldoHistory->load(
                'pointOfSaleTransaction.pointOfSaleTransactionDetails.item',
                'cashier'
            );
        } elseif ($saldoHistory->usage == SaldoHistory::USAGE_TOPUP) {
            $saldoHistory->load('transaction_details.transaction');
        }
        return $this->getSuccessResponse($saldoHistory);
    }

    public function block(Request $request)
    {
        $student = Student::findOrFail($request->student_id);

        $isBlocked = $request->type == SaldoHistory::TYPE_UNBLOCKED ? false : true;
        $student->is_blocked = $isBlocked;
        $student->save();

        $blockStatus = $isBlocked ? "Memblokir" : "UnBlokir";

        return $this->postSuccessResponse("Berhasil $blockStatus Saldo Siswa", $student);
    }

    public function topup(TopupSaldoRequest $request)
    {
        DB::beginTransaction();

        try {
            $paymentMethod = PaymentMethod::find($request->payment_method_id);
            $paymentMethodType = $paymentMethod->type;

            $saldoPending = SaldoHistory::where('student_id', $request->student_id)
                ->where('status', SaldoHistory::STATUS_PENDING)
                ->where('type', SaldoHistory::TYPE_IN)
                ->sum('amount');

            // if ($saldoPending > 0) {
            //     return $this->failedResponse("Maaf Anda masih memiliki saldo yang belum dibayar sebesar Rp. " . number_format($saldoPending, 0, ',', '.'));
            // }

            $saldoHistory = $this->createSaldoHistory($request);

            $transaction = TransactionService::createTransaction($request, $paymentMethodType, Transaction::TYPE_SALDO);
            $this->createTransactionDetail($transaction, $saldoHistory);

            if ($paymentMethodType == PaymentMethod::TYPE_XENDIT) {
                TransactionService::createInvoice($transaction, $request);
                $response = $this->postSuccessResponse("Berhasil melakukan transaksi pembayaran", $transaction->payment_link);
            } else {
                $response = $this->postSuccessResponse('Berhasil Topup Saldo', ['transaction' => $transaction]);
            }

            DB::commit();

            return $response;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error occurred while processing topup: ' . $e->getMessage());
            return $this->failedResponse('An error occurred while processing your request.');
        }
    }

    private function createSaldoHistory(TopupSaldoRequest $request)
    {
        return SaldoHistory::create([
            'student_id' => $request->student_id,
            'amount' => $request->amount,
            'type' => SaldoHistory::TYPE_IN,
            'status' => SaldoHistory::STATUS_PENDING,
            'description' => "Topup Saldo sebesar Rp. " . number_format($request->amount, 0, ',', '.'),
            'usage' => SaldoHistory::USAGE_TOPUP
        ]);
    }

    private function createTransaction(TopupSaldoRequest $request, $paymentCode)
    {
        $appSetting = ApplicationSetting::latest()->first();
        $expiryTimeInMinutes = $appSetting->getPaymentExpireTimeInMinutesAttribute();
        $transactionData = [
            'pay_amount' => $request->amount,
            'payment_code' => $paymentCode,
            'student_id' => $request->student_id,
            'expiry_time' => Carbon::now()->addMinutes($expiryTimeInMinutes),
            'status' => Transaction::STATUS_PENDING,
            'paid_at' => null,
            'type' => Transaction::TYPE_SALDO,
        ];

        return Transaction::create(array_merge($transactionData, $request->validated()));
    }

    private function createTransactionDetail($transaction, $saldoHistory)
    {
        $transaction->transactionDetails()->create([
            'saldo_history_id' => $saldoHistory->id,
        ]);
    }

    public function settLimit(SettLimitRequest $request)
    {
        $student = Student::findOrFail($request->student_id);
        $student->daily_limit = $request->daily_limit;
        $student->save();

        return $this->postSuccessResponse("Berhasil mengubah limit saldo", $student);
    }
}
