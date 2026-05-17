<?php

namespace App\Http\Controllers\Admin;

use App\Models\StudentPermit;
use App\Models\Student;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class StudentPermitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Perizinan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            $data = StudentPermit::with('student', 'user')->latest();
            return DataTables::of($data)
                ->addColumn('student_name', function ($row) {
                    return $row->student ? $row->student->name : '-';
                })
                ->addColumn('parent_name', function ($row) {
                    return $row->user ? $row->user->name : '-';
                })
                ->addColumn('planned_exit', function ($row) {
                    return $row->planned_exit_date ? date('d-m-Y H:i', strtotime($row->planned_exit_date)) : '-';
                })
                ->addColumn('planned_return', function ($row) {
                    return $row->planned_return_date ? date('d-m-Y H:i', strtotime($row->planned_return_date)) : '-';
                })
                ->addColumn('status_badge', function ($row) {
                    $badges = [
                        'pending' => '<span class="badge badge-warning">Menunggu</span>',
                        'approved' => '<span class="badge badge-indigo" style="background-color: #4f46e5; color: white;">Disetujui</span>',
                        'rejected' => '<span class="badge badge-danger">Ditolak</span>',
                        'out' => '<span class="badge badge-info">Sedang Keluar</span>',
                        'returned' => '<span class="badge badge-success">Kembali</span>',
                    ];
                    return $badges[$row->status] ?? '<span class="badge badge-secondary">' . $row->status . '</span>';
                })
                ->addColumn('btnAction', function ($row) {
                    $actionShow = route('student-permit.show', $row->id);
                    $btn = "<a href='{$actionShow}' class='btn btn-sm btn-outline-primary mr-1' title='Detail'><i class='fa fa-eye'></i> Detail</a>";
                    return "<div class='d-flex justify-content-center'>{$btn}</div>";
                })
                ->rawColumns(['status_badge', 'btnAction'])
                ->make(true);
        }

        return view('admins.student-permit.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!Auth::user()->can('Manage Perizinan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $permit = StudentPermit::with('student', 'user')->findOrFail($id);
        return view('admins.student-permit.show', compact('permit'));
    }

    /**
     * Update the specified resource in storage. (Approval / Rejection Action)
     */
    public function update(Request $request, string $id)
    {
        if (!Auth::user()->can('Approve Perizinan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $permit = StudentPermit::findOrFail($id);
        $action = $request->input('action'); // 'approve', 'reject', 'return'

        if ($action === 'approve') {
            $permit->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            return redirect()->route('student-permit.show', $id)->with('success', 'Pengizinan santri berhasil disetujui');
        } elseif ($action === 'reject') {
            $request->validate([
                'rejection_reason' => 'required|string|max:255',
            ]);
            $permit->update([
                'status' => 'rejected',
                'rejection_reason' => $request->input('rejection_reason'),
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            return redirect()->route('student-permit.show', $id)->with('success', 'Pengizinan santri telah ditolak');
        } elseif ($action === 'return') {
            $permit->update([
                'status' => 'returned',
                'actual_return_date' => now(),
            ]);
            return redirect()->route('student-permit.show', $id)->with('success', 'Santri telah dinyatakan kembali ke pondok');
        }

        return redirect()->back()->with('error', 'Aksi tidak valid');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!Auth::user()->can('Manage Perizinan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $permit = StudentPermit::findOrFail($id);
        $permit->delete();

        return redirect()->route('student-permit.index')->with('success', 'Data perizinan berhasil dihapus');
    }
}
