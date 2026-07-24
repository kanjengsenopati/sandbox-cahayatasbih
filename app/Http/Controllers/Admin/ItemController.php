<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Item;
use App\Models\Outlet;
use App\Imports\ItemImport;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\Admin\ItemRequest;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Barang')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $user = auth()->user();
        if ($user->isKasirKoperasi() && request('mode') === 'outlet') {
            return redirect()->route('item.index', ['mode' => 'kantin']);
        }
        if ($user->isKasirOutlet() && request('mode') === 'kantin') {
            return redirect()->route('item.index', ['mode' => 'outlet']);
        }

        if (request()->ajax()) {
            $koperasi = Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
            $koperasiId = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';

            $user = auth()->user();
            $authOutletIds = array_diff($user->getOutletIds(), [$koperasiId]);

            $data = Item::with(['categoryItem', 'outlet'])
                ->when(request('mode') === 'outlet', function($q) use ($authOutletIds, $koperasiId) {
                    if (request()->filled('outlet_id')) {
                        $q->where('outlet_id', request('outlet_id'));
                    } elseif (!empty($authOutletIds)) {
                        $q->whereIn('outlet_id', $authOutletIds);
                    } else {
                        $q->where(function($query) use ($koperasiId) {
                            $query->where('outlet_id', '!=', $koperasiId)
                                  ->orWhereNull('outlet_id');
                        });
                    }
                })
                ->when(request('mode') !== 'outlet', function($q) use ($koperasiId) {
                    $q->where(function($query) use ($koperasiId) {
                        $query->where('outlet_id', $koperasiId)
                              ->orWhereNull('outlet_id');
                    });
                })
                ->latest();
            return DataTables::of($data)
                ->addColumn('category', function ($data) {
                    return $data->categoryItem->name ?? 'N/A';
                })
                ->addColumn('outlet', function ($data) {
                    return $data->outlet->name ?? 'N/A';
                })
                ->addColumn('action', function ($data) {
                    $actionDelete = route('item.destroy', $data->id);
                    $html = "<div class='d-flex justify-content-center'>";
                    if (auth()->user()->can('Edit Barang')) {
                        $imgUrl = $data->image ? asset($data->image) : '';
                        $html .= "<button type='button' class='btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 btn-edit-item' " .
                            "data-id='{$data->id}' " .
                            "data-name='" . e($data->name) . "' " .
                            "data-code='" . e($data->code) . "' " .
                            "data-category_item_id='{$data->category_item_id}' " .
                            "data-price='{$data->price}' " .
                            "data-selling_price='{$data->selling_price}' " .
                            "data-profit='{$data->profit}' " .
                            "data-stock='{$data->stock}' " .
                            "data-outlet_id='{$data->outlet_id}' " .
                            "data-image='{$imgUrl}' " .
                            "data-action='" . route('item.update', $data->id) . "' title='Edit Barang'>" .
                            "<i class='fa-solid fa-pen'></i>" .
                            "</button>";
                    }
                    if (auth()->user()->can('Delete Barang')) {
                        if (auth()->user()->can('Edit Barang')) {
                            $html .= '&nbsp;';
                        }
                        $html .= view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Barang'])->render();
                    }
                    $html .= "</div>";
                    return $html;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.item.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->can('Create Barang')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $koperasi = Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
        $koperasiId = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';

        if (request('mode') === 'outlet') {
            $outlets = Outlet::where('is_active', 1)->where('id', '!=', $koperasiId)->get();
        } else {
            $outlets = Outlet::where('is_active', 1)->where('id', $koperasiId)->get();
        }
        return view('admins.item.create-edit', compact('outlets'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ItemRequest $request)
    {
        if (!Auth::user()->can('Create Barang')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        $data['stock'] = 0; // Single Source of Truth: Initial stock set to 0. Stock must be added via Inventori (StockHistory).

        if ($request->hasFile('image')) {
            $data['image'] = 'storage/' . $request->file('image')->store('images/item', 'public');
        }
        if (auth()->user()->outlet_id) {
            $data['outlet_id'] = auth()->user()->outlet_id;
        } elseif (request('mode') !== 'outlet') {
            $koperasi = Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
            $data['outlet_id'] = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';
        }
        Item::create($data);
        return redirect()->route('item.index', ['mode' => request('mode')])->with('success', 'Barang berhasil ditambahkan. Silakan isi stok pada tab Inventori Barang.');
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
        if (!Auth::user()->can('Edit Barang')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (auth()->user()->outlet_id && $item->outlet_id !== auth()->user()->outlet_id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke barang outlet lain');
        }
        $koperasi = Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
        $koperasiId = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';

        if (request('mode') === 'outlet') {
            $outlets = Outlet::where('is_active', 1)->where('id', '!=', $koperasiId)->get();
        } else {
            $outlets = Outlet::where('is_active', 1)->where('id', $koperasiId)->get();
        }
        return view('admins.item.create-edit', compact('item', 'outlets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ItemRequest $request, Item $item)
    {
        if (!Auth::user()->can('Edit Barang')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (auth()->user()->outlet_id && $item->outlet_id !== auth()->user()->outlet_id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke barang outlet lain');
        }
        $data = $request->validated();
        unset($data['stock']); // Single Source of Truth: Prevent manual stock override from Edit Barang modal

        if ($request->hasFile('image')) {
            file_exists($item->image) ? unlink($item->image) : null;
            $data['image'] = 'storage/' . $request->file('image')->store('images/item', ['disk' => 'public']);
        }
        if (auth()->user()->outlet_id) {
            $data['outlet_id'] = auth()->user()->outlet_id;
        } elseif (request('mode') !== 'outlet') {
            $koperasi = Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
            $data['outlet_id'] = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';
        }
        $item->update($data);
        // Invalidate the cache for the top 10 items
        Cache::forget('top_10_items_last_month');
        return redirect()->route('item.index', ['mode' => request('mode')])->with('success', 'Barang berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        if (!Auth::user()->can('Delete Barang')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (auth()->user()->outlet_id && $item->outlet_id !== auth()->user()->outlet_id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke barang outlet lain');
        }
        file_exists($item->image) ? unlink($item->image) : null;
        $item->delete();

        // Invalidate the cache for the top 10 items
        Cache::forget('top_10_items_last_month');
        return redirect()->route('item.index', ['mode' => request('mode')])->with('success', 'Barang berhasil dihapus');
    }

    public function searchItem(Request $request)
    {
        $outletId = auth()->user()->getEffectiveOutletId($request->mode, $request->outlet_id);

        $koperasi = \App\Models\Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
        $koperasiId = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';

        if (!$request->search) {
            $items = Item::with('categoryItem')->where('stock', '>', 0)
                ->when($outletId, function($q) use ($outletId, $koperasiId) {
                    $q->where(function($query) use ($outletId, $koperasiId) {
                        $query->where('outlet_id', $outletId);
                        if ($outletId === $koperasiId) {
                            $query->orWhereNull('outlet_id');
                        }
                    });
                })
                ->where('is_active', true)
                ->orderBy('stock', 'asc') // Order by stock in ascending order
                ->get();

            return $this->postSuccessResponse("Berhasil mengambil data", $items);
        } else {
            if ($request->type == 'CODE') {
                return $this->searchItemCode($request);
            } else {
                return $this->searchItemName($request);
            }
        }
    }

    public function searchItemCode(Request $request)
    {
        $outletId = auth()->user()->getEffectiveOutletId($request->mode, $request->outlet_id);

        $koperasi = \App\Models\Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
        $koperasiId = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';

        $item = Item::whereCode($request->search)
            ->whereIsActive(true)
            ->when($outletId, function($q) use ($outletId, $koperasiId) {
                $q->where(function($query) use ($outletId, $koperasiId) {
                    $query->where('outlet_id', $outletId);
                    if ($outletId === $koperasiId) {
                        $query->orWhereNull('outlet_id');
                    }
                });
            })
            ->first();
        if (!$item) {
            return $this->postSuccessResponse("Produk tidak ditemukan", null);
        }
        return $this->postSuccessResponse("Berhasil mengambil data", $item);
    }

    public function searchItemName(Request $request)
    {
        $searchTerm = strtolower($request->search);
        $outletId = auth()->user()->getEffectiveOutletId($request->mode, $request->outlet_id);

        $koperasi = \App\Models\Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
        $koperasiId = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';

        $items = Item::with('categoryItem')->whereIsActive(true)
            ->when($outletId, function($q) use ($outletId, $koperasiId) {
                $q->where(function($query) use ($outletId, $koperasiId) {
                    $query->where('outlet_id', $outletId);
                    if ($outletId === $koperasiId) {
                        $query->orWhereNull('outlet_id');
                    }
                });
            })
            ->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%'])
            ->get();

        if ($items->isEmpty()) {
            return $this->postSuccessResponse("Produk tidak ditemukan", null);
        }

        return $this->postSuccessResponse("Berhasil mengambil data", $items);
    }

    public function import(Request $request)
    {
        if (!Auth::user()->can('Create Barang')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        try {
            $request->validate([
                'file' => 'required|mimes:xls,xlsx'
            ]);

            return DB::transaction(function () use ($request) {
                Excel::import(new ItemImport, $request->file('file'));
                return redirect()->route('item.index')->with('success', 'Data berhasil diimport');
            });
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Import failed: ' . $e->getMessage());

            // Return with an error message or handle the exception as needed
            return redirect()->back()->with('error', 'Data gagal diimport');
        }
    }
}
