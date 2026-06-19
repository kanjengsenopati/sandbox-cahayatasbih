<?php

namespace App\Http\Controllers\Admin;

use App\Models\School;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\SchoolRequest;

class SchoolController extends Controller
{

    public function __construct()
    {
        // $this->middleware('permission:school');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Sekolah')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = School::with('adminSchool.admin')->latest()->get();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('school.edit', $data->id);
                    $actionDelete = route('school.destroy', $data->id);
                    $actionShow = route('school.show', $data->id);

                    $assignedAdminIds = $data->adminSchool->pluck('admin_id')->toArray();
                    $assignBtn = "<button type='button' class='btn btn-icon btn-active-light-primary w-30px h-30px me-1 btn-assign-user' data-id='{$data->id}' data-name='{$data->name}' data-users='".json_encode($assignedAdminIds)."'>
                        <i class='fa-solid fa-user-gear text-primary fs-5'></i>
                    </button>";

                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.show', ['action' => $actionShow, 'label' => 'Kelas']) .
                        $assignBtn .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Sekolah']) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Sekolah']) .
                        "</div>";
                })
                ->addColumn('users', function ($school) {
                    $admins = $school->adminSchool->map(fn($as) => $as->admin)->filter();
                    if ($admins->count() > 0) {
                        $badges = $admins->map(function ($admin) {
                            return "<span class='badge' style='background-color: #8b5cf6; color: white;'>{$admin->name}</span>";
                        })->implode('');
                        return '<div class="d-flex flex-wrap gap-1">' . $badges . '</div>';
                    }
                    return "<span class='text-muted italic' style='font-size: 11px;'>Belum ada user yang ditugaskan</span>";
                })
                ->addColumn('features_display', function ($school) {
                    $features = json_decode($school->features, true) ?? [];
                    return implode(', ', $features);
                })
                ->rawColumns(['action', 'users'])
                ->make(true);
        }
        $allAdmins = \App\Models\Admin::orderBy('name')->get();
        return view('admins.school.index', compact('allAdmins'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->can('Create Sekolah')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $types = School::getListType();
        return view('admins.school.create-edit', compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SchoolRequest $request)
    {
        if (!Auth::user()->can('Create Sekolah')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        $data['slug'] = Str::slug($request->name);
        // check if slug exists
        $count = School::where('slug', $data['slug'])->count();
        if ($count > 0) {
            $data['slug'] = $data['slug'] . '-' . ($count + 1);
        }
        $data['features'] = json_encode(array_filter($request->features ?? [], fn($f) => !empty(trim($f))));
        School::create($data);
        return redirect()->route('school.index')->with('success', 'Sekolah berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!Auth::user()->can('Manage Sekolah')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $school = School::findOrFail($id);
        if (request()->ajax()) {
            $data = $school->classroom()->latest();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('classroom.edit', $data->id);
                    $actionDelete = route('classroom.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Sekolah']) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Sekolah']) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.school.classroom.index', compact('school'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(School $school)
    {
        if (!Auth::user()->can('Edit Sekolah')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $types = School::getListType();
        return view('admins.school.create-edit', compact('school', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SchoolRequest $request, School $school)
    {
        if (!Auth::user()->can('Edit Sekolah')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        $data['slug'] = Str::slug($request->name);
        // check if slug exists
        $count = School::where('slug', $data['slug'])->where('id', '!=', $school->id)->count();
        if ($count > 0) {
            $data['slug'] = $data['slug'] . '-' . ($count + 1);
        }
        $data['features'] = json_encode(array_filter($request->features ?? [], fn($f) => !empty(trim($f))));
        $school->update($data);
        return redirect()->route('school.index')->with('success', 'Data sekolah berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school)
    {
        if (!Auth::user()->can('Delete Sekolah')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $school->delete();
        return redirect()->route('school.index')->with('success', 'Sekolah berhasil dihapus');
    }

    public function assignUsers(Request $request, $id)
    {
        if (!Auth::user()->can('Manage Sekolah')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk tindakan ini');
        }

        $school = School::findOrFail($id);
        $request->validate([
            'admin_ids' => 'array',
        ]);

        \App\Models\AdminSchool::where('school_id', $id)->forceDelete();
        if ($request->admin_ids) {
            foreach ($request->admin_ids as $adminId) {
                \App\Models\AdminSchool::create([
                    'admin_id' => $adminId,
                    'school_id' => $id
                ]);
            }
        }
        return back()->with('success', 'User berhasil ditugaskan ke UPT ' . $school->name);
    }
}
