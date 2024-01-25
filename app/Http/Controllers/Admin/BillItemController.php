<?php

namespace App\Http\Controllers\Admin;

use App\Models\BillItem;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BillItemRequest;

class BillItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = BillItem::orderBy('name')->get();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('bill-item.edit', $data->id);
                    $actionDelete = route('bill-item.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.bill-item.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.bill-item.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BillItemRequest $request)
    {
        BillItem::create($request->validated());
        return redirect()->route('bill-item.index')->with('success', 'Pos Bayar berhasil ditambahkan');
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
    public function edit(BillItem $billItem)
    {
        return view('admins.bill-item.create-edit', compact('billItem'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BillItemRequest $request, BillItem $billItem)
    {
        $billItem->update($request->validated());
        return redirect()->route('bill-item.index')->with('success', 'Pos Bayar berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BillItem $billItem)
    {
        $billItem->delete();
        return redirect()->route('bill-item.index')->with('success', 'Pos Bayar berhasil dihapus');
    }
}
