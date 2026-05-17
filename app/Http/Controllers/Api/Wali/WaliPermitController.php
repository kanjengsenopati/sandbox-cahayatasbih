<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\StudentPermit;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WaliPermitController extends Controller
{
    /**
     * Display a listing of the permits requested by the parent.
     */
    public function index()
    {
        $userId = Auth::guard('wali')->id();
        
        $permits = StudentPermit::where('user_id', $userId)
            ->with(['student' => function ($query) {
                $query->select('id', 'name', 'nis', 'avatar');
            }])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'permits' => $permits
        ]);
    }

    /**
     * Store a newly created leave permit in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'permit_type' => 'required|in:keluar_pondok,pulang_sementara,sakit',
            'reason' => 'required|string|min:5',
            'planned_exit_date' => 'required|date|after_or_equal:today',
            'planned_return_date' => 'required|date|after:planned_exit_date',
        ]);

        $userId = Auth::guard('wali')->id();
        $studentId = $request->input('student_id');

        // Check if student belongs to this parent
        $student = Student::where('id', $studentId)->where('user_id', $userId)->first();
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Santri tidak terdaftar pada akun Anda.'
            ], 403);
        }

        // Rule: Limit 1 active/pending leave request in the system
        $activePermitExists = StudentPermit::where('student_id', $studentId)
            ->whereIn('status', ['pending', 'approved', 'out'])
            ->exists();

        if ($activePermitExists) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengajukan: Santri masih memiliki perizinan aktif atau sedang menunggu persetujuan.'
            ], 422);
        }

        // Create permit
        $permit = StudentPermit::create([
            'student_id' => $studentId,
            'user_id' => $userId,
            'permit_type' => $request->input('permit_type'),
            'reason' => $request->input('reason'),
            'planned_exit_date' => Carbon::parse($request->input('planned_exit_date')),
            'planned_return_date' => Carbon::parse($request->input('planned_return_date')),
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perizinan berhasil diajukan, menunggu persetujuan Ustadz.',
            'permit' => $permit
        ]);
    }

    /**
     * Display the specified permit detail.
     */
    public function show($id)
    {
        $userId = Auth::guard('wali')->id();
        
        $permit = StudentPermit::where('id', $id)
            ->where('user_id', $userId)
            ->with(['student' => function ($query) {
                $query->select('id', 'name', 'nis', 'avatar');
            }, 'admin' => function ($query) {
                $query->select('id', 'name');
            }])
            ->first();

        if (!$permit) {
            return response()->json([
                'success' => false,
                'message' => 'Detail perizinan tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'permit' => $permit
        ]);
    }
}
