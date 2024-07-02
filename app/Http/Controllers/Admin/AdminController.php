<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\School;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminRequest;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:admin']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Admin::with('roles')->latest()->get();
        if (request()->ajax()) {
            return DataTables::of($data)
                ->addColumn('role', function ($query) {
                    $role = "";
                    foreach ($query->roles as $value) {
                        $role .= "<span class='badge bg-success m-1'>{$value->name}</span>";
                    }
                    return $role;
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('admin.edit', $data->id);
                    $actionDelete = route('admin.destroy', $data->id);
                    return "<div class='d-flex gap-2 flex-nowrap justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action', 'role'])
                ->make(true);
        }
        return view('admins.admin.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::get();
        $schools = School::orderBy('name')->get();
        return view('admins.admin.create-edit', compact('roles', 'schools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AdminRequest $request)
    {

        try {
            DB::beginTransaction();

            $data = $request->except('password');
            !empty($request->password) ? $data['password'] = bcrypt($request->password) : '';

            if (!empty($avatar = $request->avatar)) {
                $data['avatar'] = 'storage/' . $avatar->store('images/avatars', ['disk' => 'public']);
            }

            $admin = Admin::create($data);
            $admin->assignRole(Role::find($request->role_id)->name);

            DB::commit();

            return redirect()->route('admin.index')->with('success', 'Berhasil menambah admin');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            // Handle the exception, log it, or return an error response
            return redirect()->back()->with('error', 'Gagal menambah admin: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Admin $admin)
    {
        $roles = Role::get();
        $schools = School::orderBy('name')->get();
        return view('admins.admin.create-edit', compact('admin', 'roles', 'schools'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AdminRequest $request, Admin $admin)
    {
        $data = $request->except('password');

        if (!empty($request->password)) {
            $data['password'] = bcrypt($request->password);
        }

        if (!empty($avatar = $request->avatar)) {
            if (file_exists($admin->avatar)) {
                unlink($admin->avatar);
            }
            $data['avatar'] = 'storage/' . $avatar->store('images/avatars', ['disk' => 'public']);
        }

        $admin->update($data);
        $admin->syncRoles(Role::find($request->role_id)->name);
        return redirect()->route('admin.index')->with('success', 'Berhasil mengubah data');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Admin $admin)
    {
        file_exists($admin->avatar) ? unlink($admin->avatar) : '';
        $admin->delete();
        return redirect()->route('admin.index')->with('success', 'Berhasil menghapus admin');
    }
}
