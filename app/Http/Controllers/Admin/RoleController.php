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
                    return "<div class='d-flex gap-2 flex-nowrap justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Role']) . '&nbsp;' .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Role']) .
                        "</div>";
                })
                ->editColumn('permissions', function ($query) {
                    return $query->permissions->map(function ($permission) {
                        return "<span class='badge bg-success m-1'>{$permission->name}</span>";
                    })->implode('');
                })
                ->rawColumns(['action', 'permissions'])
                ->make(true);
        }

        return view('admins.role.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->can('Create Role')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $permissions = Permission::pluck('name', 'id')->toArray();
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

        $permissions = Permission::pluck('name', 'id')->toArray();
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
}
