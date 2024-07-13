<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ppdb;
use App\Models\School;
use App\Models\PpdbType;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PpdbRegistration;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\PpdbRequest;

class PpdbController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage PPDB')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = Ppdb::with('ppdbType', 'academicYear', 'school')->where('is_active', true)->OrderBy('name', 'asc');
            return DataTables::of($data)
                ->editColumn('image', function ($data) {
                    return "<img src='" . asset($data->image) . "' width='100px' />";
                })
                ->addColumn('status', function ($data) {
                    return $data->is_active ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Tidak Aktif</span>';
                })
                ->addColumn('action', function ($data) {
                    $actionShow = route('ppdb.show', $data->id);
                    $actionEdit = route('ppdb.edit', $data->id);
                    $actionDelete = route('ppdb.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.show', ['action' => $actionShow, 'label' => 'Peserta']) .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'PPDB']) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'PPDB']) .
                        "</div>";
                })
                ->rawColumns(['action', 'image', 'status'])
                ->make(true);
        }
        return view('admins.ppdb.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->can('Create PPDB')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.ppdb.create-edit', [
            'ppdbTypes' => PpdbType::where('is_active', true)->get(),
            'schools' => School::orderBy('name', 'asc')->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PpdbRequest $request)
    {
        if (!Auth::user()->can('Create PPDB')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = 'storage/' . $request->file('image')->store('images/ppdb', 'public');
        }
        $data['slug'] = Str::slug($data['name']);
        Ppdb::create($data);
        return redirect()->route('ppdb.index')->with('success', 'Data berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        if (!Auth::user()->can('Manage PPDB')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = PpdbRegistration::with('ppdb')->where('ppdb_id', $id)->latest()->get();
            return DataTables::of($data)
                ->addColumn('student_name', function ($data) {
                    return $data->ppdbStudents->first()->name ?? '-';
                })
                ->addColumn('gender', function ($data) {
                    $gender = $data->ppdbStudents->first()->gender ?? '-';
                    return $gender == 'L' ? 'Laki-laki' : 'Perempuan';
                })
                ->addColumn('origin_school', function ($data) {
                    return $data->ppdbStudents?->first()?->origin_school ?? '-';
                })
                ->addColumn('action', function ($data) {
                    $actionPay = route('ppdb-registration.show', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.show', ['action' => $actionPay, 'label' => 'Detail']) .
                        "</div>";
                })
                ->rawColumns(['action', 'ppdb_name', 'school_name', 'student_name'])
                ->make(true);
        }
        $ppdb = Ppdb::findorFail($id);
        return view('admins.ppdb.show', compact('ppdb'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ppdb $ppdb)
    {
        if (!Auth::user()->can('Edit PPDB')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.ppdb.create-edit', [
            'ppdb' => $ppdb,
            'ppdbTypes' => PpdbType::where('is_active', true)->get(),
            'schools' => School::orderBy('name', 'asc')->get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PpdbRequest $request, Ppdb $ppdb)
    {
        if (!Auth::user()->can('Edit PPDB')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = 'storage/' . $request->file('image')->store('images/ppdb', 'public');
        }
        $ppdb->update($data);
        return redirect()->route('ppdb.index')->with('success', 'Data berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ppdb $ppdb)
    {
        if (!Auth::user()->can('Delete PPDB')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        file_exists($ppdb->image) ? unlink($ppdb->image) : null;
        $ppdb->delete();
        return redirect()->route('ppdb.index')->with('success', 'Data berhasil dihapus');
    }
}
