<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Bill;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\UnitTransferConfig;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class UnitTransferController extends Controller
{
    public function continueUnit(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'config_id' => 'required|exists:unit_transfer_configs,id'
        ]);

        DB::beginTransaction();

        try {
            $student = Student::findOrFail($request->student_id);
            $config = UnitTransferConfig::with('billType.billItem')->findOrFail($request->config_id);

            // Check if the bill already exists
            $existingBill = Bill::where('student_id', $student->id)
                ->where('bill_type_id', $config->bill_type_id)
                ->first();

            if ($existingBill) {
                return $this->postSuccessResponse('Tagihan sudah dibuat', [
                    'bill' => $existingBill
                ]);
            }

            // Generate Bill
            $bill = Bill::create([
                'student_id' => $student->id,
                'bill_type_id' => $config->bill_type_id,
                'classroom_id' => $student->classroom_id, // At the time of creation, still in old classroom
                'academic_year_id' => $config->billType->academic_year_id,
                'month' => Carbon::now()->month,
                'year' => Carbon::now()->year,
                'amount' => $config->billType->billItem->amount ?? 0,
                'status' => Bill::STATUS_UNPAID,
            ]);

            DB::commit();

            return $this->postSuccessResponse('Berhasil Membuat Tagihan Mutasi Unit', [
                'bill' => $bill
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->failedResponse("Gagal membuat tagihan: " . $th->getMessage(), [], 500);
        }
    }
}
