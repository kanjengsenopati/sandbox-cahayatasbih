<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SavingHistory;
use App\Models\Student;
use Illuminate\Http\Request;

class SavingController extends Controller
{
    public function index(Request $request)
    {
        $studentId = $request->student_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $saving = Student::findOrFail($studentId);

        $historySavingQuery = SavingHistory::with('admin')
            ->where('student_id', $studentId);

        if ($startDate) {
            $historySavingQuery->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $historySavingQuery->whereDate('created_at', '<=', $endDate);
        }

        $historySaving = $historySavingQuery->latest()->paginate(15);

        return $this->getSuccessResponse([
            'saving' => $saving->saving,
            'history' => $historySaving
        ]);
    }
}
