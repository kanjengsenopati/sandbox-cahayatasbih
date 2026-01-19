<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Tahfidz;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\TahfidzRequest;

class TahfidzController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Tahfidz')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = Tahfidz::with('student')->hasSchool()->latest();
            return DataTables::of($data)
                ->editColumn('link', function ($data) {
                    return "<a href='$data->link' target='_blank'><i class='fas fa-external-link-alt'></i></a>";
                })
                ->editColumn('deposit_date', function ($data) {
                    return Carbon::parse($data->deposit_date)->format('d M Y');
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('tahfidz.edit', $data->id);
                    $actionDelete = route('tahfidz.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Tahfidz']) . '&nbsp;' .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Tahfidz']) .
                        "</div>";
                })
                ->rawColumns(['action', 'link'])
                ->make(true);
        }
        return view('admins.tahfidz.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->can('Create Tahfidz')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.tahfidz.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TahfidzRequest $request)
    {
        if (!Auth::user()->can('Create Tahfidz')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        Tahfidz::create($request->validated());
        return redirect()->route('tahfidz.index')->with('success', 'Data berhasil ditambahkan');
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
    public function edit(Tahfidz $tahfidz)
    {
        if (!Auth::user()->can('Edit Tahfidz')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.tahfidz.create-edit', compact('tahfidz'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TahfidzRequest $request, Tahfidz $tahfidz)
    {
        if (!Auth::user()->can('Edit Tahfidz')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $tahfidz->update($request->validated());
        return redirect()->route('tahfidz.index')->with('success', 'Data berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tahfidz $tahfidz)
    {
        if (!Auth::user()->can('Delete Tahfidz')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $tahfidz->delete();
        return redirect()->route('tahfidz.index')->with('success', 'Data berhasil dihapus');
    }
}
