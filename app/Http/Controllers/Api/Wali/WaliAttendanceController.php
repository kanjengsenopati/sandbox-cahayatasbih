<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WaliAttendanceController extends BaseWaliApiController
{
    public function index()
    {
        $student = $this->resolveActiveStudent();
        if (!$student) {
            return response()->json(['message' => 'Santri tidak ditemukan.'], 404);
        }

        $attendances = Attendance::where('presensiable_type', Student::class)
            ->where('presensiable_id', $student->id)
            ->orderBy('check_in', 'desc')
            ->get();

        // Hitung statistik sederhana
        $totalPresent = $attendances->where('status', 'present')->count();
        $totalLate = $attendances->where('status', 'late')->count();
        $totalPermit = $attendances->whereIn('status', ['permit', 'sick'])->count();
        $totalAbsent = $attendances->where('status', 'absent')->count();

        return response()->json([
            'student_name' => $student->name,
            'stats' => [
                'present' => $totalPresent,
                'late' => $totalLate,
                'permit' => $totalPermit,
                'absent' => $totalAbsent,
            ],
            'data' => $attendances
        ]);
    }
}
