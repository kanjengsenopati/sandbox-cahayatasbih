<?php

namespace App\Http\Controllers\Admin;

use App\Models\Information;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\InformationCategory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\InformationRequest;

class InformationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Informasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = Information::with('informationCategory')->latest();
            return DataTables::of($data)
                ->editColumn('image', function ($data) {
                    return "<img src='" . asset($data->image) . "' class='img-fluid' style='max-width: 100px'>";
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('information.edit', $data->id);
                    $actionDelete = route('information.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Informasi']) . '&nbsp;' .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Informasi']) .
                        "</div>";
                })
                ->rawColumns(['action', 'image'])
                ->make(true);
        }
        return view('admins.information.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->can('Create Informasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $informationCategories = InformationCategory::orderBy('name')->get();
        return view('admins.information.create-edit', compact('informationCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InformationRequest $request)
    {
        if (!Auth::user()->can('Create Informasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = 'storage/' . $request->file('image')->store('images/information', 'public');
        }
        Information::create($data);
        return redirect()->route('information.index')
            ->with('success', 'Informasi berhasil ditambahkan');
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
    public function edit(Information $information)
    {
        if (!Auth::user()->can('Edit Informasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $informationCategories = InformationCategory::orderBy('name')->get();
        return view('admins.information.create-edit', compact(
            'information',
            'informationCategories'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InformationRequest $request, Information $information)
    {
        if (!Auth::user()->can('Edit Informasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        if ($request->hasFile('image')) {
            file_exists($information->image) ? unlink($information->image) : null;
            $data['image'] = 'storage/' . $request->file('image')->store('images/information', 'public');
        }
        $information->update($data);
        return redirect()->route('information.index')
            ->with('success', 'Informasi berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Information $information)
    {
        if (!Auth::user()->can('Delete Informasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        file_exists($information->image) ? unlink($information->image) : null;
        $information->delete();
        return redirect()->route('information.index')
            ->with('success', 'Informasi berhasil dihapus');
    }
}
