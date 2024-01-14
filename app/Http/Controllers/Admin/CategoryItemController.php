<?php

namespace App\Http\Controllers\Admin;

use App\Models\CategoryItem;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryItemRequest;

class CategoryItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = CategoryItem::latest()->get();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('category-item.edit', $data->id);
                    $actionDelete = route('category-item.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.category-item.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.category-item.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryItemRequest $request)
    {
        CategoryItem::create($request->validated());
        return redirect()->route('category-item.index')->with('success', 'Kategori berhasil ditambahkan');
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
    public function edit(CategoryItem $categoryItem)
    {
        return view('admins.category-item.create-edit', compact('categoryItem'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryItemRequest $request, CategoryItem $categoryItem)
    {
        $categoryItem->update($request->validated());
        return redirect()->route('category-item.index')->with('success', 'Kategori berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CategoryItem $categoryItem)
    {
        $categoryItem->delete();
        return redirect()->route('category-item.index')->with('success', 'Kategori berhasil dihapus');
    }
}
