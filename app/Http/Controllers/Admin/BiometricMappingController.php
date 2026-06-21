<?php

namespace App\Http\Controllers\Admin;

use App\Models\BiometricMapping;
use App\Models\Student;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BiometricMappingController extends Controller
{
    public function index()
    {
        if (!Auth::user()->can('Manage Biometric')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            $data = BiometricMapping::latest()->get();
            return DataTables::of($data)
                ->addColumn('user_name', function ($row) {
                    return $row->presensiable ? $row->presensiable->name : '-';
                })
                ->addColumn('user_type', function ($row) {
                    $class = $row->presensiable_type;
                    if ($class === Student::class) {
                        return '<span class="badge badge-light-primary">Siswa/Santri</span>';
                    } elseif ($class === Admin::class) {
                        return '<span class="badge badge-light-success">Admin/Staff</span>';
                    } else {
                        return '<span class="badge badge-light-info">User/Officer</span>';
                    }
                })
                ->addColumn('biometric_type_label', function ($row) {
                    return $row->biometric_type === 'face'
                        ? '<span class="badge badge-light-warning"><i class="fa-solid fa-face-smile me-1 text-warning"></i> Wajah</span>'
                        : '<span class="badge badge-light-dark"><i class="fa-solid fa-fingerprint me-1 text-dark"></i> Sidik Jari</span>';
                })
                ->addColumn('device_pin_label', function ($row) {
                    return $row->device_pin ? '<code>' . $row->device_pin . '</code>' : '-';
                })
                ->addColumn('has_template', function ($row) {
                    return $row->template_data
                        ? '<span class="badge badge-light-success">Tersedia</span>'
                        : '<span class="badge badge-light-secondary">Kosong</span>';
                })
                ->addColumn('action', function ($row) {
                    $actionDelete = route('biometric-mapping.destroy', $row->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $row->id, 'name' => 'Biometric Mapping']) .
                        "</div>";
                })
                ->rawColumns(['action', 'user_type', 'biometric_type_label', 'device_pin_label', 'has_template'])
                ->make(true);
        }

        return view('admins.biometric-mapping.index');
    }

    public function searchUsers(Request $request)
    {
        $type = $request->type;
        $q = $request->q;

        $results = [];

        if ($type === 'siswa') {
            $students = Student::where('name', 'LIKE', "%{$q}%")
                ->orWhere('nis', 'LIKE', "%{$q}%")
                ->limit(10)
                ->get();
            foreach ($students as $student) {
                $results[] = [
                    'id' => $student->id,
                    'text' => $student->name . ' (NIS: ' . ($student->nis ?? '-') . ')',
                ];
            }
        } elseif ($type === 'karyawan') {
            $admins = Admin::where('name', 'LIKE', "%{$q}%")
                ->limit(10)
                ->get();
            foreach ($admins as $admin) {
                $results[] = [
                    'id' => $admin->id,
                    'text' => $admin->name . ' (Admin/Staff)',
                ];
            }
            $users = User::where('jamaah_status', '!=', 'JAMAAH')
                ->where('name', 'LIKE', "%{$q}%")
                ->limit(10)
                ->get();
            foreach ($users as $user) {
                $results[] = [
                    'id' => $user->id,
                    'text' => $user->name . ' (User/Officer)',
                ];
            }
        } elseif ($type === 'user') {
            $users = User::where('name', 'LIKE', "%{$q}%")
                ->limit(10)
                ->get();
            foreach ($users as $user) {
                $results[] = [
                    'id' => $user->id,
                    'text' => $user->name . ' (User/Wali)',
                ];
            }
        }

        return response()->json($results);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->can('Create Biometric')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'user_type' => 'required|in:siswa,karyawan,user',
            'user_id' => 'required|string',
            'biometric_type' => 'required|in:fingerprint,face',
            'device_pin' => 'nullable|required_if:biometric_type,fingerprint|string',
            'template_data' => 'nullable|string', // Face descriptor JSON array
        ]);

        // Tentukan model class
        $modelClass = null;
        if ($request->user_type === 'siswa') {
            $modelClass = Student::class;
        } elseif ($request->user_type === 'karyawan') {
            // Cek apakah user_id milik Admin atau User
            $existsInAdmin = Admin::find($request->user_id);
            $modelClass = $existsInAdmin ? Admin::class : User::class;
        } else {
            $modelClass = User::class;
        }

        $user = $modelClass::findOrFail($request->user_id);

        // Check if mapping already exists for this type
        $existing = BiometricMapping::where('presensiable_type', $modelClass)
            ->where('presensiable_id', $user->id)
            ->where('biometric_type', $request->biometric_type)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'User ini sudah memiliki pemetaan biometrik tipe ' . $request->biometric_type . '.'
            ], 400);
        }

        // Jika fingerprint, pastikan PIN unik untuk tipe fingerprint
        if ($request->biometric_type === 'fingerprint') {
            $pinExists = BiometricMapping::where('device_pin', $request->device_pin)
                ->where('biometric_type', 'fingerprint')
                ->exists();
            if ($pinExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'PIN ' . $request->device_pin . ' sudah terdaftar untuk pengguna lain.'
                ], 400);
            }
        }

        BiometricMapping::create([
            'presensiable_type' => $modelClass,
            'presensiable_id' => $user->id,
            'biometric_type' => $request->biometric_type,
            'biometric_index' => $request->biometric_type === 'face' ? 0 : 1, // index wajah=0, sidik jari=1
            'device_pin' => $request->biometric_type === 'fingerprint' ? $request->device_pin : 'F-' . $user->id,
            'template_data' => $request->template_data,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pemetaan biometrik berhasil ditambahkan untuk ' . $user->name
        ]);
    }

    public function destroy($id)
    {
        if (!Auth::user()->can('Delete Biometric')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $mapping = BiometricMapping::findOrFail($id);
        $mapping->delete();
        return redirect()->route('biometric-mapping.index')->with('success', 'Pemetaan biometrik berhasil dihapus');
    }

    public function kiosk()
    {
        if (!Auth::user()->can('Manage Biometric')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $today = \Carbon\Carbon::today()->format('Y-m-d');
        
        $outletId = request('outlet_id');
        if (!$outletId) {
            $koperasiId = \App\Models\Outlet::where('name', 'like', '%koperasi%')->value('id');
            $firstOutlet = \App\Models\Outlet::where('is_active', 1)
                ->where('id', '!=', $koperasiId)
                ->orderBy('name')
                ->first();
            $outletId = $firstOutlet ? $firstOutlet->id : null;
        }

        // If the logged in user is tied to a specific outlet, enforce it
        if (Auth::user()->outlet_id) {
            $outletId = Auth::user()->outlet_id;
        }

        $shiftsToday = \App\Models\EmployeeMonthlyShift::with(['presensiable', 'workingShift'])
            ->where('date', $today)
            ->whereNotNull('working_shift_id')
            ->where('is_holiday', false)
            ->get();

        $staffList = $shiftsToday->map(function ($shift) use ($outletId) {
            $user = $shift->presensiable;
            if (!$user) return null;

            // Pastikan user (Admin) terdaftar di tabel karyawans
            if ($user instanceof \App\Models\Admin) {
                // Filter by outlet
                if ($outletId && $user->outlet_id !== $outletId) {
                    return null;
                }

                $isKaryawan = \App\Models\Karyawan::where('admin_id', $user->id)->exists();
                if (!$isKaryawan) {
                    return null;
                }
            } else {
                return null;
            }

            // Tentukan status presensi hari ini
            $attendance = \App\Models\Attendance::where('presensiable_type', get_class($user))
                ->where('presensiable_id', $user->id)
                ->where('activity_type', 'work')
                ->whereDate('check_in', \Carbon\Carbon::today())
                ->first();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'type' => $user instanceof \App\Models\Admin ? 'Admin/Staff' : 'User/Officer',
                'user_type' => $user instanceof \App\Models\Admin ? 'admin' : 'user',
                'shift_name' => $shift->workingShift->name,
                'shift_time' => \Carbon\Carbon::parse($shift->workingShift->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($shift->workingShift->end_time)->format('H:i'),
                'attendance_status' => $attendance ? ($attendance->check_out ? 'done' : 'checked_in') : 'not_started',
                'attendance_id' => $attendance ? $attendance->id : null,
            ];
        })->filter()->values();

        return view('admins.attendance.kiosk', compact('staffList'));
    }

    public function descriptors()
    {
        if (!Auth::user()->can('Manage Biometric')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $mappings = BiometricMapping::where('biometric_type', 'face')
            ->whereNotNull('template_data')
            ->get()
            ->map(function($mapping) {
                $user = $mapping->presensiable;
                if (!$user) return null;

                // Saring agar Karyawan (Admin atau User) yang boleh scan wajah HANYA Kasir, Super Admin, atau yang punya hak POS
                if ($user instanceof \App\Models\Admin || $user instanceof \App\Models\User) {
                    if (!$user->hasRole('Kasir') && !$user->hasRole('Super Admin') && !$user->can('Manage Pos Kasir') && !$user->can('Create Pos Kasir')) {
                        return null;
                    }
                }
                
                $photo = asset('assets/media/avatars/blank.png');
                if (isset($user->avatar) && $user->avatar) {
                    $photo = \Illuminate\Support\Facades\Storage::url($user->avatar);
                }
                
                $type = 'User';
                if ($user instanceof \App\Models\Student) {
                    $type = 'Siswa/Santri';
                } elseif ($user instanceof \App\Models\Admin) {
                    $type = 'Admin/Staff';
                }
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'type' => $type,
                    'photo' => $photo,
                    'descriptor' => json_decode($mapping->template_data)
                ];
            })->filter()->values();
            
        return response()->json($mappings);
    }

    public function scan(Request $request)
    {
        if (!Auth::user()->can('Manage Biometric')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'user_id' => 'required|string',
            'type' => 'required|in:in,out',
        ]);

        $mapping = BiometricMapping::where('presensiable_id', $request->user_id)
            ->where('biometric_type', 'face')
            ->first();

        if (!$mapping) {
            return response()->json(['success' => false, 'message' => 'Mapping tidak ditemukan.'], 404);
        }

        $user = $mapping->presensiable;
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Pengguna tidak ditemukan.'], 404);
        }

        // Tentukan activity_type
        $activityType = 'school';
        if ($user instanceof \App\Models\Admin || ($user instanceof \App\Models\User && $user->jamaah_status !== 'JAMAAH')) {
            $activityType = 'work';
        }

        $apiController = new \App\Http\Controllers\Api\BiometricAttendanceController();
        $attendance = $apiController->processAttendance(
            $user,
            \Carbon\Carbon::now(),
            $request->type,
            'face_recognition',
            null,
            null,
            null,
            null,
            $activityType
        );

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat presensi. Pastikan jadwal shift kerja / kegiatan aktif hari ini.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Presensi berhasil dicatat untuk ' . $user->name . ' (' . ($request->type === 'in' ? 'Masuk' : 'Keluar') . ').',
            'data' => [
                'name' => $user->name,
                'time' => \Carbon\Carbon::now()->format('H:i:s'),
                'status' => $attendance->status,
                'late_minutes' => $attendance->late_minutes
            ]
        ]);
    }

    public function manualCapture(Request $request)
    {
        if (!Auth::user()->can('Manage Biometric')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'user_id' => 'required|string',
            'user_type' => 'required|in:admin,user',
            'type' => 'required|in:in,out',
            'photo' => 'required|string',
        ]);

        $modelClass = $request->user_type === 'admin' ? Admin::class : User::class;
        $user = $modelClass::find($request->user_id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Karyawan tidak ditemukan.'], 404);
        }

        $photoData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->photo));
        $fileName = 'attendances/' . \Illuminate\Support\Str::uuid() . '.jpg';
        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $photoData);
        $photoPath = \Illuminate\Support\Facades\Storage::url($fileName);

        $apiController = new \App\Http\Controllers\Api\BiometricAttendanceController();
        $attendance = $apiController->processAttendance(
            $user,
            \Carbon\Carbon::now(),
            $request->type,
            'face_recognition',
            null,
            null,
            null,
            $photoPath,
            'work'
        );

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat presensi. Pastikan jadwal shift kerja aktif hari ini.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Presensi berhasil dicatat untuk ' . $user->name . ' (' . ($request->type === 'in' ? 'Masuk' : 'Keluar') . '). Menunggu persetujuan admin.',
            'data' => [
                'name' => $user->name,
                'time' => \Carbon\Carbon::now()->format('H:i:s'),
                'status' => $attendance->status,
                'late_minutes' => $attendance->late_minutes
            ]
        ]);
    }
}
