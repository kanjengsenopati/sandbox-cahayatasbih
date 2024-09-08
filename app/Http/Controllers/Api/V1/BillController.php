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

        // Get all unpaid bills for the student
        $unpaidBills = Bill::where('student_id', $studentId)
            ->where('status', Bill::STATUS_UNPAID)
            ->select('bill_type_id', 'amount')
            ->get();
        // $totalBill = $unpaidBills->sum('amount');

        // Get bill types with unpaid bills
        $billTypeIds = $unpaidBills->pluck('bill_type_id')->unique();

        $billTypes = BillType::whereIn('id', $billTypeIds)
            ->with(['billItem', 'academicYear'])
            ->latest()
            ->get();

        // Get all bills for the student
        $allBills = Bill::where('student_id', $studentId)
            ->select('bill_type_id', 'status', 'amount')
            ->get();

        $billTypes = $billTypes->map(function ($billType) use ($allBills) {
            $typeBills = $allBills->where('bill_type_id', $billType->id);
            $billType->total_unpaid = $typeBills->where('status', Bill::STATUS_UNPAID)->sum('amount');
            $billType->total_paid = $typeBills->where('status', Bill::STATUS_PAID)->sum('amount');
            $billType->status = $typeBills->contains('status', Bill::STATUS_UNPAID) ? Bill::STATUS_UNPAID : Bill::STATUS_PAID;
            return $billType;
        });

        // count total bill from all bill types unpaid
        $totalBill = $billTypes->sum('total_unpaid');

        $data = [
            'total_bill' => $totalBill,
            'bill_types' => $billTypes,
        ];

        return $this->postSuccessResponse("Berhasil mengambil data", $data);
    }

    public function show(Request $request, $id)
    {
        $billType = BillType::with(['billItem', 'academicYear', 'bills' => function ($query) use ($request) {
            $query->where('student_id', $request->student_id)
                ->orderBy('year', 'asc')
                ->orderByRaw("FIELD(month, " . implode(",", array_keys(Bill::$monthOrder)) . ")");
        }])->find($id);


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

        // Get all bills for the student
        $allBills = Bill::where('student_id', $studentId)
            ->select('bill_type_id', 'status', 'amount')
            ->get();

        // ambil bill type id yang belum lunas
        $unpaidBillTypeIds = $allBills->where('status', Bill::STATUS_UNPAID)->pluck('bill_type_id')->unique();

        // loop semua bill type yang belum lunas dan hitung total unpaid dan jadikan $totalBill
        $billTypes = BillType::whereIn('id', $unpaidBillTypeIds)
            ->with(['billItem', 'academicYear'])
            ->latest()
            ->get();
        $totalBill = 0;
        foreach ($billTypes as $billType) {
            $typeBills = $allBills->where('bill_type_id', $billType->id);
            $billType->total_unpaid = $typeBills->where('status', Bill::STATUS_UNPAID)->sum('amount');
            $totalBill += $billType->total_unpaid;
        }

        // Group bills by bill_type_id
        $groupedBills = $allBills->groupBy('bill_type_id');

        // Filter to get only fully paid bill types
        $fullyPaidBillTypeIds = $groupedBills->filter(function ($typeBills) {
            return $typeBills->every(function ($bill) {
                return $bill->status === Bill::STATUS_PAID;
            });
        })->keys();

        $billTypes = BillType::whereIn('id', $fullyPaidBillTypeIds)
            ->with(['billItem', 'academicYear'])
            ->latest()
            ->get();

        $billTypes = $billTypes->map(function ($billType) use ($groupedBills) {
            $typeBills = $groupedBills[$billType->id];
            $billType->total_paid = $typeBills->sum('amount');
            $billType->total_unpaid = 0; // Always 0 for fully paid bill types
            $billType->status = Bill::STATUS_PAID; // Always PAID for fully paid bill types
            return $billType;
        })->values(); // Reset array keys

        $data = [
            'total_bill' => $totalBill,
            'bill_types' => $billTypes,
        ];

        return $this->postSuccessResponse("Berhasil mengambil data", $data);
    }

    // public function history(Request $request)
    // {
    //     $studentId = $request->student_id;

    //     $totalBill = Bill::where('student_id', $studentId)
    //         ->where('status', Bill::STATUS_PAID)
    //         ->sum('amount');

    //     $billTypes = BillType::whereHas('bills', function ($query) use ($studentId) {
    //         $query->where('student_id', $studentId);
    //         $query->where('status', Bill::STATUS_PAID);
    //     })
    //         ->whereDoesntHave('bills', function ($query) use ($studentId) {
    //             $query->where('student_id', $studentId);
    //             $query->where('status', '!=', Bill::STATUS_PAID);
    //         })
    //         ->with(['billItem', 'academicYear'])
    //         ->latest()
    //         ->get()
    //         ->map(function ($billType) use ($request) {
    //             $billType->total_unpaid = $billType->bills->where('status', Bill::STATUS_UNPAID)
    //                 ->where('student_id', $request->student_id)->sum('amount');
    //             $billType->total_paid = $billType->bills->where('status', Bill::STATUS_PAID)
    //                 ->where('student_id', $request->student_id)->sum('amount');
    //             $billType->status = $billType->bills->where('status', Bill::STATUS_UNPAID)
    //                 ->where('student_id', $request->student_id)->count() > 0 ? Bill::STATUS_UNPAID : Bill::STATUS_PAID;
    //             return $billType;
    //         });

    //     $data = [
    //         'total_bill' => $totalBill,
    //         'bill_types' => $billTypes,
    //     ];

    //     return $this->postSuccessResponse("Berhasil mengambil data", $data);
    // }
}
