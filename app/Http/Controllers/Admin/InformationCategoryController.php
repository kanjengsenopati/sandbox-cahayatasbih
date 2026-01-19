<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\InformationCategory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\InformationCategoryRequest;

class InformationCategoryController extends Controller
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
            $data = InformationCategory::latest();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('information-category.edit', $data->id);
                    $actionDelete = route('information-category.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Informasi']) . '&nbsp;' .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Informasi']) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.information-category.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->can('Create Informasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.information-category.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InformationCategoryRequest $request)
    {
        if (!Auth::user()->can('Create Informasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        InformationCategory::create($request->validated());
        return redirect()->route('information-category.index')
            ->with('success', 'Kategori informasi berhasil ditambahkan');
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
    public function edit(InformationCategory $informationCategory)
    {
        if (!Auth::user()->can('Edit Informasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.information-category.create-edit', compact('informationCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InformationCategoryRequest $request, InformationCategory $informationCategory)
    {
        if (!Auth::user()->can('Edit Informasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $informationCategory->update($request->validated());
        return redirect()->route('information-category.index')
            ->with('success', 'Kategori informasi berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InformationCategory $informationCategory)
    {
        if (!Auth::user()->can('Delete Informasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $informationCategory->delete();
        return redirect()->route('information-category.index')
            ->with('success', 'Kategori informasi berhasil dihapus');
    }
}
