<?php

namespace App\Http\Controllers\Admin;

use App\Models\BillItem;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\BillItemRequest;

class BillItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Item Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            $data = BillItem::orderBy('name');
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('bill-item.edit', $data->id);
                    $actionDelete = route('bill-item.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Item Bayar']) . '&nbsp;' .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Item Bayar']) .
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
        if (!Auth::user()->can('Create Item Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.bill-item.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BillItemRequest $request)
    {
        if (!Auth::user()->can('Create Item Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
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
        if (!Auth::user()->can('Edit Item Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.bill-item.create-edit', compact('billItem'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BillItemRequest $request, BillItem $billItem)
    {
        if (!Auth::user()->can('Edit Item Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $billItem->update($request->validated());
        return redirect()->route('bill-item.index')->with('success', 'Pos Bayar berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BillItem $billItem)
    {
        if (!Auth::user()->can('Delete Item Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $billItem->delete();
        return redirect()->route('bill-item.index')->with('success', 'Pos Bayar berhasil dihapus');
    }
}
