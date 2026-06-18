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
    public function index()
    {
        if (!Auth::user()->can('Manage Payroll')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            $data = SalarySlip::latest();
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
                    $actionShow = route('payroll.show', $row->id);
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
        $admins = Admin::has('employeeSalary')->get();
        $users = User::has('employeeSalary')->get();
        $employees = $admins->merge($users);

        $processedCount = 0;

        DB::transaction(function () use ($employees, $startDate, $endDate, &$processedCount) {
            foreach ($employees as $employee) {
                $salaryConfig = $employee->employeeSalary;
                if (!$salaryConfig) continue;

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

                $totalLatenessPenalty = $totalLateMinutes * $salaryConfig->lateness_penalty_per_minute;
                $totalAbsencePenalty = $totalAbsentDays * $salaryConfig->absence_penalty;

                $netSalary = ($baseSalary + $totalAttendanceAllowance + $totalTransportAllowance) - ($totalLatenessPenalty + $totalAbsencePenalty);
                
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
                        'total_present_days' => $totalPresentDays,
                        'total_late_minutes' => $totalLateMinutes,
                        'total_absent_days' => $totalAbsentDays,
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

        return redirect()->route('payroll.show', $id)->with('success', 'Slip gaji berhasil disetujui (Approved).');
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

        return redirect()->route('payroll.show', $id)->with('success', 'Slip gaji berhasil ditandai sebagai dibayarkan (Paid).');
    }
}
