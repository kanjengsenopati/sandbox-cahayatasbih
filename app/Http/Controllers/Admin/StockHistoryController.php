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

            $user = auth()->user();
            $authOutletIds = array_diff($user->getOutletIds(), [$koperasiId]);

            $data = Item::with(['categoryItem', 'outlet', 'stockHistories' => function($q) {
                    $q->latest()->with('admin');
                }])
                ->when(!empty($authOutletIds) && ($user->isKasirOutlet() || request('mode') === 'outlet'), function($q) use ($authOutletIds) {
                    if (request()->filled('outlet_id') && in_array(request('outlet_id'), $authOutletIds)) {
                        $q->where('outlet_id', request('outlet_id'));
                    } else {
                        $q->whereIn('outlet_id', $authOutletIds);
                    }
                })
                ->when(empty($authOutletIds) && ($user->outlet_id), function($q) use ($koperasiId) {
                    $outletId = auth()->user()->outlet_id;
                    $q->where(function($query) use ($outletId, $koperasiId) {
                        $query->where('outlet_id', $outletId);
                        if ($outletId === $koperasiId) {
                            $query->orWhereNull('outlet_id');
                        }
                    });
                })
                ->when(empty($authOutletIds) && !$user->outlet_id, function($q) use ($koperasiId) {
                    if (request('mode') === 'outlet') {
                        if (request()->filled('outlet_id')) {
                            $q->where('outlet_id', request('outlet_id'));
                        } else {
                            $q->where(function($query) use ($koperasiId) {
                                $query->where('outlet_id', '!=', $koperasiId)
                                      ->orWhereNull('outlet_id');
                            });
                        }
                    } else {
                        $q->where(function($query) use ($koperasiId) {
                            $query->where('outlet_id', $koperasiId)
                                  ->orWhereNull('outlet_id');
                        });
                    }
                })
                ->latest();

            return DataTables::of($data)
                ->addColumn('admin', function ($item) {
                    $latestHistory = $item->stockHistories->first();
                    return $latestHistory->admin->name ?? 'System';
                })
                ->addColumn('outlet', function ($item) {
                    return $item->outlet->name ?? 'N/A';
                })
                ->addColumn('current_stock', function ($item) {
                    return $item->stock ?? 0;
                })
                ->addColumn('initial_stock', function ($item) {
                    $firstHistory = $item->stockHistories->sortBy('created_at')->first();
                    $initialVal = $firstHistory ? $firstHistory->quantity : $item->stock;
                    return 'Stok Awal: ' . $initialVal;
                })
                ->addColumn('notes', function ($item) {
                    $latestHistory = $item->stockHistories->first();
                    return $latestHistory ? ($latestHistory->notes ?? '-') : 'Inisialisasi Master Item';
                })
                ->addColumn('action', function ($item) {
                    $html = "<div class='d-flex justify-content-center'>";
                    if (auth()->user()->can('Create Barang') || auth()->user()->can('Edit Barang')) {
                        $html .= "<button type='button' class='btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 btn-add-stock-item' " .
                            "data-item_id='{$item->id}' " .
                            "data-item_name='" . e($item->name) . "' " .
                            "data-outlet_id='{$item->outlet_id}' " .
                            "title='Tambah / Opname Stok'>" .
                            "<i class='fa-solid fa-pen'></i>" .
                            "</button>";
                    }
                    $html .= "</div>";
                    return $html;
                })
                ->rawColumns(['action'])
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

            if ($data['type'] == StockHistory::TYPE_ADJUSTMENT) {
                $data['notes'] = $request->input('notes') ?? 'Stok Opname Physical Count';
                $item->stock = $data['quantity'];
            } elseif ($data['type'] == StockHistory::TYPE_OUT) {
                if ($item->stock < $data['quantity']) {
                    return redirect()->back()->with('error', 'Stok Tidak Mencukupi');
                }
                $item->stock -= $data['quantity'];
                $data['notes'] = $request->input('notes') ?? 'Stok Keluar';
            } else {
                $item->stock += $data['quantity'];
                $data['notes'] = $request->input('notes') ?? 'Stok Masuk';
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
