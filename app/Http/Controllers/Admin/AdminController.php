<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\School;
use App\Models\AdminSchool;
use App\Models\AdminOutlet;
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
                    
                    $impersonateBtn = '';
                    if ($data->id !== Auth::id()) {
                        $actionImpersonate = route('admin.impersonate', $data->id);
                        $csrf = csrf_field();
                        $impersonateBtn = "
                            <form action='{$actionImpersonate}' method='POST' class='d-inline' style='display:inline;'>
                                {$csrf}
                                <button type='submit' class='btn btn-icon btn-active-light-success w-30px h-30px' title='Login As (Impersonate)' style='border: none; background: transparent;'>
                                    <i class='fas fa-user-secret text-success fs-5'></i>
                                </button>
                            </form>
                        ";
                    }
                    
                    return "<div class='d-flex gap-2 flex-nowrap justify-content-center align-items-center'>" .
                        $impersonateBtn .
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
        $outlets = \App\Models\Outlet::where('is_active', true)->orderBy('name')->get();
        $adminSchools = [];
        $adminOutlets = [];
        return view('admins.admin.create-edit', compact('roles', 'schools', 'outlets', 'adminSchools', 'adminOutlets'));
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

            $data = $request->except(['password', 'role_ids', 'admin_outlets']);
            !empty($request->password) ? $data['password'] = bcrypt($request->password) : '';

            if (!empty($avatar = $request->avatar)) {
                $data['avatar'] = 'storage/' . $avatar->store('images/avatars', ['disk' => 'public']);
            }

            // Legacy support: set single role_id field to the first role ID in the array
            if ($request->role_ids && count($request->role_ids) > 0) {
                $data['role_id'] = $request->role_ids[0];
            }

            // Set outlet_id utama dari pilihan pertama multi-outlet
            if ($request->admin_outlets && is_array($request->admin_outlets) && count($request->admin_outlets) > 0) {
                $data['outlet_id'] = $request->admin_outlets[0];
            } else {
                $data['outlet_id'] = null;
            }

            $admin = Admin::create($data);

            if ($request->role_ids) {
                $roles = Role::whereIn('id', $request->role_ids)->pluck('name')->toArray();
                $admin->assignRole($roles);
            }

            if ($request->admin_schools && is_array($request->admin_schools)) {
                foreach ($request->admin_schools as $school) {
                    AdminSchool::create([
                        'admin_id' => $admin->id,
                        'school_id' => $school
                    ]);
                }
            }

            // Simpan multi-outlet pivot
            if ($request->admin_outlets && is_array($request->admin_outlets)) {
                foreach ($request->admin_outlets as $outletId) {
                    AdminOutlet::create([
                        'admin_id' => $admin->id,
                        'outlet_id' => $outletId
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
        $outlets = \App\Models\Outlet::where('is_active', true)->orderBy('name')->get();
        $adminSchools = $admin->adminSchool->pluck('school_id')->toArray();
        $adminOutlets = $admin->adminOutlet->pluck('outlet_id')->toArray();
        $adminRoles = $admin->roles->pluck('id')->toArray();
        return view('admins.admin.create-edit', compact('admin', 'roles', 'schools', 'outlets', 'adminSchools', 'adminOutlets', 'adminRoles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AdminRequest $request, Admin $admin)
    {
        if (!Auth::user()->can('Edit Admin')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->except(['password', 'role_ids', 'admin_outlets']);

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

        // Legacy support: set single role_id field to the first role ID in the array
        if ($request->role_ids && count($request->role_ids) > 0) {
            $data['role_id'] = $request->role_ids[0];
        }

        // Set outlet_id utama dari pilihan pertama multi-outlet
        if ($request->admin_outlets && is_array($request->admin_outlets) && count($request->admin_outlets) > 0) {
            $data['outlet_id'] = $request->admin_outlets[0];
        } else {
            $data['outlet_id'] = null;
        }

        $admin->update($data);

        if ($request->role_ids) {
            $roles = Role::whereIn('id', $request->role_ids)->pluck('name')->toArray();
            $admin->syncRoles($roles);
        }

        if ($request->admin_schools) {
            $admin->adminSchool()->delete();
            foreach ($request->admin_schools as $school) {
                AdminSchool::create([
                    'admin_id' => $admin->id,
                    'school_id' => $school
                ]);
            }
        }

        // Sync multi-outlet pivot
        $admin->adminOutlet()->delete();
        if ($request->admin_outlets && is_array($request->admin_outlets)) {
            foreach ($request->admin_outlets as $outletId) {
                AdminOutlet::create([
                    'admin_id' => $admin->id,
                    'outlet_id' => $outletId
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
        $admin->adminOutlet()->delete();
        $admin->delete();
        return redirect()->route('admin.index')->with('success', 'Berhasil menghapus admin');
    }

    public function impersonate(Admin $admin)
    {
        if (!Auth::user()->can('Manage Admin')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk tindakan ini');
        }

        if ($admin->id === Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat meng-impersonate diri sendiri');
        }

        $impersonatorId = Auth::id();
        session(['impersonator_backoffice_id' => $impersonatorId]);

        Auth::login($admin);

        return redirect('/dashboard')->with('success', 'Berhasil masuk sebagai ' . $admin->name);
    }

    public function stopImpersonating()
    {
        $impersonatorId = session('impersonator_backoffice_id');
        if (!$impersonatorId) {
            return redirect('/');
        }

        $originalAdmin = Admin::find($impersonatorId);
        if ($originalAdmin) {
            Auth::login($originalAdmin);
            session()->forget('impersonator_backoffice_id');
            return redirect()->route('admin.index')->with('success', 'Kembali ke sesi Admin Asli.');
        }

        return redirect('/');
    }

    public function scopeAkses()
    {
        if (!Auth::user()->can('Manage Admin')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $allAdmins = Admin::orderBy('name')->get();
        
        $scopes = [
            [
                'id' => 'backoffice',
                'name' => 'Backoffice (Panel Web Saja)',
                'users' => Admin::where('access_scope', 'backoffice')->get()
            ],
            [
                'id' => 'pwa',
                'name' => 'PWA Mobile (Aplikasi HP Saja)',
                'users' => Admin::where('access_scope', 'pwa')->get()
            ],
            [
                'id' => 'both',
                'name' => 'Keduanya (Backoffice & PWA)',
                'users' => Admin::where('access_scope', 'both')->get()
            ]
        ];

        return view('admins.admin.scope-akses', compact('allAdmins', 'scopes'));
    }

    public function assignScopeUsers(\Illuminate\Http\Request $request, $scope)
    {
        if (!Auth::user()->can('Manage Admin')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk tindakan ini');
        }

        $request->validate([
            'admin_ids' => 'array',
        ]);

        $selectedIds = $request->admin_ids ?? [];

        // Set all admins currently with this scope to 'both' if not in selectedIds
        Admin::where('access_scope', $scope)
            ->whereNotIn('id', $selectedIds)
            ->update(['access_scope' => 'both']);

        // Set selected admins to this scope
        if (!empty($selectedIds)) {
            Admin::whereIn('id', $selectedIds)->update(['access_scope' => $scope]);
        }

        return back()->with('success', 'User berhasil ditugaskan ke Scope Akses.');
    }
}

