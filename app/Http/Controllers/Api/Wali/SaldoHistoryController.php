<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\SaldoHistory;
use Illuminate\Http\Request;

class SaldoHistoryController extends BaseWaliApiController
{
    public function index(Request $request)
    {
        $student = $this->resolveActiveStudent();
        if (!$student) return response()->json(['data' => []]);
        
        $query = SaldoHistory::where('student_id', $student->id)
            ->latest();
            
        if ($request->filter == 'today') {
            $query->whereDate('created_at', now());
        } elseif ($request->filter == 'week') {
            $query->where('created_at', '>=', now()->startOfWeek());
        } elseif ($request->filter == 'month') {
            $query->where('created_at', '>=', now()->startOfMonth());
        } elseif ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }
        
        return response()->json($query->paginate(10));
    }
}
