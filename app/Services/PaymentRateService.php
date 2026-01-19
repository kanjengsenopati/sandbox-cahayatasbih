<?php

namespace App\Services;

use App\Models\BillType;
use App\Models\Classroom;
use App\Models\PaymentRate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Admin\PaymentRateRequest;

class PaymentRateService
{
    public function storePaymentRate(PaymentRateRequest $request)
    {
        try {
            DB::beginTransaction();

            $billType = BillType::findOrFail($request->bill_type_id);

            $paymentRate = $billType->paymentRates()->create([
                'amount' => $request->price,
            ]);

            $this->createPaymentRateClassrooms($paymentRate, $request->classrooms);
            $this->createPaymentRateItems($paymentRate, $billType, $request);
            $this->createBillsForStudents($paymentRate, $billType, $request);

            DB::commit();

            return ['status' => 'success', 'message' => 'Tarif pembayaran berhasil ditambahkan'];
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            return ['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
        }
    }

    private function createPaymentRateClassrooms(PaymentRate $paymentRate, array $classrooms)
    {
        foreach ($classrooms as $classroom) {
            $paymentRate->paymentRateClassrooms()->create([
                'classroom_id' => $classroom,
            ]);
        }
    }

    private function createPaymentRateItems(PaymentRate $paymentRate, BillType $billType, PaymentRateRequest $request)
    {
        if ($billType->type == BillType::TYPE_MONTHLY) {
            for ($month = 1; $month <= 12; $month++) {
                $paymentRate->paymentRateItems()->create([
                    'month' => $month,
                    'year' => $request->{"tahun_$month"},
                    'amount' => $request->{"bulan_$month"},
                ]);
            }
        } else {
            foreach ($request->months as $month) {
                $paymentRate->paymentRateItems()->create([
                    'month' => $month,
                    'year' => $request->year,
                    'amount' => $request->price,
                ]);
            }
        }
    }

    public function createBillsForStudents($paymentRate, $billType, $data)
    {
        $classrooms = Classroom::whereIn('id', $data['classrooms'])->get();

        if ($billType->type == BillType::TYPE_MONTHLY) {
            for ($month = 1; $month <= 12; $month++) {
                $this->createMonthlyBills($classrooms, $paymentRate, $billType, $data, $month);
            }
        } else {
            foreach ($data['months'] as $month) {
                $this->createCustomBills($classrooms, $paymentRate, $billType, $data, $month);
            }
        }
    }

    private function createMonthlyBills($classrooms, $paymentRate, $billType, $data, $month)
    {
        foreach ($classrooms as $classroom) {
            foreach ($classroom->students as $student) {
                // Access array elements using the correct array syntax
                $billAmount = $data['bulan_' . $month];
                $billYear = $data['tahun_' . $month];

                $paymentRateItem = $paymentRate->paymentRateItems()
                    ->where('month', $month)
                    ->first();

                $existingBill = $student->bills()->where('bill_type_id', $billType->id)
                    ->where('month', $month)
                    ->where('year', $billYear)
                    ->first();

                if (!$existingBill) {
                    $student->bills()->create([
                        'bill_type_id' => $billType->id,
                        'classroom_id' => $classroom->id,
                        'academic_year_id' => $billType->academic_year_id,
                        'month' => $month,
                        'year' => $billYear,
                        'amount' => $billAmount,
                        'status' => 'UNPAID',
                        'payment_rate_item_id' => $paymentRateItem?->id ?? null,
                    ]);
                }
            }
        }
    }


    private function createCustomBills($classrooms, $paymentRate, $billType, $data)
    {
        // Check if paymentRateItems is not null or empty
        if ($paymentRate->paymentRateItems->isEmpty()) {
            return;
        }

        foreach ($classrooms as $classroom) {
            foreach ($classroom->students as $student) {
                // Loop through each payment rate item
                foreach ($paymentRate->paymentRateItems as $item) {
                    $billMonth = $item->month;
                    $billYear = $item->year;
                    $billAmount = $data['bulan_' . $billMonth] ?? 0; // Use default 0 if not set

                    // Check for existing bill
                    $existingBill = $student->bills()
                        ->where('bill_type_id', $billType->id)
                        ->where('month', $billMonth)
                        ->where('year', $billYear)
                        ->first();

                    if (!$existingBill) {
                        // Create a new bill record
                        $student->bills()->create([
                            'bill_type_id' => $billType->id,
                            'classroom_id' => $classroom->id,
                            'academic_year_id' => $billType->academic_year_id,
                            'month' => $billMonth,
                            'year' => $billYear,
                            'amount' => $billAmount,
                            'status' => 'UNPAID',
                            'payment_rate_item_id' => $item->id,
                        ]);
                    }
                }
            }
        }
    }
}
