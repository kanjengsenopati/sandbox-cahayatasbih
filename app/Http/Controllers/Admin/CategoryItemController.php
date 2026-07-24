<?php

namespace App\Http\Controllers\Admin;

use App\Models\CategoryItem;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\CategoryItemRequest;

class CategoryItemController extends Controller
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

            $data = CategoryItem::with('outlet')
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
                ->addColumn('outlet', function ($data) {
                    return $data->outlet->name ?? 'N/A';
                })
                ->addColumn('action', function ($data) {
                    $actionDelete = route('category-item.destroy', $data->id);
                    $html = "<div class='d-flex justify-content-center'>";
                    if (auth()->user()->can('Edit Barang')) {
                        $html .= "<button type='button' class='btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 btn-edit-category' " .
                            "data-id='{$data->id}' " .
                            "data-name='" . e($data->name) . "' " .
                            "data-code='" . e($data->code) . "' " .
                            "data-outlet_id='{$data->outlet_id}' " .
                            "data-action='" . route('category-item.update', $data->id) . "' title='Edit Kategori'>" .
                            "<i class='fa-solid fa-pen'></i>" .
                            "</button>";
                    }
                    if (auth()->user()->can('Delete Barang')) {
                        if (auth()->user()->can('Edit Barang')) {
                            $html .= '&nbsp;';
                        }
                        $html .= view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Kategori'])->render();
                    }
                    $html .= "</div>";
                    return $html;
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
        return view('admins.category-item.create-edit', compact('outlets'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryItemRequest $request)
    {
        if (!Auth::user()->can('Create Barang')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        if (auth()->user()->outlet_id) {
            $data['outlet_id'] = auth()->user()->outlet_id;
        } elseif (request('mode') !== 'outlet') {
            $koperasi = Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
            $data['outlet_id'] = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';
        }
        CategoryItem::create($data);
        return redirect()->route('item.index', ['mode' => request('mode')])->with('success', 'Kategori berhasil ditambahkan');
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
        if (!Auth::user()->can('Edit Barang')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (auth()->user()->outlet_id && $categoryItem->outlet_id !== auth()->user()->outlet_id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke kategori outlet lain');
        }
        $koperasi = Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
        $koperasiId = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';

        if (request('mode') === 'outlet') {
            $outlets = Outlet::where('is_active', 1)->where('id', '!=', $koperasiId)->get();
        } else {
            $outlets = Outlet::where('is_active', 1)->where('id', $koperasiId)->get();
        }
        return view('admins.category-item.create-edit', compact('categoryItem', 'outlets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryItemRequest $request, CategoryItem $categoryItem)
    {
        if (!Auth::user()->can('Edit Barang')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (auth()->user()->outlet_id && $categoryItem->outlet_id !== auth()->user()->outlet_id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke kategori outlet lain');
        }
        $data = $request->validated();
        if (auth()->user()->outlet_id) {
            $data['outlet_id'] = auth()->user()->outlet_id;
        } elseif (request('mode') !== 'outlet') {
            $koperasi = Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
            $data['outlet_id'] = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';
        }
        $categoryItem->update($data);
        return redirect()->route('item.index', ['mode' => request('mode')])->with('success', 'Kategori berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CategoryItem $categoryItem)
    {
        if (!Auth::user()->can('Delete Barang')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $categoryItem->delete();
        return redirect()->route('item.index', ['mode' => request('mode')])->with('success', 'Kategori berhasil dihapus');
    }
}
