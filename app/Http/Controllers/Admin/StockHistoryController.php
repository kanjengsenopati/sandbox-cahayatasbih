<?php

namespace App\Http\Controllers\Admin;

use App\Models\Item;
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
            $data = StockHistory::with(['item', 'admin'])->latest();
            return DataTables::of($data)
                ->addColumn('admin', function ($data) {
                    return $data->admin->name ?? 'Belum Ada Admin';
                })
                ->addColumn('item_category', function ($data) {
                    return $data->item->categoryItem->name ?? 'Belum Ada Kategori';
                })
                ->editColumn('quantity', function ($data) {
                    return $data->type == StockHistory::TYPE_IN ? '<span class="text-success">+' . $data->quantity . '</span>' : '<span class="text-danger">-' . $data->quantity . '</span>';
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('stock-history.edit', $data->id);
                    $actionDelete = route('stock-history.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Barang']) . '&nbsp;' .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Barang']) .
                        "</div>";
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
        return view('admins.stock-history.create-edit');
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

            return redirect()->route('stock-history.index')->with('success', 'Data Stok Berhasil Ditambahkan');
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
        return view('admins.stock-history.create-edit', compact('stockHistory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StockHistoryRequest $request, StockHistory $stockHistory)
    {
        if (!Auth::user()->can('Edit Barang')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['admin_id'] = auth()->user()->id;
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

            return redirect()->route('stock-history.index')->with('success', 'Data Stok Berhasil Diubah');
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

            return redirect()->route('stock-history.index')->with('success', 'Data Stok Berhasil Dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            // Handle the exception (log, redirect, etc.)
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data stok.');
        }
    }
}
