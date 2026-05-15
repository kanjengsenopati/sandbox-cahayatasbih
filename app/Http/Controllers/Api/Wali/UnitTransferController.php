<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\Bill;
use App\Models\UnitTransferConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnitTransferController extends BaseWaliApiController
{
    /**
     * Generate tagihan daftar ulang untuk mutasi pindah unit.
     */
    public function continueUnit(Request $request)
    {
        $request->validate([
            'config_id' => 'required|exists:unit_transfer_configs,id'
        ]);

        $student = $this->resolveActiveStudent();
        if (!$student) {
            return response()->json(['message' => 'Santri tidak ditemukan'], 404);
        }

        $config = UnitTransferConfig::with('billType')->findOrFail($request->config_id);

        // Validate student is in the source school
        if ($student->school_id != $config->from_school_id) {
            return response()->json(['message' => 'Santri tidak memenuhi syarat untuk jalur ini'], 422);
        }

        DB::beginTransaction();
        try {
            // Check if the bill already exists (idempotent)
            $existingBill = Bill::where('student_id', $student->id)
                ->where('bill_type_id', $config->bill_type_id)
                ->where('status', Bill::STATUS_UNPAID)
                ->first();

            if ($existingBill) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Tagihan sudah dibuat sebelumnya',
                    'data' => ['bill' => $existingBill]
                ]);
            }

            // Get billing amount from config
            $amount = $config->amount ?? 0;
            if ($amount <= 0) {
                DB::rollBack();
                return response()->json(['message' => 'Jumlah tagihan belum dikonfigurasi'], 422);
            }

            // Create the bill
            $bill = Bill::create([
                'student_id' => $student->id,
                'bill_type_id' => $config->bill_type_id,
                'classroom_id' => $student->classroom_id, // Historical: at the time of creation
                'academic_year_id' => $config->billType->academic_year_id ?? null,
                'month' => Carbon::now()->month,
                'year' => Carbon::now()->year,
                'amount' => $amount,
                'status' => Bill::STATUS_UNPAID,
            ]);

            DB::commit();

            Log::info("[UnitTransfer] Bill #{$bill->id} created for Student #{$student->id} via Config #{$config->id}");

            return response()->json([
                'message' => 'Berhasil membuat tagihan pendaftaran lanjut unit',
                'data' => ['bill' => $bill]
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("[UnitTransfer] Error creating bill: " . $e->getMessage());
            return response()->json(['message' => 'Gagal membuat tagihan'], 500);
        }
    }
}
