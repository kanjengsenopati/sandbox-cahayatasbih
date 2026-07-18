<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Memperbaiki tagihan UNPAID yang memiliki:
     * 1. payment_rate_item_id NULL padahal seharusnya terhubung ke PaymentRateItem
     * 2. amount = 0 padahal PaymentRateItem memiliki nominal > 0
     * 3. payment_rate_item_id mismatch (terhubung ke rate item yang salah)
     */
    public function up(): void
    {
        $fixedCount = 0;

        // Ambil semua PaymentRate yang memiliki jamaah_status filter
        $paymentRates = DB::table('payment_rates')
            ->whereNotNull('jamaah_status')
            ->get();

        foreach ($paymentRates as $paymentRate) {
            // Ambil semua payment_rate_items untuk rate ini
            $rateItems = DB::table('payment_rate_items')
                ->where('payment_rate_id', $paymentRate->id)
                ->get();

            if ($rateItems->isEmpty()) {
                continue;
            }

            // Ambil classroom_ids dari payment_rate_classrooms
            $classroomIds = DB::table('payment_rate_classrooms')
                ->where('payment_rate_id', $paymentRate->id)
                ->pluck('classroom_id')
                ->toArray();

            if (empty($classroomIds)) {
                continue;
            }

            // Ambil jamaah_statuses yang diperbolehkan
            $jamaahStatuses = array_map('trim', explode(',', $paymentRate->jamaah_status));

            // Ambil student IDs yang cocok dengan filter jamaah_status
            $studentsQuery = DB::table('students')
                ->whereIn('classroom_id', $classroomIds)
                ->where('status', 'ACTIVE');

            // Build student IDs berdasarkan jamaah_status
            $matchingStudentIds = $studentsQuery
                ->where(function ($q) use ($jamaahStatuses) {
                    // Students with matching user jamaah_status
                    $q->whereIn('user_id', function ($subQ) use ($jamaahStatuses) {
                        $subQ->select('id')
                            ->from('users')
                            ->whereIn('jamaah_status', $jamaahStatuses);
                    });

                    // For NON_JAMAAH, also include students without user or with null jamaah_status
                    if (in_array('NON_JAMAAH', $jamaahStatuses)) {
                        $q->orWhereNull('user_id')
                          ->orWhereNotIn('user_id', function ($subQ) {
                              $subQ->select('id')->from('users');
                          })
                          ->orWhereIn('user_id', function ($subQ) {
                              $subQ->select('id')
                                  ->from('users')
                                  ->whereNull('jamaah_status');
                          });
                    }
                })
                ->pluck('id')
                ->toArray();

            if (empty($matchingStudentIds)) {
                continue;
            }

            // Untuk setiap rate item, cari dan perbaiki bill UNPAID yang bermasalah
            foreach ($rateItems as $rateItem) {
                // Cari bill UNPAID untuk student yang cocok, dengan month/year sesuai rate item
                $billsToFix = DB::table('bills')
                    ->where('bill_type_id', $paymentRate->bill_type_id)
                    ->whereIn('student_id', $matchingStudentIds)
                    ->where('month', $rateItem->month)
                    ->where('year', $rateItem->year)
                    ->where('status', 'UNPAID')
                    ->where(function ($q) use ($rateItem) {
                        // Bill yang perlu diperbaiki:
                        // 1. payment_rate_item_id NULL
                        $q->whereNull('payment_rate_item_id')
                          // 2. payment_rate_item_id salah (bukan milik rate ini)
                          ->orWhere('payment_rate_item_id', '!=', $rateItem->id)
                          // 3. amount tidak sesuai
                          ->orWhere('amount', '!=', $rateItem->amount);
                    })
                    ->whereNull('deleted_at')
                    ->get();

                foreach ($billsToFix as $bill) {
                    // Pastikan bill ini memang BUKAN milik rate item lain yang sudah benar
                    // Cek apakah ada rate item lain yang lebih cocok
                    $correctRateItem = $this->findCorrectRateItem($bill, $paymentRates, $jamaahStatuses);
                    
                    if ($correctRateItem && $correctRateItem->id === $rateItem->id) {
                        DB::table('bills')
                            ->where('id', $bill->id)
                            ->update([
                                'amount' => $rateItem->amount,
                                'payment_rate_item_id' => $rateItem->id,
                                'updated_at' => now(),
                            ]);
                        $fixedCount++;
                    }
                }
            }
        }

        Log::info("Migration fix_jamaah_registration_bills: Fixed {$fixedCount} bill records.");
    }

    /**
     * Cari rate item yang paling cocok untuk bill berdasarkan student jamaah_status
     */
    private function findCorrectRateItem($bill, $allPaymentRates, $currentJamaahStatuses)
    {
        // Ambil jamaah_status dari user student ini
        $studentJamaahStatus = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->where('students.id', $bill->student_id)
            ->value('users.jamaah_status');

        // Jika null, anggap NON_JAMAAH
        if (!$studentJamaahStatus) {
            $studentJamaahStatus = 'NON_JAMAAH';
        }

        // Cari payment rate yang jamaah_status-nya cocok dengan student
        foreach ($allPaymentRates as $rate) {
            if ($rate->bill_type_id !== $bill->bill_type_id) {
                continue;
            }

            $rateStatuses = array_map('trim', explode(',', $rate->jamaah_status ?? ''));
            if (in_array($studentJamaahStatus, $rateStatuses)) {
                // Cari rate item yang month/year-nya cocok
                $rateItem = DB::table('payment_rate_items')
                    ->where('payment_rate_id', $rate->id)
                    ->where('month', $bill->month)
                    ->where('year', $bill->year)
                    ->first();

                if ($rateItem) {
                    return $rateItem;
                }
            }
        }

        return null;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data repair migration - tidak perlu rollback
    }
};
