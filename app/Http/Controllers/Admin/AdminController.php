<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\School;
use App\Models\AdminSchool;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\AdminRequest;

class AdminController extends Controller
{
    public function __construct()
    {
        // $this->middleware(['permission:admin']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Admin')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = Admin::with('roles', 'adminSchool')->latest();
            return DataTables::of($data)
                ->addColumn('role', function ($query) {
                    $role = "";
                    foreach ($query->roles as $value) {
                        $role .= "<span class='badge bg-success m-1'>{$value->name}</span>";
                    }
                    return $role;
                })
                ->addColumn('school', function ($query) {
                    $school = "";
                    foreach ($query->adminSchool as $value) {
                        $school .= "<span class='badge bg-success m-1'>{$value?->school?->name}</span>";
                    }
                    return $school;
                })
                ->addColumn('access_scope', function ($query) {
                    $scope = $query->access_scope ?? 'both';
                    if ($scope === 'backoffice') {
                        return '<span class="badge badge-light-primary">Backoffice</span>';
                    } elseif ($scope === 'pwa') {
                        return '<span class="badge badge-light-success">PWA Mobile</span>';
                    } else {
                        return '<span class="badge badge-light-info">Keduanya</span>';
                    }
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('admin.edit', $data->id);
                    $actionDelete = route('admin.destroy', $data->id);
                    return "<div class='d-flex gap-2 flex-nowrap justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Admin']) . '&nbsp;' .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Admin']) .
                        "</div>";
                })
                ->rawColumns(['action', 'role', 'school', 'access_scope'])
                ->make(true);
        }
        return view('admins.admin.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->can('Create Admin')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $roles = Role::get();
        $schools = School::orderBy('name')->get();
        $adminSchools = [];
        return view('admins.admin.create-edit', compact('roles', 'schools', 'adminSchools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AdminRequest $request)
    {
        if (!Auth::user()->can('Create Admin')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        try {
            DB::beginTransaction();

            $data = $request->except('password');
            !empty($request->password) ? $data['password'] = bcrypt($request->password) : '';

            if (!empty($avatar = $request->avatar)) {
                $data['avatar'] = 'storage/' . $avatar->store('images/avatars', ['disk' => 'public']);
            }

            $admin = Admin::create($data);
            $admin->assignRole(Role::find($request->role_id)->name);
            if ($request->admin_schools && is_array($request->admin_schools)) {
                foreach ($request->admin_schools as $school) {
                    AdminSchool::create([
                        'admin_id' => $admin->id,
                        'school_id' => $school
                    ]);
                }
            }

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
        if (!Auth::user()->can('Edit Admin')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $roles = Role::get();
        $schools = School::orderBy('name')->get();
        $adminSchools = $admin->adminSchool->pluck('school_id')->toArray();
        return view('admins.admin.create-edit', compact('admin', 'roles', 'schools', 'adminSchools'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AdminRequest $request, Admin $admin)
    {
        if (!Auth::user()->can('Edit Admin')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->except('password');

        // check email is unique
        $existAdmin = Admin::where('email', $request->email)->where('id', '!=', $admin->id)->first();
        if ($existAdmin) {
            return redirect()->back()->with('error', 'Email sudah digunakan');
        }

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
        if ($request->admin_schools) {
            $admin->adminSchool()->delete();
            foreach ($request->admin_schools as $school) {
                AdminSchool::create([
                    'admin_id' => $admin->id,
                    'school_id' => $school
                ]);
            }
        }
        return redirect()->route('admin.index')->with('success', 'Berhasil mengubah data');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Admin $admin)
    {
        if (!Auth::user()->can('Delete Admin')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        file_exists($admin->avatar) ? unlink($admin->avatar) : '';
        $admin->adminSchool()->delete();
        $admin->delete();
        return redirect()->route('admin.index')->with('success', 'Berhasil menghapus admin');
    }
}
