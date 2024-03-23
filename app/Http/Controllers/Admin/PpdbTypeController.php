<?php

namespace App\Http\Controllers\Admin;

use App\Models\PpdbType;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PpdbTypeRequest;

class PpdbTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = PpdbType::OrderBy('name', 'asc')->get();
            return DataTables::of($data)
                ->addColumn('status', function ($data) {
                    return $data->is_active ? '<span class="badge badge-success">Aktif</span>' :
                        '<span class="badge badge-danger">Nonaktif</span>
                    ';
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('ppdb-type.edit', $data->id);
                    $actionDelete = route('ppdb-type.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        return view('admins.ppdb-type.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.ppdb-type.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PpdbTypeRequest $request)
    {
        PpdbType::create($request->validated());
        return redirect()->route('ppdb-type.index')->with('success', 'Data berhasil ditambahkan');
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
    public function edit(PpdbType $ppdbType)
    {
        return view('admins.ppdb-type.create-edit', compact('ppdbType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PpdbTypeRequest $request, PpdbType $ppdbType)
    {
        $ppdbType->update($request->validated());
        return redirect()->route('ppdb-type.index')->with('success', 'Data Berhasil Di Ubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PpdbType $ppdbType)
    {
        $ppdbType->delete();
        return redirect()->route('ppdb-type.index')->with('success', 'Data Berhasil Di Hapus');
    }
}
