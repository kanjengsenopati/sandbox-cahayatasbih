<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\School;
use App\Models\Student;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class ReportAttendanceController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->can('Manage Laporan Presensi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if ($request->ajax()) {
            $type = $request->input('type', 'siswa_santri');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            if ($type === 'siswa_santri') {
                $schoolId = $request->input('school_id');
                $activity = $request->input('activity'); // school, prayer, kajian

                $query = Attendance::with('presensiable.school')
                    ->where('presensiable_type', Student::class);

                if ($schoolId) {
                    $query->whereHas('presensiable', function ($q) use ($schoolId) {
                        $q->where('school_id', $schoolId);
                    });
                }

                if ($activity) {
                    if ($activity === 'school') {
                        $query->where('activity_type', 'school');
                    } elseif ($activity === 'prayer') {
                        $query->where('activity_type', 'prayer');
                    } elseif ($activity === 'kajian') {
                        $query->where('activity_type', 'kajian');
                    }
                }

                if ($startDate) {
                    $query->whereDate('check_in', '>=', $startDate);
                }
                if ($endDate) {
                    $query->whereDate('check_in', '<=', $endDate);
                }

                $query->latest('check_in');

                return DataTables::of($query)
                    ->addColumn('student_name', function ($row) {
                        return $row->presensiable ? $row->presensiable->name : '-';
                    })
                    ->addColumn('school_name', function ($row) {
                        return $row->presensiable && $row->presensiable->school ? $row->presensiable->school->name : '-';
                    })
                    ->addColumn('activity_type_label', function ($row) {
                        $types = [
                            'school' => '<span class="badge badge-light-primary">Sekolah</span>',
                            'prayer' => '<span class="badge badge-light-success">Sholat</span>',
                            'kajian' => '<span class="badge badge-light-warning">Ngaji Murojaah</span>',
                        ];
                        return $types[$row->activity_type] ?? $row->activity_type;
                    })
                    ->addColumn('check_in_formatted', function ($row) {
                        return $row->check_in ? $row->check_in->translatedFormat('d/m/Y H:i') : '-';
                    })
                    ->addColumn('check_out_formatted', function ($row) {
                        return $row->check_out ? $row->check_out->translatedFormat('d/m/Y H:i') : '-';
                    })
                    ->addColumn('status_badge', function ($row) {
                        $badges = [
                            'present' => '<span class="badge badge-success">Hadir</span>',
                            'late' => '<span class="badge badge-warning">Terlambat</span>',
                            'absent' => '<span class="badge badge-danger">Alpa</span>',
                            'permit' => '<span class="badge badge-info">Izin</span>',
                            'sick' => '<span class="badge badge-secondary">Sakit</span>',
                        ];
                        return $badges[$row->status] ?? $row->status;
                    })
                    ->addColumn('late_label', function ($row) {
                        return $row->late_minutes > 0 ? $row->late_minutes . ' Menit' : '-';
                    })
                    ->rawColumns(['activity_type_label', 'status_badge'])
                    ->make(true);
            } else {
                // Tipe Karyawan
                $query = Attendance::with('presensiable')
                    ->whereIn('presensiable_type', [Admin::class, User::class])
                    ->where('activity_type', 'work');

                if ($request->input('mode') === 'outlet') {
                    $koperasi = \App\Models\Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
                    $koperasiId = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';

                    $outletId = $request->input('outlet_id');
                    $authOutletIds = auth()->user()->getOutletIds();
                    $hasOutletRestriction = count($authOutletIds) > 0;

                    if ($hasOutletRestriction) {
                        $outletId = $outletId && in_array($outletId, $authOutletIds) ? $outletId : ($authOutletIds[0] ?? null);
                    } else {
                        // Super Admin
                        if (!$outletId) {
                            $firstOutlet = \App\Models\Outlet::where('is_active', 1)
                                ->where('id', '!=', $koperasiId)
                                ->orderBy('name')
                                ->first();
                            $outletId = $firstOutlet ? $firstOutlet->id : null;
                        }
                    }

                    if ($outletId) {
                        $query->whereHasMorph('presensiable', [\App\Models\Admin::class], function($q) use ($outletId) {
                            $q->where('outlet_id', $outletId);
                        });
                    }
                }

                if ($startDate) {
                    $query->whereDate('check_in', '>=', $startDate);
                }
                if ($endDate) {
                    $query->whereDate('check_in', '<=', $endDate);
                }

                $query->latest('check_in');

                return DataTables::of($query)
                    ->addColumn('employee_name', function ($row) {
                        return $row->presensiable ? $row->presensiable->name : '-';
                    })
                    ->addColumn('employee_type', function ($row) {
                        return $row->presensiable_type === Admin::class ? '<span class="badge badge-light-primary">Admin/Staff</span>' : '<span class="badge badge-light-success">User/Officer</span>';
                    })
                    ->addColumn('check_in_formatted', function ($row) {
                        return $row->check_in ? $row->check_in->translatedFormat('d/m/Y H:i') : '-';
                    })
                    ->addColumn('check_out_formatted', function ($row) {
                        return $row->check_out ? $row->check_out->translatedFormat('d/m/Y H:i') : '-';
                    })
                    ->addColumn('status_badge', function ($row) {
                        $badges = [
                            'present' => '<span class="badge badge-success">Hadir</span>',
                            'late' => '<span class="badge badge-warning">Terlambat</span>',
                            'absent' => '<span class="badge badge-danger">Alpa</span>',
                            'permit' => '<span class="badge badge-info">Izin</span>',
                            'sick' => '<span class="badge badge-secondary">Sakit</span>',
                        ];
                        return $badges[$row->status] ?? $row->status;
                    })
                    ->addColumn('late_label', function ($row) {
                        return $row->late_minutes > 0 ? $row->late_minutes . ' Menit' : '-';
                    })
                    ->addColumn('photo_url_html', function ($row) {
                        if ($row->photo_path) {
                            return '<a href="' . asset($row->photo_path) . '" target="_blank">' .
                                   '<img src="' . asset($row->photo_path) . '" class="w-50px h-50px rounded object-cover shadow-sm" alt="Foto Bukti">' .
                                   '</a>';
                        }
                        return '<span class="text-muted italic">Tidak ada foto</span>';
                    })
                    ->addColumn('approval_badge', function ($row) {
                        $badges = [
                            'pending' => '<span class="badge badge-light-warning">Pending</span>',
                            'approved' => '<span class="badge badge-light-success">Disetujui</span>',
                            'rejected' => '<span class="badge badge-light-danger">Ditolak</span>',
                        ];
                        return $badges[$row->approval_status] ?? '<span class="badge badge-light-secondary">' . $row->approval_status . '</span>';
                    })
                    ->addColumn('action', function ($row) {
                        if ($row->approval_status === 'pending') {
                            return '<div class="d-flex gap-2 justify-content-center">' .
                                   '<button class="btn btn-sm btn-icon btn-light-success btn-approve-attendance" data-id="' . $row->id . '"><i class="fa fa-check p-0"></i></button>' .
                                   '<button class="btn btn-sm btn-icon btn-light-danger btn-reject-attendance" data-id="' . $row->id . '"><i class="fa fa-times p-0"></i></button>' .
                                   '</div>';
                        }
                        
                        $approver = $row->approvedBy ? $row->approvedBy->name : 'System';
                        return '<span class="text-muted fs-8">Diproses oleh: ' . $approver . '</span>';
                    })
                    ->rawColumns(['employee_type', 'status_badge', 'photo_url_html', 'approval_badge', 'action'])
                    ->make(true);
            }
        }

        $schools = School::orderBy('name')->get();
        return view('admins.report-attendance.index', compact('schools'));
    }

    public function approve($id)
    {
        if (!Auth::user()->can('Manage Laporan Presensi')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $attendance = Attendance::findOrFail($id);
        $attendance->update([
            'approval_status' => 'approved',
            'approved_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Presensi berhasil disetujui.',
        ]);
    }

    public function reject($id, Request $request)
    {
        if (!Auth::user()->can('Manage Laporan Presensi')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $attendance = Attendance::findOrFail($id);
        $attendance->update([
            'approval_status' => 'rejected',
            'approved_by' => Auth::id(),
            'notes' => $request->input('notes') ? 'Rejected: ' . $request->input('notes') : 'Rejected by Admin',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Presensi berhasil ditolak.',
        ]);
    }
}
