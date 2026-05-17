<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\StudentPermit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AsatidzPermitController extends Controller
{
    /**
     * View pending permit requests.
     */
    public function pendingList()
    {
        $adminId = Auth::guard('web')->id();
        $permits = StudentPermit::where('status', 'pending')
            ->whereHas('student', function ($query) use ($adminId) {
                $query->where('asrama_host_id', $adminId);
            })
            ->with(['student' => function ($query) {
                $query->select('id', 'name', 'nis', 'avatar', 'asrama_name');
            }, 'user' => function ($query) {
                $query->select('id', 'name', 'phone');
            }])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'permits' => $permits
        ]);
    }

    /**
     * Approve or reject a permit request.
     */
    public function action($id, Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:255',
        ]);

        $permit = StudentPermit::findOrFail($id);
        
        if ($permit->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Status perizinan ini sudah diproses sebelumnya.'
            ], 422);
        }

        $adminId = Auth::guard('web')->id();
        $action = $request->input('action');

        if ($action === 'approve') {
            $permit->update([
                'status' => 'approved',
                'admin_id' => $adminId,
                'barcode_token' => 'CT-' . Str::upper(Str::random(12)),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Perizinan santri berhasil disetujui.',
                'permit' => $permit
            ]);
        } else {
            $permit->update([
                'status' => 'rejected',
                'admin_id' => $adminId,
                'rejection_reason' => $request->input('rejection_reason'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Perizinan santri berhasil ditolak.',
                'permit' => $permit
            ]);
        }
    }

    /**
     * View active leave permits (students currently outside or approved).
     */
    public function activeList()
    {
        $adminId = Auth::guard('web')->id();
        $permits = StudentPermit::whereIn('status', ['approved', 'out'])
            ->whereHas('student', function ($query) use ($adminId) {
                $query->where('asrama_host_id', $adminId);
            })
            ->with(['student' => function ($query) {
                $query->select('id', 'name', 'nis', 'avatar', 'asrama_name');
            }, 'user' => function ($query) {
                $query->select('id', 'name', 'phone');
            }, 'admin' => function ($query) {
                $query->select('id', 'name');
            }])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'permits' => $permits
        ]);
    }

    /**
     * View overdue leave permits (students currently outside past their planned return).
     */
    public function overdueList()
    {
        $now = Carbon::now();
        $adminId = Auth::guard('web')->id();
        $permits = StudentPermit::where('status', 'out')
            ->where('planned_return_date', '<', $now)
            ->whereHas('student', function ($query) use ($adminId) {
                $query->where('asrama_host_id', $adminId);
            })
            ->with(['student' => function ($query) {
                $query->select('id', 'name', 'nis', 'avatar', 'asrama_name');
            }, 'user' => function ($query) {
                $query->select('id', 'name', 'phone');
            }])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'permits' => $permits
        ]);
    }

    /**
     * Scan exit/entry barcode and verify checkout/checkin with photos and GPS.
     */
    public function scanBarcode(Request $request)
    {
        $request->validate([
            'barcode_token' => 'required|exists:student_permits,barcode_token',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'photo_santri' => 'required|string', // Base64 image
            'photo_escort' => 'required|string', // Base64 image
            'escort_name' => 'required_if:scan_type,exit|nullable|string|max:255',
            'escort_relation' => 'required_if:scan_type,exit|nullable|string|max:255',
        ]);

        $token = $request->input('barcode_token');
        $permit = StudentPermit::where('barcode_token', $token)
            ->with('student')
            ->firstOrFail();

        // 1. Process Check-Out (Gerbang Keluar)
        if ($permit->status === 'approved') {
            // Store photos
            $photoSantriPath = $this->saveBase64Image($request->input('photo_santri'), 'exit_santri_' . $permit->id);
            $photoEscortPath = $this->saveBase64Image($request->input('photo_escort'), 'exit_escort_' . $permit->id);

            $permit->update([
                'status' => 'out',
                'actual_exit_date' => Carbon::now(),
                'exit_photo_santri' => $photoSantriPath,
                'exit_photo_escort' => $photoEscortPath,
                'exit_escort_name' => $request->input('escort_name'),
                'exit_escort_relation' => $request->input('escort_relation'),
                'exit_latitude' => $request->input('latitude'),
                'exit_longitude' => $request->input('longitude'),
            ]);

            return response()->json([
                'success' => true,
                'type' => 'exit',
                'message' => 'Validasi berhasil! Santri ' . $permit->student->name . ' diizinkan KELUAR pondok.',
                'permit' => $permit
            ]);
        }

        // 2. Process Check-In (Gerbang Kembali)
        if ($permit->status === 'out') {
            // Store photos
            $photoSantriPath = $this->saveBase64Image($request->input('photo_santri'), 'return_santri_' . $permit->id);
            $photoEscortPath = $this->saveBase64Image($request->input('photo_escort'), 'return_escort_' . $permit->id);

            $permit->update([
                'status' => 'returned',
                'actual_return_date' => Carbon::now(),
                'return_photo_santri' => $photoSantriPath,
                'return_photo_escort' => $photoEscortPath,
                'return_latitude' => $request->input('latitude'),
                'return_longitude' => $request->input('longitude'),
            ]);

            return response()->json([
                'success' => true,
                'type' => 'return',
                'message' => 'Validasi berhasil! Santri ' . $permit->student->name . ' telah KEMBALI di pondok dengan selamat.',
                'permit' => $permit
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tiket perizinan ini berstatus ' . $permit->status . ' dan tidak dapat diverifikasi di gerbang.'
        ], 422);
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

    public function dashboardStats()
    {
        $admin = Auth::guard('web')->user();
        $adminId = $admin->id;

        // Count supervised students
        $totalStudents = \App\Models\Student::where('asrama_host_id', $adminId)->count();

        // Get asrama name from first student
        $asramaName = \App\Models\Student::where('asrama_host_id', $adminId)
            ->whereNotNull('asrama_name')
            ->value('asrama_name') ?: 'Asrama Asatidz';

        // Count permits
        $pendingCount = StudentPermit::where('status', 'pending')
            ->whereHas('student', function ($query) use ($adminId) {
                $query->where('asrama_host_id', $adminId);
            })->count();

        $activeCount = StudentPermit::whereIn('status', ['approved', 'out'])
            ->whereHas('student', function ($query) use ($adminId) {
                $query->where('asrama_host_id', $adminId);
            })->count();

        $overdueCount = StudentPermit::where('status', 'out')
            ->where('planned_return_date', '<', Carbon::now())
            ->whereHas('student', function ($query) use ($adminId) {
                $query->where('asrama_host_id', $adminId);
            })->count();

        return response()->json([
            'success' => true,
            'host_name' => $admin->name,
            'asrama_name' => $asramaName,
            'total_students' => $totalStudents,
            'counts' => [
                'pending' => $pendingCount,
                'active' => $activeCount,
                'overdue' => $overdueCount
            ]
        ]);
    }

    public function myStudents()
    {
        $adminId = Auth::guard('web')->id();
        $students = \App\Models\Student::where('asrama_host_id', $adminId)
            ->with(['classroom'])
            ->orderBy('name', 'asc')
            ->get();

        $now = Carbon::now();

        $result = $students->map(function ($student) use ($now) {
            // Get latest permit to check status
            $latestPermit = StudentPermit::where('student_id', $student->id)
                ->orderBy('created_at', 'desc')
                ->first();

            $statusText = 'Di Pondok';
            if ($latestPermit) {
                if ($latestPermit->status === 'out') {
                    if (Carbon::parse($latestPermit->planned_return_date)->isBefore($now)) {
                        $statusText = 'Terlambat';
                    } else {
                        $statusText = 'Sedang Keluar';
                    }
                }
            }

            return [
                'id' => $student->id,
                'name' => $student->name,
                'nis' => $student->nis,
                'avatar' => $student->avatar,
                'classroom_name' => $student->classroom->name ?? 'Tanpa Kelas',
                'status' => $statusText,
                'latest_permit' => $latestPermit
            ];
        });

        return response()->json([
            'success' => true,
            'students' => $result
        ]);
    }

    public function studentHistory($studentId)
    {
        $adminId = Auth::guard('web')->id();
        
        // Ensure student is supervised by this ustadz
        $student = \App\Models\Student::where('id', $studentId)
            ->where('asrama_host_id', $adminId)
            ->firstOrFail();

        $history = StudentPermit::where('student_id', $studentId)
            ->with(['user' => function ($query) {
                $query->select('id', 'name');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'nis' => $student->nis
            ],
            'history' => $history
        ]);
    }
}
