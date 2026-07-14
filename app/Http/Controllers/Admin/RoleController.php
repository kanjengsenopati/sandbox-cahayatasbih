<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        // $this->middleware(['permission:role']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Role')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            $data = Role::with('permissions')->latest();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('role.edit', $data->id);
                    $actionDelete = route('role.destroy', $data->id);

                    $admins = collect();
                    if ($data->guard_name === 'web' || $data->guard_name === 'api') {
                        try {
                            $admins = \App\Models\Admin::role($data->name)->pluck('id')->toArray();
                        } catch (\Throwable $e) {}
                    }

                    $assignBtn = "<button type='button' class='btn btn-icon btn-active-light-primary w-30px h-30px me-1 btn-assign-user' data-id='{$data->id}' data-name='{$data->name}' data-users='".json_encode($admins)."'>
                        <i class='fa-solid fa-user-gear text-primary fs-5'></i>
                    </button>";

                    return "<div class='d-flex gap-2 flex-nowrap justify-content-center align-items-center'>" .
                        $assignBtn .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Role']) . '&nbsp;' .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Role']) .
                        "</div>";
                })
                ->editColumn('permissions', function ($query) {
                    $permissionsHtml = $query->permissions->map(function ($permission) {
                        return "<span class='badge bg-success m-1'>{$permission->name}</span>";
                    })->implode('');

                    $admins = collect();
                    if ($query->guard_name === 'web' || $query->guard_name === 'api') {
                        try {
                            $admins = \App\Models\Admin::role($query->name)->get();
                        } catch (\Throwable $e) {}
                    }

                    $walis = collect();
                    if ($query->guard_name === 'wali') {
                        try {
                            $walis = \App\Models\User::role($query->name)->get();
                        } catch (\Throwable $e) {}
                    }

                    $users = $admins->concat($walis);

                    $usersHtml = '';
                    if ($users->count() > 0) {
                        $usersBadges = $users->map(function ($user) {
                            return "<span class='badge' style='background-color: #8b5cf6; color: white;'>{$user->name}</span>";
                        })->implode('');
                        $usersHtml = "<div class='mt-2 border-top pt-2 d-flex flex-wrap align-items-center gap-1'><span class='text-muted me-1' style='font-size: 11px; font-weight: 600;'>User:</span><div class='d-flex flex-wrap gap-1'>{$usersBadges}</div></div>";
                    } else {
                        $usersHtml = "<div class='mt-2 border-top pt-2'><span class='text-muted italic' style='font-size: 11px;'>Belum ada user yang ditugaskan</span></div>";
                    }

                    return "<div class='d-flex flex-column'>" .
                        "<div class='d-flex flex-wrap'>{$permissionsHtml}</div>" .
                        $usersHtml .
                        "</div>";
                })
                ->rawColumns(['action', 'permissions'])
                ->make(true);
        }

        $allAdmins = \App\Models\Admin::orderBy('name')->get();
        return view('admins.role.index', compact('allAdmins'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->can('Create Role')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $permissions = Permission::where('guard_name', 'web')->pluck('name', 'id')->toArray();
        $permissionValue = [];

        return view('admins.role.create-edit', compact('permissions', 'permissionValue'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('Create Role')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $request->validate([
            'name' => 'required',
            'permissions' => 'required|array'
        ]);

        DB::beginTransaction();

        try {
            $role = Role::create($request->only('name'));

            if ($request->permissions) {
                $role->givePermissionTo($request->permissions);
            }

            DB::commit();
            return redirect('role')->with('success', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return redirect('role')->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Implementasi jika diperlukan
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if (!Auth::user()->can('Edit Role')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $permissions = Permission::where('guard_name', 'web')->pluck('name', 'id')->toArray();
        $role = Role::with('permissions')->findOrFail($id);
        $permissionValue = $role->permissions->pluck('id')->toArray();

        return view('admins.role.create-edit', compact('role', 'permissions', 'permissionValue'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        if (!Auth::user()->can('Edit Role')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $request->validate([
            'name' => 'required',
            'permissions' => 'required|array'
        ]);

        DB::beginTransaction();

        try {
            $role->update($request->only('name'));
            $role->syncPermissions($request->permissions);

            DB::commit();
            return redirect('role')->with('success', 'Data berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect('role')->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        if (!Auth::user()->can('Delete Role')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $role->delete();
        return back()->with('success', 'Data berhasil dihapus');
    }

    public function assignUsers(Request $request, $id)
    {
        if (!Auth::user()->can('Manage Role')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk tindakan ini');
        }

        $role = Role::findOrFail($id);
        $request->validate([
            'admin_ids' => 'array',
        ]);

        $selectedAdminIds = $request->admin_ids ?? [];

        // Get all admins who currently have this role
        $currentAdmins = \App\Models\Admin::role($role->name)->get();

        // For admins who currently have it but are not in the new list, remove it
        foreach ($currentAdmins as $admin) {
            if (!in_array($admin->id, $selectedAdminIds)) {
                $admin->removeRole($role->name);
            }
        }

        // For selected admins who do not have it, assign it
        if (!empty($selectedAdminIds)) {
            $selectedAdmins = \App\Models\Admin::whereIn('id', $selectedAdminIds)->get();
            foreach ($selectedAdmins as $admin) {
                if (!$admin->hasRole($role->name)) {
                    $admin->assignRole($role->name);
                }
            }
        }

        return back()->with('success', 'User berhasil ditugaskan ke Peran ' . $role->name);
    }
}
