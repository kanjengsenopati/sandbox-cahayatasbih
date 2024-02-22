<?php

namespace App\Http\Controllers\Admin;

use App\Models\Item;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ItemRequest;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = Item::with('categoryItem')->get();
            return DataTables::of($data)
                ->addColumn('status', function ($data) {
                    return $data->is_active == 1 ? '<span class="badge badge-success">Aktif</span>' :
                        '<span class="badge badge-danger">Tidak Aktif</span>';
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('item.edit', $data->id);
                    $actionDelete = route('item.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action', 'status', 'summary_price'])
                ->make(true);
        }
        return view('admins.item.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.item.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ItemRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = 'storage/' . $request->file('image')->store('images/item', 'public');
        }
        Item::create($data);
        return redirect()->route('item.index')->with('success', 'Barang berhasil ditambahkan');
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
    public function edit(Item $item)
    {
        return view('admins.item.create-edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ItemRequest $request, Item $item)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            file_exists($item->image) ? unlink($item->image) : null;
            $data['image'] = 'storage/' . $request->file('image')->store('images/item', 'public');
        }
        $item->update($data);
        return redirect()->route('item.index')->with('success', 'Barang berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        file_exists($item->image) ? unlink($item->image) : null;
        $item->delete();
        return redirect()->route('item.index')->with('success', 'Barang berhasil dihapus');
    }

    public function searchItem(Request $request)
    {
        if ($request->type == 'CODE') {
            return $this->searchItemCode($request);
        } else {
            return $this->searchItemName($request);
        }
    }

    public function searchItemCode(Request $request)
    {
        $item = Item::whereCode($request->search)->whereIsActive(true)->first();
        if (!$item) {
            return $this->postSuccessResponse("Produk tidak ditemukan", null);
        }
        return $this->postSuccessResponse("Berhasil mengambil data", $item);
    }

    public function searchItemName(Request $request)
    {
        $searchTerm = strtolower($request->search);

        $items = Item::whereIsActive(true)
            ->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%'])
            ->get();

        if ($items->isEmpty()) {
            return $this->postSuccessResponse("Produk tidak ditemukan", null);
        }

        return $this->postSuccessResponse("Berhasil mengambil data", $items);
    }
}
