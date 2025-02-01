<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\CashFlowCategory;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\CashFlowCategoryRequest;

class CashFlowCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Kategori Arus Kas')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = CashFlowCategory::latest();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('cashflow-category.edit', $data->id);
                    $actionDelete = route('cashflow-category.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Arus Kas']) . '&nbsp;' .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Arus Kas']) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.cashflow-category.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.cashflow-category.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CashFlowCategoryRequest $request)
    {
        CashFlowCategory::create($request->validated());
        return redirect()->route('cashflow-category.index')->with('success', 'Kategori berhasil ditambahkan');
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
    public function edit(CashFlowCategory $cashflowCategory)
    {
        return view('admins.cashflow-category.create-edit', compact('cashflowCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CashFlowCategoryRequest $request, CashFlowCategory $cashflowCategory)
    {
        $cashflowCategory->update($request->validated());
        return redirect()->route('cashflow-category.index')->with('success', 'Kategori berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CashFlowCategory $cashflowCategory)
    {
        $cashflowCategory->delete();
        return redirect()->route('cashflow-category.index')->with('success', 'Kategori berhasil dihapus');
    }
}
