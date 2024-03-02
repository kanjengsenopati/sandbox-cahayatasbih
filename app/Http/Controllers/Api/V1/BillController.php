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
        $billTypes = BillType::whereHas('bills', function ($query) use ($request) {
            $query->where('student_id', $request->student_id);
        })->with('billItem', 'academicYear')->latest()->get();

        return $this->postSuccessResponse("Berhasil mengambil data", $billTypes);
    }

    public function show(Request $request, $id)
    {
        $billType = BillType::with(['billItem', 'academicYear', 'bills' => function ($query) use ($request) {
            $query->where('student_id', $request->student_id);
            $query->orderBy('month', 'asc');
        },])->find($id);

        if (!$billType) {
            return $this->failedResponse("Data tidak ditemukan");
        }

        return $this->postSuccessResponse("Berhasil mengambil data", $billType);
    }

    public function history(Request $request)
    {
        $billTypes = BillType::whereHas('bills', function ($query) use ($request) {
            $query->where('student_id', $request->student_id)
                ->where('status', Bill::STATUS_PAID);
        })
            ->with(['bills' => function ($query) use ($request) {
                $query->where('student_id', $request->student_id)
                    ->where('status', Bill::STATUS_PAID);
            }, 'billItem', 'academicYear'])
            ->latest()
            ->get();

        return $this->getSuccessResponse($billTypes);
    }
}
