<?php

namespace App\Http\Controllers\Api\Wali;

use Illuminate\Http\Request;

class LimitController extends BaseWaliApiController
{
    public function show()
    {
        $student = $this->resolveActiveStudent();
        if (!$student) return response()->json(['message' => 'Student not found'], 404);
        
        return response()->json([
            'daily_limit' => $student->daily_limit,
            'student_name' => $student->name
        ]);
    }

    public function update(Request $request)
    {
        $request->validate(['daily_limit' => 'required|numeric|min:0']);
        $student = $this->resolveActiveStudent();
        if (!$student) return response()->json(['message' => 'Student not found'], 404);
        
        $student->update(['daily_limit' => $request->daily_limit]);
        
        return response()->json([
            'message' => 'Daily limit updated successfully',
            'daily_limit' => $student->daily_limit
        ]);
    }
}
