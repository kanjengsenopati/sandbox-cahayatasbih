<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\EmployeeMonthlyShift;
use App\Models\EmployeeSalary;
use App\Models\SalarySlip;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    /**
     * Tampilkan Halaman Utama Laporan Slip Gaji
     */
    public function index(Request $request)
    {
        if (!Auth::user()->can('Manage Payroll')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if ($request->ajax()) {
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

                $data = SalarySlip::whereHasMorph('presensiable', [\App\Models\Admin::class], function($q) use ($outletId) {
                    if ($outletId) {
                        $q->where('outlet_id', $outletId);
                    }
                })->latest();
            } else {
                $data = SalarySlip::latest();
            }

            return DataTables::of($data)
                ->addColumn('employee_name', function ($row) {
                    return $row->presensiable ? $row->presensiable->name : '-';
                })
                ->addColumn('period', function ($row) {
                    return $row->period_start->format('d/m/Y') . ' - ' . $row->period_end->format('d/m/Y');
                })
                ->addColumn('net_salary_formatted', function ($row) {
                    return 'Rp ' . number_format($row->net_salary, 0, ',', '.');
                })
                ->addColumn('status_badge', function ($row) {
                    $badges = [
                        'draft' => '<span class="badge badge-warning">Draft</span>',
                        'approved' => '<span class="badge badge-primary">Approved</span>',
                        'paid' => '<span class="badge badge-success">Paid</span>',
                    ];
                    return $badges[$row->status] ?? $row->status;
                })
                ->addColumn('btnAction', function ($row) {
                    $actionShow = route('payroll.show', array_merge([$row->id], request()->only(['mode', 'outlet_id'])));
                    $btn = "<a href='{$actionShow}' class='btn btn-sm btn-outline-primary mr-1' title='Detail'><i class='fa fa-eye'></i> Detail</a>";
                    return "<div class='d-flex justify-content-center'>{$btn}</div>";
                })
                ->rawColumns(['status_badge', 'btnAction'])
                ->make(true);
        }

        return view('admins.payroll.index');
    }

    /**
     * Proses/Kalkulasi Slip Gaji Karyawan Baru (Drafting)
     */
    public function process(Request $request)
    {
        if (!Auth::user()->can('Create Payroll')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Cari semua karyawan (baik di tabel admins maupun users yang memiliki relasi employeeSalary)
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

            $admins = Admin::has('employeeSalary')
                ->where('outlet_id', $outletId)
                ->get();
            $users = collect();
        } else {
            $admins = Admin::has('employeeSalary')->get();
            $users = User::has('employeeSalary')->get();
        }
        $employees = $admins->merge($users);

        $processedCount = 0;

        DB::transaction(function () use ($employees, $startDate, $endDate, &$processedCount) {
            foreach ($employees as $employee) {
                $salaryConfig = $employee->employeeSalary;
                if (!$salaryConfig) continue;

                $karyawan = \App\Models\Karyawan::where('admin_id', $employee->id)->first();

                if ($karyawan) {
                    $gajiBulan = $karyawan->gaji_bulan;
                    $gajiHari = $karyawan->gaji_hari;

                    // 1. Ambil semua shift kerja terjadwal dari EmployeeMonthlyShift
                    $scheduledShifts = EmployeeMonthlyShift::with('workingShift')
                        ->where('presensiable_type', get_class($employee))
                        ->where('presensiable_id', $employee->id)
                        ->whereBetween('date', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
                        ->where('is_holiday', false)
                        ->whereNotNull('working_shift_id')
                        ->get();

                    $totalScheduledDays = $scheduledShifts->count();
                    if ($totalScheduledDays === 0) continue;

                    // Ambil kehadiran aktual
                    $attendances = Attendance::where('presensiable_type', get_class($employee))
                        ->where('presensiable_id', $employee->id)
                        ->where('activity_type', 'work')
                        ->where('approval_status', 'approved')
                        ->whereBetween('check_in', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
                        ->get();

                    $totalPresentDays = 0;
                    $totalLateMinutes = 0;
                    $totalLatenessPenalty = 0;
                    $totalAbsencePenalty = 0;

                    // Kelompokkan scheduledShifts berdasarkan tanggal
                    $shiftsByDate = $scheduledShifts->groupBy(function($item) {
                        return Carbon::parse($item->date)->toDateString();
                    });

                    $presentDaysProportion = 0;
                    $absentDaysProportion = 0;

                    foreach ($shiftsByDate as $dateStr => $shiftsOnDate) {
                        $numShiftsToday = $shiftsOnDate->count();
                        if ($numShiftsToday === 0) continue;

                        $salaryPerShift = $gajiHari / $numShiftsToday;
                        $presentShiftsToday = 0;
                        $absentShiftsToday = 0;

                        foreach ($shiftsOnDate as $monthlyShift) {
                            $shift = $monthlyShift->workingShift;
                            if (!$shift) continue;

                            $att = $attendances->first(function($a) use ($dateStr, $shift) {
                                return Carbon::parse($a->check_in)->toDateString() === $dateStr && $a->activity_name === $shift->name;
                            });

                            if ($att) {
                                if ($att->status === 'late') {
                                    $totalLatenessPenalty += $salaryPerShift;
                                    $totalLateMinutes += $att->late_minutes;
                                } else {
                                    $presentShiftsToday++;
                                }
                            } else {
                                $totalAbsencePenalty += $salaryPerShift;
                                $absentShiftsToday++;
                            }
                        }

                        $presentDaysProportion += ($presentShiftsToday / $numShiftsToday);
                        $absentDaysProportion += ($absentShiftsToday / $numShiftsToday);
                    }

                    $totalPresentDays = $presentDaysProportion;
                    $totalAbsentDays = $absentDaysProportion;

                    $baseSalary = $gajiBulan;
                    $totalAttendanceAllowance = 0;
                    $totalTransportAllowance = 0;

                    $netSalary = $baseSalary - ($totalLatenessPenalty + $totalAbsencePenalty);
                } else {
                    // 1. Hitung hari kerja terjadwal dari EmployeeMonthlyShift
                    $scheduledShifts = EmployeeMonthlyShift::where('presensiable_type', get_class($employee))
                        ->where('presensiable_id', $employee->id)
                        ->whereBetween('date', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
                        ->where('is_holiday', false)
                        ->whereNotNull('working_shift_id')
                        ->get();

                    $totalScheduledDays = $scheduledShifts->count();
                    if ($totalScheduledDays === 0) continue;

                    // 2. Hitung kehadiran aktual
                    $attendances = Attendance::where('presensiable_type', get_class($employee))
                        ->where('presensiable_id', $employee->id)
                        ->where('activity_type', 'work')
                        ->where('approval_status', 'approved')
                        ->whereBetween('check_in', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
                        ->get();

                    $totalPresentDays = 0;
                    $totalLateMinutes = 0;

                    foreach ($scheduledShifts as $shift) {
                        $attendanceToday = $attendances->first(function ($att) use ($shift) {
                            return Carbon::parse($att->check_in)->toDateString() === $shift->date->toDateString();
                        });

                        if ($attendanceToday) {
                            $totalPresentDays++;
                            $totalLateMinutes += $attendanceToday->late_minutes;
                        }
                    }

                    $totalAbsentDays = $totalScheduledDays - $totalPresentDays;

                    // 3. Kalkulasi Gaji, Tunjangan, dan Potongan
                    $baseSalary = $salaryConfig->base_salary;
                    $totalAttendanceAllowance = $totalPresentDays * $salaryConfig->attendance_allowance;
                    $totalTransportAllowance = $totalPresentDays * $salaryConfig->transport_allowance;

                    $latenessPenaltyRate = $salaryConfig->lateness_penalty_type === 'percentage'
                        ? ($salaryConfig->lateness_penalty_value / 100) * $baseSalary
                        : ($salaryConfig->lateness_penalty_value > 0 ? $salaryConfig->lateness_penalty_value : $salaryConfig->lateness_penalty_per_minute);

                    $totalLatenessPenalty = $totalLateMinutes * $latenessPenaltyRate;
                    $totalAbsencePenalty = $totalAbsentDays * $salaryConfig->absence_penalty;

                    $netSalary = ($baseSalary + $totalAttendanceAllowance + $totalTransportAllowance) - ($totalLatenessPenalty + $totalAbsencePenalty);
                }

                // Pastikan gaji bersih tidak bernilai negatif
                if ($netSalary < 0) {
                    $netSalary = 0;
                }

                // 4. Buat atau update slip gaji DRAFT
                SalarySlip::updateOrCreate(
                    [
                        'presensiable_type' => get_class($employee),
                        'presensiable_id' => $employee->id,
                        'period_start' => $startDate->toDateString(),
                        'period_end' => $endDate->toDateString(),
                    ],
                    [
                        'total_present_days' => (int) round($totalPresentDays),
                        'total_late_minutes' => $totalLateMinutes,
                        'total_absent_days' => (int) round($totalAbsentDays),
                        'base_salary' => $baseSalary,
                        'total_attendance_allowance' => $totalAttendanceAllowance,
                        'total_transport_allowance' => $totalTransportAllowance,
                        'total_lateness_penalty' => $totalLatenessPenalty,
                        'total_absence_penalty' => $totalAbsencePenalty,
                        'net_salary' => $netSalary,
                        'status' => 'draft',
                    ]
                );

                $processedCount++;
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Berhasil memproses $processedCount slip gaji (Draft)."
        ]);
    }

    /**
     * Tampilkan Detail Slip Gaji
     */
    public function show($id)
    {
        if (!Auth::user()->can('Manage Payroll')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $slip = SalarySlip::with('approver')->findOrFail($id);
        return view('admins.payroll.show', compact('slip'));
    }

    /**
     * Verifikasi Manual / Approve Slip Gaji (Verifikasi Manual)
     */
    public function approve($id)
    {
        if (!Auth::user()->can('Approve Payroll')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki hak untuk melakukan persetujuan.');
        }

        $slip = SalarySlip::findOrFail($id);
        if ($slip->status !== 'draft') {
            return redirect()->back()->with('error', 'Slip gaji sudah di-approve sebelumnya.');
        }

        $slip->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('payroll.show', array_merge([$id], request()->only(['mode', 'outlet_id'])))->with('success', 'Slip gaji berhasil disetujui (Approved).');
    }

    /**
     * Konfirmasi Pembayaran Slip Gaji
     */
    public function pay($id)
    {
        if (!Auth::user()->can('Pay Payroll')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki hak untuk memproses pembayaran.');
        }

        $slip = SalarySlip::findOrFail($id);
        if ($slip->status !== 'approved') {
            return redirect()->back()->with('error', 'Slip gaji harus disetujui (Approved) sebelum dibayarkan.');
        }

        $slip->update([
            'status' => 'paid',
        ]);

        // Hubungkan ke CashFlow Outlet (Pengeluaran - Honor Kasir)
        $employee = $slip->presensiable;
        $outletId = $employee && isset($employee->outlet_id) ? $employee->outlet_id : null;

        if (!$outletId && $employee) {
            $firstOutlet = \App\Models\Outlet::first();
            $outletId = $firstOutlet ? $firstOutlet->id : null;
        }

        if ($outletId) {
            $category = \App\Models\CashFlowCategory::firstOrCreate([
                'name' => 'Pengeluaran - Honor Kasir'
            ]);

            $cashflowCount = \App\Models\CashFlow::whereDate('created_at', now())->count();
            $paymentCode = 'CT-' . now()->format('Ymd') . str_pad($cashflowCount + 1, 3, '0', STR_PAD_LEFT);
            
            $adminId = Auth::id() ?: (\App\Models\Admin::first() ? \App\Models\Admin::first()->id : null);
            $receiverId = $employee ? $employee->id : $adminId;

            if ($adminId && $receiverId) {
                \App\Models\CashFlow::create([
                    'sender_id' => $adminId,
                    'receiver_id' => $receiverId,
                    'outlet_id' => $outletId,
                    'cash_flow_category_id' => $category->id,
                    'payment_code' => $paymentCode,
                    'type' => \App\Models\CashFlow::TYPE_EXPENSE,
                    'amount' => (int) $slip->net_salary,
                    'date' => now()->toDateString(),
                    'description' => 'Pembayaran gaji periode ' . $slip->period_start->format('d/m/Y') . ' - ' . $slip->period_end->format('d/m/Y') . ' untuk ' . ($employee ? $employee->name : 'Karyawan'),
                    'status' => \App\Models\CashFlow::STATUS_APPROVED,
                    'payment_method' => 'cash',
                ]);
            }
        }

        return redirect()->route('payroll.show', array_merge([$id], request()->only(['mode', 'outlet_id'])))->with('success', 'Slip gaji berhasil ditandai sebagai dibayarkan (Paid) dan dicatat sebagai Pengeluaran - Honor Kasir pada CashFlow Outlet.');
    }

    /**
     * Tampilkan Pengaturan Gaji Karyawan via DataTables/View
     */
    public function settings(Request $request)
    {
        if (!Auth::user()->can('Manage Payroll')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if ($request->ajax()) {
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

                $admins = Admin::where('outlet_id', $outletId)->get()->map(function ($item) {
                    $item->type_class = Admin::class;
                    $item->role_name = $item->roles->first()->name ?? 'Admin';
                    return $item;
                });
                
                $users = collect();
            } else {
                // Kita ingin memuat semua Admins dan Users (Karyawan)
                $admins = Admin::all()->map(function ($item) {
                    $item->type_class = Admin::class;
                    $item->role_name = $item->roles->first()->name ?? 'Admin';
                    return $item;
                });
                
                $users = User::where('jamaah_status', '!=', 'JAMAAH')->get()->map(function ($item) {
                    $item->type_class = User::class;
                    $item->role_name = 'User / Officer';
                    return $item;
                });
            }

            $employees = $admins->concat($users);

            return DataTables::of($employees)
                ->addColumn('employee_name', function ($row) {
                    return $row->name;
                })
                ->addColumn('employee_type', function ($row) {
                    return $row->type_class === Admin::class ? '<span class="badge badge-light-primary">Admin/Staff</span>' : '<span class="badge badge-light-success">User/Officer</span>';
                })
                ->addColumn('role_label', function ($row) {
                    return $row->role_name;
                })
                ->addColumn('base_salary_label', function ($row) {
                    $config = $row->employeeSalary;
                    return $config ? 'Rp ' . number_format($config->base_salary, 0, ',', '.') : '-';
                })
                ->addColumn('allowance_label', function ($row) {
                    $isKaryawanOutlet = \App\Models\Karyawan::where('admin_id', $row->id)->exists();
                    if ($isKaryawanOutlet) {
                        return '-';
                    }
                    $config = $row->employeeSalary;
                    if (!$config) return '-';
                    return 'Hadir: Rp ' . number_format($config->attendance_allowance, 0, ',', '.') . '<br>Trans: Rp ' . number_format($config->transport_allowance, 0, ',', '.');
                })
                ->addColumn('lateness_penalty_label', function ($row) {
                    $isKaryawanOutlet = \App\Models\Karyawan::where('admin_id', $row->id)->exists();
                    if ($isKaryawanOutlet) {
                        return 'Potongan Full per Shift';
                    }
                    $config = $row->employeeSalary;
                    if (!$config) return '-';
                    if ($config->lateness_penalty_type === 'percentage') {
                        return $config->lateness_penalty_value . '% dari Gaji Pokok';
                    }
                    return 'Rp ' . number_format($config->lateness_penalty_value, 0, ',', '.') . ' (Fixed)';
                })
                ->addColumn('btnAction', function ($row) {
                    $isKaryawan = \App\Models\Karyawan::where('admin_id', $row->id)->exists();
                    $config = $row->employeeSalary;
                    $dataAttr = 'data-id="' . $row->id . '" ' .
                        'data-type="' . urlencode($row->type_class) . '" ' .
                        'data-name="' . htmlspecialchars($row->name) . '" ' .
                        'data-is_karyawan="' . ($isKaryawan ? 1 : 0) . '" ' .
                        'data-base_salary="' . ($config ? (int)$config->base_salary : 0) . '" ' .
                        'data-attendance_allowance="' . ($config ? (int)$config->attendance_allowance : 0) . '" ' .
                        'data-transport_allowance="' . ($config ? (int)$config->transport_allowance : 0) . '" ' .
                        'data-lateness_penalty_type="' . ($config ? $config->lateness_penalty_type : 'fixed') . '" ' .
                        'data-lateness_penalty_value="' . ($config ? (int)$config->lateness_penalty_value : 0) . '" ' .
                        'data-absence_penalty="' . ($config ? (int)$config->absence_penalty : 0) . '"';
                    
                    return '<button class="btn btn-sm btn-light-primary btn-edit-salary" ' . $dataAttr . '><i class="fa-solid fa-pencil fs-7 me-1"></i> Atur Gaji</button>';
                })
                ->rawColumns(['employee_type', 'allowance_label', 'btnAction'])
                ->make(true);
        }

        return redirect()->route('payroll.index');
    }

    /**
     * Simpan Pengaturan Gaji Karyawan
     */
    public function saveSetting(Request $request)
    {
        if (!Auth::user()->can('Manage Payroll')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Sanitasi input nominal dengan membuang pemisah ribuan titik
        $fields = ['base_salary', 'attendance_allowance', 'transport_allowance', 'absence_penalty'];
        $merge = [];
        foreach ($fields as $field) {
            if ($request->has($field) && $request->input($field) !== null) {
                $merge[$field] = str_replace('.', '', $request->input($field));
            }
        }
        if ($request->input('lateness_penalty_type') === 'fixed' && $request->has('lateness_penalty_value') && $request->input('lateness_penalty_value') !== null) {
            $merge['lateness_penalty_value'] = str_replace('.', '', $request->input('lateness_penalty_value'));
        }
        if (!empty($merge)) {
            $request->merge($merge);
        }

        $employeeType = urldecode($request->employee_type);
        $isKaryawanOutlet = false;
        if (in_array($employeeType, [Admin::class, User::class])) {
            $isKaryawanOutlet = ($employeeType === Admin::class) && \App\Models\Karyawan::where('admin_id', $request->employee_id)->exists();
        }

        if ($isKaryawanOutlet) {
            $baseSalary = (int) $request->input('base_salary', 0);
            $gajiHari = floor(($baseSalary / 30) / 100) * 100;
            $request->merge([
                'attendance_allowance' => 0,
                'transport_allowance' => 0,
                'lateness_penalty_type' => 'fixed',
                'lateness_penalty_value' => 0,
                'absence_penalty' => $gajiHari
            ]);
        }

        $request->validate([
            'employee_id' => 'required|string',
            'employee_type' => 'required|string',
            'base_salary' => 'required|numeric|min:0',
            'attendance_allowance' => 'required|numeric|min:0',
            'transport_allowance' => 'required|numeric|min:0',
            'lateness_penalty_type' => 'required|string|in:fixed,percentage',
            'lateness_penalty_value' => 'required|numeric|min:0',
            'absence_penalty' => 'required|numeric|min:0',
        ]);

        if (!in_array($employeeType, [Admin::class, User::class])) {
            return response()->json(['success' => false, 'message' => 'Tipe Karyawan tidak valid.'], 400);
        }

        $employee = $employeeType::findOrFail($request->employee_id);

        if ($isKaryawanOutlet) {
            $karyawan = \App\Models\Karyawan::where('admin_id', $employee->id)->first();
            if ($karyawan) {
                $karyawan->update([
                    'gaji_bulan' => $request->base_salary,
                    'gaji_hari' => $request->absence_penalty,
                    'potongan_absen' => $request->absence_penalty,
                    'potongan_terlambat' => 0
                ]);
            }
        }

        EmployeeSalary::updateOrCreate(
            [
                'presensiable_type' => $employeeType,
                'presensiable_id' => $employee->id,
            ],
            [
                'base_salary' => $request->base_salary,
                'attendance_allowance' => $request->attendance_allowance,
                'transport_allowance' => $request->transport_allowance,
                'lateness_penalty_type' => $request->lateness_penalty_type,
                'lateness_penalty_value' => $request->lateness_penalty_value,
                'absence_penalty' => $request->absence_penalty,
                'lateness_penalty_per_minute' => $request->lateness_penalty_type === 'fixed' ? $request->lateness_penalty_value : 0,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan gaji untuk ' . $employee->name . ' berhasil disimpan.'
        ]);
    }
}
