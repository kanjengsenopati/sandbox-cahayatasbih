<?php

namespace App\Http\Controllers\Admin;

use App\Models\Outlet;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\OutletRequest;

class OutletController extends Controller
{
    public function index()
    {
        if (!Auth::user()->can('Manage Outlet')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = Outlet::latest()->get();
            return DataTables::of($data)
                ->addColumn('is_active', function ($data) {
                    return $data->is_active 
                        ? '<span class="badge bg-success">Aktif</span>' 
                        : '<span class="badge bg-danger">Nonaktif</span>';
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('outlet.edit', $data->id);
                    $actionDelete = route('outlet.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Outlet']) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Outlet']) .
                        "</div>";
                })
                ->rawColumns(['action', 'is_active'])
                ->make(true);
        }
        return view('admins.outlet.index');
    }

    public function create()
    {
        if (!Auth::user()->can('Create Outlet')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.outlet.create-edit');
    }

    public function store(OutletRequest $request)
    {
        if (!Auth::user()->can('Create Outlet')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        Outlet::create($request->validated());
        return redirect()->route('outlet.index')->with('success', 'Outlet berhasil ditambahkan');
    }

    public function edit(Outlet $outlet)
    {
        if (!Auth::user()->can('Edit Outlet')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.outlet.create-edit', compact('outlet'));
    }

    public function update(OutletRequest $request, Outlet $outlet)
    {
        if (!Auth::user()->can('Edit Outlet')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $outlet->update($request->validated());
        return redirect()->route('outlet.index')->with('success', 'Outlet berhasil diperbarui');
    }

    public function destroy(Outlet $outlet)
    {
        if (!Auth::user()->can('Delete Outlet')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $outlet->delete();
        return redirect()->route('outlet.index')->with('success', 'Outlet berhasil dihapus');
    }
}
