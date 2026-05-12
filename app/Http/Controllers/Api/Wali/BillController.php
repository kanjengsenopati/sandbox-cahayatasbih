<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\Bill;
use App\Models\BillType;
use Illuminate\Http\Request;

class BillController extends BaseWaliApiController
{
    public function index()
    {
        $student = $this->resolveActiveStudent();
        if (!$student) return response()->json(['unpaid' => [], 'paid' => []]);
        
        $groupedBills = Bill::with(['billType.billItem', 'billType.academicYear'])
            ->where('student_id', $student->id)
            ->get()
            ->groupBy('bill_type_id')
            ->map(function ($items) {
                $first = $items->first();
                return [
                    'bill_type_id' => $first->bill_type_id,
                    'bill_type_name' => $first->billType->name ?? 'Tagihan',
                    'academic_year' => $first->billType->academicYear->name ?? '-',
                    'total' => $items->sum('amount'),
                    'paid' => $items->where('status', 'PAID')->sum('amount'),
                    'unpaid' => $items->where('status', 'UNPAID')->sum('amount'),
                    'items_count' => $items->count(),
                    'unpaid_count' => $items->where('status', 'UNPAID')->count(),
                ];
            });

        return response()->json([
            'unpaid' => $groupedBills->filter(fn($g) => $g['unpaid'] > 0)->values(),
            'paid' => $groupedBills->filter(fn($g) => $g['unpaid'] == 0)->values(),
        ]);
    }

    public function show($id)
    {
        $student = $this->resolveActiveStudent();
        if (!$student) return response()->json(['error' => 'Student not found'], 404);
        
        $billType = BillType::with(['billItem', 'academicYear'])->findOrFail($id);
        
        $bills = Bill::where('student_id', $student->id)
            ->where('bill_type_id', $id)
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json([
            'billType' => $billType,
            'bills' => $bills,
            'summary' => [
                'total' => $bills->sum('amount'),
                'paid' => $bills->where('status', 'PAID')->sum('amount'),
                'unpaid' => $bills->where('status', 'UNPAID')->sum('amount'),
            ]
        ]);
    }
}
