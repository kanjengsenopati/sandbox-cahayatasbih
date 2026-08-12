<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\PaymentRate;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HealMissingRateItems extends Command
{
    protected $signature = 'bills:heal-missing-rate-items';
    protected $description = 'Menyembuhkan data tagihan lama yang kehilangan payment_rate_item_id';

    public function handle()
    {
        $this->info('Mencari tagihan dengan payment_rate_item_id yang kosong (NULL)...');

        $bills = Bill::whereNull('payment_rate_item_id')->with('student')->get();
        if ($bills->isEmpty()) {
            $this->info('Semua tagihan sudah memiliki payment_rate_item_id. Tidak ada yang perlu disembuhkan.');
            return 0;
        }

        $this->warn('Ditemukan ' . $bills->count() . ' tagihan yang membutuhkan penyembuhan relasi.');
        
        $healed = 0;
        $failed = 0;

        // Cache rates per bill_type_id to avoid N+1
        $ratesCache = [];

        DB::beginTransaction();
        try {
            foreach ($bills as $bill) {
                if (!$bill->student) {
                    $failed++;
                    continue;
                }

                if (!isset($ratesCache[$bill->bill_type_id])) {
                    $ratesCache[$bill->bill_type_id] = PaymentRate::with('paymentRateClassrooms', 'paymentRateStudents', 'paymentRateItems')
                        ->where('bill_type_id', $bill->bill_type_id)
                        ->get();
                }

                $matchingItem = null;
                $rates = $ratesCache[$bill->bill_type_id];

                foreach ($rates as $rate) {
                    $isStudentInRate = false;

                    if ($rate->type === PaymentRate::TYPE_REGULAR) {
                        $isStudentInRate = $rate->paymentRateClassrooms->contains('classroom_id', $bill->student->classroom_id);
                        // Additionally check gender
                        if ($isStudentInRate && $rate->gender) {
                            $isStudentInRate = in_array($bill->student->gender, explode(',', $rate->gender));
                        }
                    } else {
                        $isStudentInRate = $rate->paymentRateStudents->contains('student_id', $bill->student_id);
                    }

                    if ($isStudentInRate) {
                        // Find the matching item for the month/year
                        $item = $rate->paymentRateItems->where('month', $bill->month)->where('year', $bill->year)->first();
                        if ($item) {
                            $matchingItem = $item;
                            break;
                        }
                    }
                }

                if ($matchingItem) {
                    $bill->payment_rate_item_id = $matchingItem->id;
                    $bill->save();
                    $healed++;
                } else {
                    $failed++;
                }
            }

            DB::commit();
            $this->info("Penyembuhan Selesai. Berhasil: {$healed} | Gagal/Tidak Cocok: {$failed}");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
