<?php

namespace App\Http\Controllers\Admin;

use App\Models\Help;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\HelpRequest;

class HelpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Kontak Bantuan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = Help::latest();
            return DataTables::of($data)
                ->editColumn('image', function ($data) {
                    return "<img src='" . asset($data->image) . "' class='img-fluid' style='max-width: 100px'>";
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('help.edit', $data->id);
                    $actionDelete = route('help.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Kontak Bantuan']) . '&nbsp;' .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Kontak Bantuan']) .
                        "</div>";
                })
                ->rawColumns(['action', 'image'])
                ->make(true);
        }
        return view('admins.help.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->can('Create Kontak Bantuan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $types = (new Help())->getTypes();
        return view('admins.help.create-edit', compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HelpRequest $request)
    {
        if (!Auth::user()->can('Create Kontak Bantuan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = 'storage/' . $request->file('image')->store('images/help', 'public');
        }
        Help::create($data);
        return redirect()->route('help.index')->with('success', 'Data Bantuan berhasil ditambahkan');
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
    public function edit(Help $help)
    {
        if (!Auth::user()->can('Edit Kontak Bantuan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $types = $help->getTypes();
        return view('admins.help.create-edit', compact('help', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HelpRequest $request, Help $help)
    {
        if (!Auth::user()->can('Edit Kontak Bantuan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        if ($request->hasFile('image')) {
            file_exists($help->image) && unlink($help->image);
            $data['image'] = 'storage/' . $request->file('image')->store('images/help', 'public');
        }
        $help->update($data);
        return redirect()->route('help.index')->with('success', 'Data Bantuan berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Help $help)
    {
        if (!Auth::user()->can('Delete Kontak Bantuan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        file_exists($help->image) && unlink($help->image);
        $help->delete();
        return redirect()->route('help.index')->with('success', 'Data Bantuan berhasil dihapus');
    }
}
