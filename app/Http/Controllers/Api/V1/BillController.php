<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BillType;
use Illuminate\Http\Request;

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
}
