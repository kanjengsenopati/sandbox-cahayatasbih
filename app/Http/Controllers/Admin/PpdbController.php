<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ppdb;
use App\Models\School;
use App\Models\PpdbType;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PpdbRequest;

class PpdbController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = Ppdb::with('ppdbType', 'academicYear', 'school')->where('is_active', true)->OrderBy('name', 'asc')->get();
            return DataTables::of($data)
                ->editColumn('image', function ($data) {
                    return "<img src='" . asset($data->image) . "' width='100px' />";
                })
                ->addColumn('status', function ($data) {
                    return $data->is_active ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Tidak Aktif</span>';
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('ppdb.edit', $data->id);
                    $actionDelete = route('ppdb.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
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
    public function show($slug)
    {
        $ppdb = Ppdb::where('slug', $slug)->first();
        return view('admins.ppdb.show', compact('ppdb'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ppdb $ppdb)
    {
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
        file_exists($ppdb->image) ? unlink($ppdb->image) : null;
        $ppdb->delete();
        return redirect()->route('ppdb.index')->with('success', 'Data berhasil dihapus');
    }
}
