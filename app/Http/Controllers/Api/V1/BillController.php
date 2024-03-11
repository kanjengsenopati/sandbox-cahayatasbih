<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Bill;
use App\Models\BillType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $studentId = $request->student_id;

        $totalBill = Bill::where('student_id', $studentId)
            ->where('status', Bill::STATUS_UNPAID)
            ->sum('amount');

        $billTypes = BillType::whereHas('bills', function ($query) use ($studentId) {
            $query->where('student_id', $studentId);
        })
            ->with(['billItem', 'academicYear'])
            ->latest()
            ->get()
            ->map(function ($billType) use ($request) {
                $billType->total_unpaid = $billType->bills->where('status', Bill::STATUS_UNPAID)
                    ->where('student_id', $request->student_id)->sum('amount');
                $billType->total_paid = $billType->bills->where('status', Bill::STATUS_PAID)
                    ->where('student_id', $request->student_id)->sum('amount');
                $billType->status = $billType->bills->where('status', Bill::STATUS_UNPAID)
                    ->where('student_id', $request->student_id)->count() > 0 ? Bill::STATUS_UNPAID : Bill::STATUS_PAID;
                return $billType;
            });

        $data = [
            'total_bill' => $totalBill,
            'bill_types' => $billTypes,
        ];

        return $this->postSuccessResponse("Berhasil mengambil data", $data);
    }

    public function show(Request $request, $id)
    {
        $billType = BillType::with(['billItem', 'academicYear', 'bills' => function ($query) use ($request) {
            $query->where('student_id', $request->student_id);
            $query->orderBy('month', 'asc');
        },])->find($id);

        // add total unpaid and total paid
        $billType->total = $billType->bills->sum('amount');
        $billType->total_unpaid = $billType->bills->where('status', Bill::STATUS_UNPAID)->sum('amount');
        $billType->total_paid = $billType->bills->where('status', Bill::STATUS_PAID)->sum('amount');
        // add status
        $billType->status = $billType->bills->where('status', Bill::STATUS_UNPAID)->count() > 0 ? Bill::STATUS_UNPAID : Bill::STATUS_PAID;

        // add relation with bill.transactions
        $billType->bills->map(function ($bill) {
            $bill->transactions;
            return $bill;
        });
        if (!$billType) {
            return $this->failedResponse("Data tidak ditemukan");
        }

        return $this->postSuccessResponse("Berhasil mengambil data", $billType);
    }

    public function history(Request $request)
    {
        $studentId = $request->student_id;

        $totalBill = Bill::where('student_id', $studentId)
            ->where('status', Bill::STATUS_PAID)
            ->sum('amount');

        $billTypes = BillType::whereHas('bills', function ($query) use ($studentId) {
            $query->where('student_id', $studentId);
            $query->where('status', Bill::STATUS_PAID);
        })
            ->with(['billItem', 'academicYear'])
            ->latest()
            ->get()
            ->map(function ($billType) use ($request) {
                $billType->total_unpaid = $billType->bills->where('status', Bill::STATUS_UNPAID)
                    ->where('student_id', $request->student_id)->sum('amount');
                $billType->total_paid = $billType->bills->where('status', Bill::STATUS_PAID)
                    ->where('student_id', $request->student_id)->sum('amount');
                $billType->status = $billType->bills->where('status', Bill::STATUS_UNPAID)
                    ->where('student_id', $request->student_id)->count() > 0 ? Bill::STATUS_UNPAID : Bill::STATUS_PAID;
                return $billType;
            });

        $data = [
            'total_bill' => $totalBill,
            'bill_types' => $billTypes,
        ];

        return $this->postSuccessResponse("Berhasil mengambil data", $data);
    }
}
