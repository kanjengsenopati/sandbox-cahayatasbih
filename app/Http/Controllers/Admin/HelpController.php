<?php

namespace App\Http\Controllers\Admin;

use App\Models\Help;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HelpRequest;

class HelpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = Help::latest()->get();
            return DataTables::of($data)
                ->editColumn('image', function ($data) {
                    return "<img src='" . asset($data->image) . "' class='img-fluid' style='max-width: 100px'>";
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('help.edit', $data->id);
                    $actionDelete = route('help.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
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
        $types = (new Help())->getTypes();
        return view('admins.help.create-edit', compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HelpRequest $request)
    {
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
        $types = $help->getTypes();
        return view('admins.help.create-edit', compact('help', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HelpRequest $request, Help $help)
    {
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
        file_exists($help->image) && unlink($help->image);
        $help->delete();
        return redirect()->route('help.index')->with('success', 'Data Bantuan berhasil dihapus');
    }
}
