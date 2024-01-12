<?php

namespace App\Http\Controllers\Admin;

use App\Models\School;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
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
        if (request()->ajax()) {
            $data = School::latest()->get();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('school.edit', $data->id);
                    $actionDelete = route('school.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
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
        return view('admins.school.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SchoolRequest $request)
    {
        School::create($request->validated());
        return redirect()->route('school.index')->with('success', 'Sekolah berhasil ditambahkan');
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
    public function edit(School $school)
    {
        return view('admins.school.create-edit', compact('school'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SchoolRequest $request, School $school)
    {
        $school->update($request->validated());
        return redirect()->route('school.index')->with('success', 'Sekolah berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school)
    {
        $school->delete();
        return redirect()->route('school.index')->with('success', 'Sekolah berhasil dihapus');
    }
}
