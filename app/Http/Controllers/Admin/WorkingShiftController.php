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
        if (!Auth::user()->can('Manage Pengaturan Aplikasi')) {
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
                    $actionStatus = route('working-shift.status', $row->id);
                    $actionEdit = route('working-shift.edit', $row->id);
                    $actionDelete = route('working-shift.destroy', $row->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.status', ['action' => $actionStatus, 'status' => $row->is_active, 'id' => $row->id]) .
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
        if (!Auth::user()->can('Manage Pengaturan Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.working-shift.create-edit');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->can('Manage Pengaturan Aplikasi')) {
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
        ]);

        WorkingShift::create([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'grace_period' => $request->grace_period,
            'target_type' => $request->target_type,
            'days' => $request->days,
            'is_active' => true,
        ]);

        return redirect()->route('working-shift.index')->with('success', 'Shift Presensi berhasil ditambahkan');
    }

    public function edit(WorkingShift $workingShift)
    {
        if (!Auth::user()->can('Manage Pengaturan Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.working-shift.create-edit', compact('workingShift'));
    }

    public function update(Request $request, WorkingShift $workingShift)
    {
        if (!Auth::user()->can('Manage Pengaturan Aplikasi')) {
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
        ]);

        $workingShift->update([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'grace_period' => $request->grace_period,
            'target_type' => $request->target_type,
            'days' => $request->days,
        ]);

        return redirect()->route('working-shift.index')->with('success', 'Shift Presensi berhasil diperbarui');
    }

    public function destroy(WorkingShift $workingShift)
    {
        if (!Auth::user()->can('Manage Pengaturan Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $workingShift->delete();
        return redirect()->route('working-shift.index')->with('success', 'Shift Presensi berhasil dihapus');
    }

    public function status(string $id)
    {
        if (!Auth::user()->can('Manage Pengaturan Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $shift = WorkingShift::findOrFail($id);
        $shift->update(['is_active' => !$shift->is_active]);
        return redirect()->route('working-shift.index')->with('success', 'Status shift presensi berhasil diperbarui');
    }
}
