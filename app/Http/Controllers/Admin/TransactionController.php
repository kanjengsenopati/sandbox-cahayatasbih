<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use App\Http\Requests\Admin\TransactionRequest;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TransactionRequest $request)
    {
        DB::beginTransaction();
        try {
            // Generate payment code
            $paymentCode = 'CHT-' . Str::random(3) . time();

            // Create transaction
            $transactionData = [
                'payment_code' => $paymentCode,
                'student_id' => $request->student_id,
                'expiry_time' => Carbon::now()->addDay(),
                'status' => $request->payment_method_id == PaymentMethod::CASH_PAYMENT ? Transaction::STATUS_PAID : Transaction::STATUS_PENDING,
                'paid_at' => $request->payment_method_id == PaymentMethod::CASH_PAYMENT ? $request->date : null,
            ];
            $transaction = Transaction::create(array_merge($transactionData, $request->validated()));

            // Create transaction detail
            $transaction->transactionDetails()->create([
                'bill_id' => $request->bill_id,
            ]);

            if ($request->payment_method_id == PaymentMethod::AUTO_PAYMENT) {
                // Create transaction detail for auto payment
                TransactionService::createInvoice($transaction);
            }

            // Change status to paid if cash payment in bill
            if ($transaction->status == Transaction::STATUS_PAID) {
                TransactionService::changeStatusToPaid($request->bill_id);
            }
            DB::commit();
            // if auto payment, open new tab to xendit
            return $this->postSuccessResponse("Berhasil melakukan transaksi pembayaran", $transaction->payment_link);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return $this->failedResponse("Gagal melakukan transaksi pembayaran");
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
