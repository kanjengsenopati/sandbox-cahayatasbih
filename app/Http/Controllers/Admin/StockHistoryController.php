<?php

namespace App\Http\Controllers\Admin;

use App\Models\Item;
use App\Models\Outlet;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\StockHistoryRequest;

class StockHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Barang')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $koperasi = Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
            $koperasiId = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';

            $data = StockHistory::with(['item', 'admin', 'outlet'])
                ->when(auth()->user()->outlet_id, function($q) {
                    $q->where('outlet_id', auth()->user()->outlet_id);
                })
                ->when(!auth()->user()->outlet_id, function($q) use ($koperasiId) {
                    if (request('mode') === 'outlet') {
                        if (request()->filled('outlet_id')) {
                            $q->where('outlet_id', request('outlet_id'));
                        } else {
                            $q->where('outlet_id', '!=', $koperasiId);
                        }
                    } else {
                        $q->where('outlet_id', $koperasiId);
                    }
                })
                ->latest();
            return DataTables::of($data)
                ->addColumn('admin', function ($data) {
                    return $data->admin->name ?? 'Belum Ada Admin';
                })
                ->addColumn('outlet', function ($data) {
                    return $data->outlet->name ?? 'N/A';
                })
                ->addColumn('item_category', function ($data) {
                    return $data->item->categoryItem->name ?? 'Belum Ada Kategori';
                })
                ->editColumn('quantity', function ($data) {
                    return $data->type == StockHistory::TYPE_IN ? '<span class="text-success">+' . $data->quantity . '</span>' : '<span class="text-danger">-' . $data->quantity . '</span>';
                })
                ->addColumn('action', function ($data) {
                    $actionDelete = route('stock-history.destroy', $data->id);
                    $html = "<div class='d-flex justify-content-center'>";
                    if (auth()->user()->can('Edit Barang')) {
                        $html .= "<button type='button' class='btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 btn-edit-stock' " .
                            "data-id='{$data->id}' " .
                            "data-type='{$data->type}' " .
                            "data-item_id='{$data->item_id}' " .
                            "data-quantity='{$data->quantity}' " .
                            "data-outlet_id='{$data->outlet_id}' " .
                            "data-action='" . route('stock-history.update', $data->id) . "' title='Edit Stok'>" .
                            "<i class='fa-solid fa-pen'></i>" .
                            "</button>";
                    }
                    if (auth()->user()->can('Delete Barang')) {
                        if (auth()->user()->can('Edit Barang')) {
                            $html .= '&nbsp;';
                        }
                        $html .= view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Stok'])->render();
                    }
                    $html .= "</div>";
                    return $html;
                })
                ->rawColumns(['action', 'quantity'])
                ->make(true);
        }
        return view('admins.stock-history.index');
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
        return view('admins.stock-history.create-edit', compact('outlets'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(StockHistoryRequest $request)
    {
        if (!Auth::user()->can('Create Barang')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['admin_id'] = auth()->user()->id;
            if (auth()->user()->outlet_id) {
                $data['outlet_id'] = auth()->user()->outlet_id;
            } elseif (request('mode') !== 'outlet') {
                $koperasi = Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
                $data['outlet_id'] = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';
            }
            $item = Item::findOrFail($data['item_id']);

            if ($data['type'] == StockHistory::TYPE_OUT) {
                if ($item->stock < $data['quantity']) {
                    return redirect()->back()->with('error', 'Stok Tidak Mencukupi');
                }
                $item->stock -= $data['quantity'];
            } else {
                $item->stock += $data['quantity'];
            }

            $item->save();

            StockHistory::create($data);

            DB::commit();

            return redirect()->route('item.index', ['mode' => request('mode')])->with('success', 'Data Stok Berhasil Ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            // Handle the exception (log, redirect, etc.)
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data stok.');
        }
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
    public function edit(StockHistory $stockHistory)
    {
        if (!Auth::user()->can('Edit Barang')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (auth()->user()->outlet_id && $stockHistory->outlet_id !== auth()->user()->outlet_id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke data stok outlet lain');
        }
        $koperasi = Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
        $koperasiId = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';

        if (request('mode') === 'outlet') {
            $outlets = Outlet::where('is_active', 1)->where('id', '!=', $koperasiId)->get();
        } else {
            $outlets = Outlet::where('is_active', 1)->where('id', $koperasiId)->get();
        }
        return view('admins.stock-history.create-edit', compact('stockHistory', 'outlets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StockHistoryRequest $request, StockHistory $stockHistory)
    {
        if (!Auth::user()->can('Edit Barang')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (auth()->user()->outlet_id && $stockHistory->outlet_id !== auth()->user()->outlet_id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke data stok outlet lain');
        }
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['admin_id'] = auth()->user()->id;
            if (auth()->user()->outlet_id) {
                $data['outlet_id'] = auth()->user()->outlet_id;
            } elseif (request('mode') !== 'outlet') {
                $koperasi = Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
                $data['outlet_id'] = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';
            }
            $item = Item::findOrFail($data['item_id']);

            if ($data['type'] == StockHistory::TYPE_OUT) {
                if ($item->stock < $data['quantity']) {
                    return redirect()->back()->with('error', 'Stok Tidak Mencukupi');
                }
                $item->stock -= $data['quantity'];
            } else {
                $item->stock += $data['quantity'];
            }

            $item->save();

            $stockHistory->update($data);

            DB::commit();

            return redirect()->route('item.index', ['mode' => request('mode')])->with('success', 'Data Stok Berhasil Diubah');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            // Handle the exception (log, redirect, etc.)
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data stok.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockHistory $stockHistory)
    {
        if (!Auth::user()->can('Delete Barang')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (auth()->user()->outlet_id && $stockHistory->outlet_id !== auth()->user()->outlet_id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke data stok outlet lain');
        }
        try {
            DB::beginTransaction();

            $item = Item::findOrFail($stockHistory->item_id);

            if ($stockHistory->type == StockHistory::TYPE_OUT) {
                $item->stock += $stockHistory->quantity;
            } else {
                $item->stock -= $stockHistory->quantity;
            }

            $item->save();

            $stockHistory->delete();

            DB::commit();

            if (request('mode') === 'outlet') {
                return redirect()->route('stock-history.index', ['mode' => 'outlet'])->with('success', 'Data Stok Berhasil Dihapus');
            } else {
                return redirect()->route('item.index', ['mode' => 'kantin'])->with('success', 'Data Stok Berhasil Dihapus');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            // Handle the exception (log, redirect, etc.)
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data stok.');
        }
    }
}
