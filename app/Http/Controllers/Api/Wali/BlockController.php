<?php

namespace App\Http\Controllers\Api\Wali;

use Illuminate\Http\Request;

class BlockController extends BaseWaliApiController
{
    public function show()
    {
        $student = $this->resolveActiveStudent();
        if (!$student) return response()->json(['message' => 'Student not found'], 404);
        
        return response()->json([
            'is_blocked' => (bool) $student->is_blocked,
            'student_name' => $student->name
        ]);
    }

    public function toggle(Request $request)
    {
        $student = $this->resolveActiveStudent();
        if (!$student) return response()->json(['message' => 'Student not found'], 404);
        
        $student->is_blocked = !$student->is_blocked;
        $student->save();
        
        return response()->json([
            'message' => $student->is_blocked ? 'Kartu santri berhasil diblokir' : 'Kartu santri berhasil diaktifkan',
            'is_blocked' => (bool) $student->is_blocked
        ]);
    }
}
