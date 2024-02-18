<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\InformationCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InformationCategoryRequest;

class InformationCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = InformationCategory::latest()->get();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('information-category.edit', $data->id);
                    $actionDelete = route('information-category.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
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
        return view('admins.information-category.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InformationCategoryRequest $request)
    {
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
        return view('admins.information-category.create-edit', compact('informationCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InformationCategoryRequest $request, InformationCategory $informationCategory)
    {
        $informationCategory->update($request->validated());
        return redirect()->route('information-category.index')
            ->with('success', 'Kategori informasi berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InformationCategory $informationCategory)
    {
        $informationCategory->delete();
        return redirect()->route('information-category.index')
            ->with('success', 'Kategori informasi berhasil dihapus');
    }
}
