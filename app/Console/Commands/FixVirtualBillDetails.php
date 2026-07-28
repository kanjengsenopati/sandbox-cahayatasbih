<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Services\TransactionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixVirtualBillDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:fix-virtual-details {--student_id= : Optional student ID filter}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Repair transaction details with virtual bill IDs (generated_ / auto_) and sync bill status.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting repair of virtual bill details and data tagihan sync...');

        $query = Transaction::with(['transactionDetails', 'student'])
            ->where('type', Transaction::TYPE_BILL);

        if ($studentId = $this->option('student_id')) {
            $query->where('student_id', $studentId);
        }

        $transactions = $query->get();
        $fixedCount = 0;

        foreach ($transactions as $tx) {
            $isPaidTx = in_array($tx->status, [Transaction::STATUS_PAID, 'approved', 'SUCCESS']);

            foreach ($tx->transactionDetails as $detail) {
                $billId = $detail->bill_id;
                if (empty($billId)) continue;

                $isVirtual = str_starts_with($billId, 'generated_') || str_starts_with($billId, 'auto_');
                $billExists = Bill::where('id', $billId)->exists();

                if ($isVirtual || !$billExists) {
                    DB::transaction(function () use ($tx, $detail, $billId, $isPaidTx, &$fixedCount) {
                        $realBillId = TransactionService::ensureBillRecord($tx->student_id, $billId);

                        if ($realBillId && $realBillId !== $billId) {
                            $detail->update(['bill_id' => $realBillId]);
                            $bill = Bill::find($realBillId);

                            if ($bill && $isPaidTx) {
                                $detailAmount = intval($detail->amount ?? 0);
                                $paidVal = $detailAmount > 0 ? $detailAmount : ($bill->amount > 0 ? $bill->amount : 10000);
                                $bill->paid_amount = min($bill->amount, $bill->paid_amount + $paidVal);
                                if ($bill->paid_amount >= $bill->amount) {
                                    $bill->status = Bill::STATUS_PAID;
                                }
                                $bill->save();
                            }

                            $fixedCount++;
                            $this->line("Fixed Tx #{$tx->id} (Student: {$tx->student?->name}): '{$billId}' -> Real Bill UUID '{$realBillId}'");
                        }
                    });
                }
            }
        }

        $this->info("Completed! Total transaction details repaired: {$fixedCount}");
        return 0;
    }
}
