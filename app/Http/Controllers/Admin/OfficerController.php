<?php

namespace App\Http\Controllers\Admin;

use App\Models\Officer;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\OfficerRequest;

class OfficerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Petugas')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = Officer::latest();
            return DataTables::of($data)
                ->addColumn('photo', function ($data) {
                    if ($data->photo) {
                        return "<img src='" . asset($data->photo) . "' class='img-fluid rounded' style='max-width: 60px; max-height: 60px; object-fit: cover;'>";
                    }
                    return "<span class='text-muted italic'>Tanpa Foto</span>";
                })
                ->addColumn('status', function ($data) {
                    return $data->is_active ? '<span class="badge badge-success">Aktif</span>'
                        : '<span class="badge badge-danger">Tidak Aktif</span>';
                })
                ->addColumn('action', function ($data) {
                    $actionStatus = route('officer.status', $data->id);
                    $actionEdit = route('officer.edit', $data->id);
                    $actionDelete = route('officer.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.status', ['action' => $actionStatus, 'status' => $data->is_active, 'id' => $data->id, 'name' => 'Petugas']) . '&nbsp;' .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Petugas']) . '&nbsp;' .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Petugas']) .
                        "</div>";
                })
                ->rawColumns(['action', 'photo', 'status'])
                ->make(true);
        }
        return view('admins.officer.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->can('Create Petugas')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.officer.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OfficerRequest $request)
    {
        if (!Auth::user()->can('Create Petugas')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        if ($request->hasFile('photo')) {
            $data['photo'] = 'storage/' . $request->file('photo')->store('images/officers', 'public');
        }
        $data['is_active'] = $request->has('is_active') ? $request->is_active : true;
        
        Officer::create($data);
        return redirect()->route('officer.index')->with('success', 'Petugas berhasil ditambahkan');
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
    public function edit(Officer $officer)
    {
        if (!Auth::user()->can('Edit Petugas')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.officer.create-edit', compact('officer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OfficerRequest $request, Officer $officer)
    {
        if (!Auth::user()->can('Edit Petugas')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        if ($request->hasFile('photo')) {
            if ($officer->photo && file_exists(public_path($officer->photo))) {
                @unlink(public_path($officer->photo));
            }
            $data['photo'] = 'storage/' . $request->file('photo')->store('images/officers', 'public');
        }
        $data['is_active'] = $request->has('is_active') ? $request->is_active : $officer->is_active;

        $officer->update($data);
        return redirect()->route('officer.index')->with('success', 'Petugas berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Officer $officer)
    {
        if (!Auth::user()->can('Delete Petugas')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if ($officer->photo && file_exists(public_path($officer->photo))) {
            @unlink(public_path($officer->photo));
        }
        $officer->delete();
        return redirect()->route('officer.index')->with('success', 'Petugas berhasil dihapus');
    }

    /**
     * Toggle active/inactive status of the officer.
     */
    public function status(string $id)
    {
        if (!Auth::user()->can('Edit Petugas')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $officer = Officer::findOrFail($id);
        $officer->is_active = !$officer->is_active;
        $officer->save();
        return redirect()->route('officer.index')->with('success', 'Status Petugas berhasil diperbarui');
    }
}
