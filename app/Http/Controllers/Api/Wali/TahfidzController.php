<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\Tahfidz;
use Illuminate\Http\Request;

class TahfidzController extends BaseWaliApiController
{
    public function index()
    {
        $student = $this->resolveActiveStudent();
        if (!$student) return response()->json(['message' => 'Student not found'], 404);
        
        $tahfidzs = Tahfidz::where('student_id', $student->id)
            ->latest()
            ->get();
            
        return response()->json([
            'student_name' => $student->name,
            'total_pages' => (int) Tahfidz::where('student_id', $student->id)->sum('number_of_pages'),
            'data' => $tahfidzs
        ]);
    }
}
