<?php

namespace App\Http\Controllers\Admin;

use App\Models\Study;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudyRequest;

class StudyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = Study::latest()->get();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('study.edit', $data->id);
                    $actionDelete = route('study.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.study.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.study.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudyRequest $request)
    {
        $data = $request->validated();
        Study::create($data);
        return redirect()->route('study.index')->with('success', 'Mata Pelajaran berhasil ditambahkan');
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
    public function edit(Study $study)
    {
        return view('admins.study.create-edit', compact('study'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudyRequest $request, Study $study)
    {
        $data = $request->validated();
        $study->update($data);
        return redirect()->route('study.index')->with('success', 'Mata Pelajaran berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Study $study)
    {
        $study->delete();
        return redirect()->route('study.index')->with('success', 'Mata Pelajaran berhasil dihapus');
    }
}
