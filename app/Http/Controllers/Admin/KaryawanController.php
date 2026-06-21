<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\Outlet;
use App\Models\Admin;
use App\Http\Requests\Admin\KaryawanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class KaryawanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!Auth::user()->can('Manage Karyawan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if ($request->ajax()) {
            $query = Karyawan::with(['admin', 'outlet']);

            // Filter by Outlet Mode
            if ($request->input('mode') === 'outlet') {
                $outletId = $request->input('outlet_id');
                $authOutletIds = auth()->user()->getOutletIds();
                $hasOutletRestriction = count($authOutletIds) > 0;

                $koperasi = Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
                $koperasiId = $koperasi ? $koperasi->id : null;

                if ($hasOutletRestriction) {
                    $outletId = $outletId && in_array($outletId, $authOutletIds) ? $outletId : ($authOutletIds[0] ?? null);
                } else {
                    if (!$outletId) {
                        $firstOutlet = Outlet::where('is_active', 1)
                            ->when($koperasiId, function($q) use ($koperasiId) {
                                $q->where('id', '!=', $koperasiId);
                            })
                            ->orderBy('name')
                            ->first();
                        $outletId = $firstOutlet ? $firstOutlet->id : null;
                    }
                }

                if ($outletId) {
                    $query->where('outlet_id', $outletId);
                }
            }

            $query->latest();

            return DataTables::of($query)
                ->addColumn('employee_name', function ($row) {
                    return $row->admin ? $row->admin->name : '-';
                })
                ->addColumn('outlet_name', function ($row) {
                    return $row->outlet ? $row->outlet->name : '-';
                })
                ->addColumn('gaji_bulan_formatted', function ($row) {
                    return 'Rp ' . number_format($row->gaji_bulan, 0, ',', '.');
                })
                ->addColumn('gaji_hari_formatted', function ($row) {
                    return 'Rp ' . number_format($row->gaji_hari, 0, ',', '.');
                })
                ->addColumn('action', function ($row) {
                    $actionEdit = route('karyawan.edit', array_merge([$row->id], request()->only(['mode', 'outlet_id'])));
                    $actionDelete = route('karyawan.destroy', array_merge([$row->id], request()->only(['mode', 'outlet_id'])));

                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Karyawan']) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $row->id, 'name' => 'Karyawan']) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admins.karyawan.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if (!Auth::user()->can('Manage Karyawan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $outletId = $request->input('outlet_id');
        $authOutletIds = auth()->user()->getOutletIds();
        $hasOutletRestriction = count($authOutletIds) > 0;

        $koperasi = Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
        $koperasiId = $koperasi ? $koperasi->id : null;

        if ($hasOutletRestriction) {
            $outletId = $outletId && in_array($outletId, $authOutletIds) ? $outletId : ($authOutletIds[0] ?? null);
            $outlets = Outlet::whereIn('id', $authOutletIds)->orderBy('name')->get();
        } else {
            if (!$outletId) {
                $firstOutlet = Outlet::where('is_active', 1)
                    ->when($koperasiId, function($q) use ($koperasiId) {
                        $q->where('id', '!=', $koperasiId);
                    })
                    ->orderBy('name')
                    ->first();
                $outletId = $firstOutlet ? $firstOutlet->id : null;
            }
            $outlets = Outlet::where('is_active', 1)
                ->when($koperasiId, function($q) use ($koperasiId) {
                    $q->where('id', '!=', $koperasiId);
                })
                ->orderBy('name')
                ->get();
        }

        // Ambil Pengguna (Admin) yang berada di Scope Pondok Mart (Outlet)
        // dan belum terdaftar sebagai Karyawan di tabel karyawans
        $registeredAdminIds = Karyawan::pluck('admin_id')->toArray();
        
        $adminsQuery = Admin::whereNotIn('id', $registeredAdminIds);

        if ($outletId) {
            $adminsQuery->where(function($q) use ($outletId) {
                $q->where('outlet_id', $outletId)
                  ->orWhereHas('adminOutlet', function($q2) use ($outletId) {
                      $q2->where('outlet_id', $outletId);
                  });
            });
        }

        $admins = $adminsQuery->orderBy('name')->get();

        return view('admins.karyawan.create-edit', compact('outlets', 'admins', 'outletId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(KaryawanRequest $request)
    {
        if (!Auth::user()->can('Manage Karyawan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        \Illuminate\Support\Facades\DB::transaction(function() use ($request) {
            Karyawan::create($request->validated());

            // Cari Karyawan yang baru dibuat dan hubungkan ke konfigurasi gaji di payroll (jika diperlukan)
            $employee = Admin::find($request->admin_id);
            if ($employee) {
                \App\Models\EmployeeSalary::updateOrCreate(
                    [
                        'presensiable_type' => Admin::class,
                        'presensiable_id' => $employee->id,
                    ],
                    [
                        'base_salary' => $request->gaji_bulan,
                        'attendance_allowance' => $request->gaji_hari,
                        'transport_allowance' => 0,
                        'lateness_penalty_type' => 'fixed',
                        'lateness_penalty_value' => $request->potongan_terlambat ?? 0,
                        'absence_penalty' => $request->potongan_absen ?? 0,
                        'lateness_penalty_per_minute' => 0,
                    ]
                );
            }
        });

        return redirect()->route('karyawan.index', $request->only(['mode', 'outlet_id']))->with('success', 'Karyawan berhasil ditambahkan');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Karyawan $karyawan)
    {
        if (!Auth::user()->can('Manage Karyawan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $outletId = $karyawan->outlet_id;
        $authOutletIds = auth()->user()->getOutletIds();
        $hasOutletRestriction = count($authOutletIds) > 0;

        $koperasi = Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
        $koperasiId = $koperasi ? $koperasi->id : null;

        if ($hasOutletRestriction) {
            $outlets = Outlet::whereIn('id', $authOutletIds)->orderBy('name')->get();
        } else {
            $outlets = Outlet::where('is_active', 1)
                ->when($koperasiId, function($q) use ($koperasiId) {
                    $q->where('id', '!=', $koperasiId);
                })
                ->orderBy('name')
                ->get();
        }

        // Ambil Pengguna (Admin) yang berada di Scope Pondok Mart (Outlet)
        // dan belum terdaftar (kecuali karyawan yang sedang di-edit ini sendiri)
        $registeredAdminIds = Karyawan::where('id', '!=', $karyawan->id)->pluck('admin_id')->toArray();
        
        $adminsQuery = Admin::whereNotIn('id', $registeredAdminIds);

        if ($outletId) {
            $adminsQuery->where(function($q) use ($outletId) {
                $q->where('outlet_id', $outletId)
                  ->orWhereHas('adminOutlet', function($q2) use ($outletId) {
                      $q2->where('outlet_id', $outletId);
                  });
            });
        }

        $admins = $adminsQuery->orderBy('name')->get();

        return view('admins.karyawan.create-edit', compact('karyawan', 'outlets', 'admins', 'outletId'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(KaryawanRequest $request, Karyawan $karyawan)
    {
        if (!Auth::user()->can('Manage Karyawan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        \Illuminate\Support\Facades\DB::transaction(function() use ($request, $karyawan) {
            $karyawan->update($request->validated());

            // Update konfigurasi gaji di payroll
            $employee = Admin::find($request->admin_id);
            if ($employee) {
                \App\Models\EmployeeSalary::updateOrCreate(
                    [
                        'presensiable_type' => Admin::class,
                        'presensiable_id' => $employee->id,
                    ],
                    [
                        'base_salary' => $request->gaji_bulan,
                        'attendance_allowance' => $request->gaji_hari,
                        'lateness_penalty_value' => $request->potongan_terlambat ?? 0,
                        'absence_penalty' => $request->potongan_absen ?? 0,
                    ]
                );
            }
        });

        return redirect()->route('karyawan.index', $request->only(['mode', 'outlet_id']))->with('success', 'Karyawan berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Karyawan $karyawan)
    {
        if (!Auth::user()->can('Manage Karyawan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $karyawan->delete();

        return redirect()->route('karyawan.index', $request->only(['mode', 'outlet_id']))->with('success', 'Karyawan berhasil dihapus');
    }
}
