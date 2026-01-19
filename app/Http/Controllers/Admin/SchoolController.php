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
            $data = School::latest()->get();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('school.edit', $data->id);
                    $actionDelete = route('school.destroy', $data->id);
                    $actionShow = route('school.show', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.show', ['action' => $actionShow, 'label' => 'Kelas']) .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Sekolah']) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Sekolah']) .
                        "</div>";
                })
                ->addColumn('features_display', function ($school) {
                    $features = json_decode($school->features, true) ?? [];
                    return implode(', ', $features);
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.school.index');
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
}
