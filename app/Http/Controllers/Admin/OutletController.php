<?php

namespace App\Http\Controllers\Admin;

use App\Models\Outlet;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\OutletRequest;

class OutletController extends Controller
{
    public function index()
    {
        if (!Auth::user()->can('Manage Outlet')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = Outlet::with('adminOutlet.admin')->latest()->get();
            return DataTables::of($data)
                ->addColumn('is_active', function ($data) {
                    return $data->is_active 
                        ? '<span class="badge bg-success">Aktif</span>' 
                        : '<span class="badge bg-danger">Nonaktif</span>';
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('outlet.edit', $data->id);
                    $actionDelete = route('outlet.destroy', $data->id);

                    $assignedAdminIds = $data->adminOutlet->pluck('admin_id')->toArray();
                    $assignBtn = "<button type='button' class='btn btn-icon btn-active-light-primary w-30px h-30px me-1 btn-assign-user' data-id='{$data->id}' data-name='{$data->name}' data-users='".json_encode($assignedAdminIds)."'>
                        <i class='fa-solid fa-user-gear text-primary fs-5'></i>
                    </button>";

                    return "<div class='d-flex justify-content-center'>" .
                        $assignBtn .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Outlet']) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Outlet']) .
                        "</div>";
                })
                ->addColumn('users', function ($outlet) {
                    $admins = $outlet->adminOutlet->map(fn($ao) => $ao->admin)->filter();
                    if ($admins->count() > 0) {
                        return $admins->map(function ($admin) {
                            return "<span class='badge m-1' style='background-color: #8b5cf6; color: white;'>{$admin->name}</span>";
                        })->implode('');
                    }
                    return "<span class='text-muted italic' style='font-size: 11px;'>Belum ada user yang ditugaskan</span>";
                })
                ->rawColumns(['action', 'is_active', 'users'])
                ->make(true);
        }
        $allAdmins = \App\Models\Admin::orderBy('name')->get();
        return view('admins.outlet.index', compact('allAdmins'));
    }

    public function create()
    {
        if (!Auth::user()->can('Create Outlet')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.outlet.create-edit');
    }

    public function store(OutletRequest $request)
    {
        if (!Auth::user()->can('Create Outlet')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        Outlet::create($request->validated());
        return redirect()->route('outlet.index')->with('success', 'Outlet berhasil ditambahkan');
    }

    public function edit(Outlet $outlet)
    {
        if (!Auth::user()->can('Edit Outlet')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.outlet.create-edit', compact('outlet'));
    }

    public function update(OutletRequest $request, Outlet $outlet)
    {
        if (!Auth::user()->can('Edit Outlet')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $outlet->update($request->validated());
        return redirect()->route('outlet.index')->with('success', 'Outlet berhasil diperbarui');
    }

    public function destroy(Outlet $outlet)
    {
        if (!Auth::user()->can('Delete Outlet')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $outlet->delete();
        return redirect()->route('outlet.index')->with('success', 'Outlet berhasil dihapus');
    }

    public function assignUsers(Request $request, $id)
    {
        if (!Auth::user()->can('Manage Outlet')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk tindakan ini');
        }

        $outlet = Outlet::findOrFail($id);
        $request->validate([
            'admin_ids' => 'array',
        ]);

        \App\Models\AdminOutlet::where('outlet_id', $id)->delete();
        if ($request->admin_ids) {
            foreach ($request->admin_ids as $adminId) {
                \App\Models\AdminOutlet::create([
                    'admin_id' => $adminId,
                    'outlet_id' => $id
                ]);
            }
        }
        return back()->with('success', 'User berhasil ditugaskan ke Outlet ' . $outlet->name);
    }
}
