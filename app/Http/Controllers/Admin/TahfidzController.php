<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tahfidz;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TahfidzRequest;
use Carbon\Carbon;

class TahfidzController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = Tahfidz::with('student')->latest()->get();
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
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
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
        return view('admins.tahfidz.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TahfidzRequest $request)
    {
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
        return view('admins.tahfidz.create-edit', compact('tahfidz'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TahfidzRequest $request, Tahfidz $tahfidz)
    {
        $tahfidz->update($request->validated());
        return redirect()->route('tahfidz.index')->with('success', 'Data berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tahfidz $tahfidz)
    {
        $tahfidz->delete();
        return redirect()->route('tahfidz.index')->with('success', 'Data berhasil dihapus');
    }
}
