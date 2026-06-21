<?php

namespace App\Http\Controllers\Admin;

use App\Models\WorkingShift;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class WorkingShiftController extends Controller
{
    public function index()
    {
        if (!Auth::user()->can('Manage Shift')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            $data = WorkingShift::latest()->get();
            return DataTables::of($data)
                ->addColumn('target_label', function ($row) {
                    $labels = [
                        'siswa_santri' => '<span class="badge badge-light-primary">Siswa dan Santri</span>',
                        'karyawan' => '<span class="badge badge-light-success">Karyawan</span>',
                        'user' => '<span class="badge badge-light-info">User / Wali</span>',
                    ];
                    return $labels[$row->target_type] ?? $row->target_type;
                })
                ->addColumn('days_label', function ($row) {
                    if (empty($row->days)) return '-';
                    return implode(', ', $row->days);
                })
                ->addColumn('status', function ($row) {
                    return $row->is_active ? '<span class="badge badge-success">Aktif</span>'
                        : '<span class="badge badge-danger">Tidak Aktif</span>';
                })
                ->addColumn('action', function ($row) {
                    $actionStatus = route('working-shift.status', array_merge([$row->id], request()->only(['mode', 'outlet_id'])));
                    $actionEdit = route('working-shift.edit', array_merge([$row->id], request()->only(['mode', 'outlet_id'])));
                    $actionDelete = route('working-shift.destroy', array_merge([$row->id], request()->only(['mode', 'outlet_id'])));
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.status', ['action' => $actionStatus, 'status' => $row->is_active, 'id' => $row->id, 'name' => 'Shift']) .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Shift']) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $row->id, 'name' => 'Shift']) .
                        "</div>";
                })
                ->rawColumns(['action', 'status', 'target_label', 'days_label'])
                ->make(true);
        }

        return view('admins.working-shift.index');
    }

    public function create()
    {
        if (!Auth::user()->can('Create Shift')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $admins = \App\Models\Admin::orderBy('name')->get();
        $users = \App\Models\User::where('jamaah_status', '!=', 'JAMAAH')->orderBy('name')->get();
        
        $employees = collect();
        foreach ($admins as $admin) {
            $employees->push([
                'value' => 'App\\Models\\Admin:' . $admin->id,
                'name' => $admin->name . ' (Admin/Staff)',
            ]);
        }
        foreach ($users as $user) {
            $employees->push([
                'value' => 'App\\Models\\User:' . $user->id,
                'name' => $user->name . ' (User/Officer)',
            ]);
        }

        return view('admins.working-shift.create-edit', compact('employees'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->can('Create Shift')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'grace_period' => 'required|integer|min:0',
            'target_type' => 'required|string|in:siswa_santri,karyawan,user',
            'days' => 'required|array',
            'days.*' => 'string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'assigned_users' => 'nullable|array',
        ]);

        $workingShift = WorkingShift::create([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'grace_period' => $request->grace_period,
            'target_type' => $request->target_type,
            'days' => $request->days,
            'assigned_users' => $request->assigned_users,
            'is_active' => true,
        ]);

        $this->syncEmployeeMonthlyShifts($workingShift);

        return redirect()->route('working-shift.index', $request->only(['mode', 'outlet_id']))->with('success', 'Shift Presensi berhasil ditambahkan');
    }

    public function edit(WorkingShift $workingShift)
    {
        if (!Auth::user()->can('Edit Shift')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $admins = \App\Models\Admin::orderBy('name')->get();
        $users = \App\Models\User::where('jamaah_status', '!=', 'JAMAAH')->orderBy('name')->get();
        
        $employees = collect();
        foreach ($admins as $admin) {
            $employees->push([
                'value' => 'App\\Models\\Admin:' . $admin->id,
                'name' => $admin->name . ' (Admin/Staff)',
            ]);
        }
        foreach ($users as $user) {
            $employees->push([
                'value' => 'App\\Models\\User:' . $user->id,
                'name' => $user->name . ' (User/Officer)',
            ]);
        }

        return view('admins.working-shift.create-edit', compact('workingShift', 'employees'));
    }

    public function update(Request $request, WorkingShift $workingShift)
    {
        if (!Auth::user()->can('Edit Shift')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'grace_period' => 'required|integer|min:0',
            'target_type' => 'required|string|in:siswa_santri,karyawan,user',
            'days' => 'required|array',
            'days.*' => 'string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'assigned_users' => 'nullable|array',
        ]);

        $workingShift->update([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'grace_period' => $request->grace_period,
            'target_type' => $request->target_type,
            'days' => $request->days,
            'assigned_users' => $request->assigned_users,
        ]);

        $this->syncEmployeeMonthlyShifts($workingShift);

        return redirect()->route('working-shift.index', $request->only(['mode', 'outlet_id']))->with('success', 'Shift Presensi berhasil diperbarui');
    }

    public function destroy(WorkingShift $workingShift)
    {
        if (!Auth::user()->can('Delete Shift')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $workingShift->delete();
        return redirect()->route('working-shift.index', request()->only(['mode', 'outlet_id']))->with('success', 'Shift Presensi berhasil dihapus');
    }

    public function status(string $id)
    {
        if (!Auth::user()->can('Edit Shift')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $shift = WorkingShift::findOrFail($id);
        $shift->update(['is_active' => !$shift->is_active]);
        return redirect()->route('working-shift.index', request()->only(['mode', 'outlet_id']))->with('success', 'Status shift presensi berhasil diperbarui');
    }

    private function syncEmployeeMonthlyShifts(WorkingShift $workingShift)
    {
        $activeDays = $workingShift->days ?? [];
        if (empty($activeDays)) {
            return;
        }

        $dayMap = [
            'Senin' => 1,
            'Selasa' => 2,
            'Rabu' => 3,
            'Kamis' => 4,
            'Jumat' => 5,
            'Sabtu' => 6,
            'Minggu' => 7,
        ];

        $activeDayNumbers = array_map(function($day) use ($dayMap) {
            return $dayMap[$day] ?? null;
        }, $activeDays);
        $activeDayNumbers = array_filter($activeDayNumbers);

        $startDate = now()->startOfMonth();
        $endDate = now()->addMonth()->endOfMonth();

        // Hapus monthly shift lama untuk shift ini agar bisa di-sync ulang
        \App\Models\EmployeeMonthlyShift::where('working_shift_id', $workingShift->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->delete();

        if ($workingShift->target_type !== 'karyawan') {
            return;
        }

        $assignedUsers = $workingShift->assigned_users ?? [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayOfWeek = $date->dayOfWeekIso;
            
            if (in_array($dayOfWeek, $activeDayNumbers)) {
                $isHoliday = \App\Models\Holiday::where('date', $date->toDateString())->exists();

                foreach ($assignedUsers as $userStr) {
                    $parts = explode(':', $userStr);
                    if (count($parts) === 2) {
                        $type = $parts[0];
                        $id = $parts[1];

                        \App\Models\EmployeeMonthlyShift::updateOrCreate(
                            [
                                'presensiable_type' => $type,
                                'presensiable_id' => $id,
                                'date' => $date->toDateString(),
                                'working_shift_id' => $workingShift->id,
                            ],
                            [
                                'is_holiday' => $isHoliday,
                            ]
                        );
                    }
                }
            }
        }
    }
}
