<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\StudentPermit;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
            'attachment_photo' => 'nullable|string',
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

        // Process base64 attachment photo if uploaded by Wali
        $attachmentPath = null;
        if ($request->filled('attachment_photo')) {
            $attachmentPath = $this->saveBase64Image($request->input('attachment_photo'), 'attachment_' . $studentId);
        }

        // Create permit
        $permit = StudentPermit::create([
            'student_id' => $studentId,
            'user_id' => $userId,
            'permit_type' => $request->input('permit_type'),
            'reason' => $request->input('reason'),
            'planned_exit_date' => Carbon::parse($request->input('planned_exit_date')),
            'planned_return_date' => Carbon::parse($request->input('planned_return_date')),
            'attachment_photo' => $attachmentPath,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perizinan berhasil diajukan, menunggu persetujuan Ustadz.',
            'permit' => $permit
        ]);
    }

    /**
     * Helper to save base64 photo stream.
     */
    private function saveBase64Image($base64String, $filename)
    {
        try {
            if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
                $base64String = substr($base64String, strpos($base64String, ',') + 1);
                $type = strtolower($type[1]); // png, jpg, jpeg
                
                if (!in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                    throw new \Exception('invalid image type');
                }
                
                $data = base64_decode($base64String);
                
                if ($data === false) {
                    throw new \Exception('base64_decode failed');
                }
            } else {
                throw new \Exception('did not match data URI with image data');
            }

            $fileNameToSave = 'images/permits/' . $filename . '_' . time() . '.' . $type;
            
            Storage::disk('public')->put($fileNameToSave, $data);
            
            return 'storage/' . $fileNameToSave;
        } catch (\Exception $e) {
            return null;
        }
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

    /**
     * Report return of a student by the parent (Wali Santri).
     */
    public function reportReturn(Request $request, $id)
    {
        $request->validate([
            'return_photo_santri' => 'required|string', // Base64 image
            'return_photo_escort' => 'required|string', // Base64 image
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
        ]);

        $userId = Auth::guard('wali')->id();

        $permit = StudentPermit::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$permit) {
            return response()->json([
                'success' => false,
                'message' => 'Data perizinan tidak ditemukan.'
            ], 404);
        }

        if (!in_array($permit->status, ['approved', 'out'])) {
            return response()->json([
                'success' => false,
                'message' => 'Pelaporan kembali hanya dapat dilakukan untuk perizinan berstatus DISETUJUI atau KELUAR.'
            ], 422);
        }

        // Save base64 photos
        $photoSantriPath = $this->saveBase64Image($request->input('return_photo_santri'), 'return_santri_' . $permit->id);
        $photoEscortPath = $this->saveBase64Image($request->input('return_photo_escort'), 'return_escort_' . $permit->id);

        if (!$photoSantriPath || !$photoEscortPath) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah foto pendukung kembali.'
            ], 422);
        }

        $permit->update([
            'status' => 'pending_return',
            'actual_return_date' => Carbon::now(),
            'return_photo_santri' => $photoSantriPath,
            'return_photo_escort' => $photoEscortPath,
            'return_latitude' => $request->input('latitude'),
            'return_longitude' => $request->input('longitude'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan kepulangan berhasil dikirim. Menunggu persetujuan Ustadz.',
            'permit' => $permit
        ]);
    }
}
