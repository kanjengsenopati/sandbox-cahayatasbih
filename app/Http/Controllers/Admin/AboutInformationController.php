<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\AboutInformation;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AboutInformationRequest;

class AboutInformationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:content');
    }


    public function index()
    {
        $data = AboutInformation::latest()->get();
        $total = $data->count();
        if (request()->ajax()) {
            return DataTables::of($data)
                ->addColumn('image', function ($data) {
                    return $data->image ? '<img src="' . asset($data->image) . '" class="img-fluid" width="100px">' : '';
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('about-information.edit', $data->id);
                    $actionDelete = route('about-information.destroy', $data->id);
                    return "<div class='d-flex gap-2 flex-nowrap'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action', 'image'])
                ->make(true);
        }
        return view('admins.about.information.index', compact('total'));
    }

    public function create()
    {
        return view('admins.about.information.create-edit');
    }

    public function store(AboutInformationRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = 'storage/' . $request->file('image')->store('images/about', 'public');
        }
        AboutInformation::create($data);
        foreach (['history_data_en', 'history_data'] as $key) {
            if (cache()->has($key)) {
                cache()->forget($key);
            }
        }
        return redirect()->route('about-information.index')->with('success', 'Informasi Berhasil Ditambahkan');
    }

    public function edit($id)
    {
        $aboutInformation = AboutInformation::findOrFail($id);
        return view('admins.about.information.create-edit', compact('aboutInformation'));
    }

    public function update(AboutInformationRequest $request, $id)
    {
        $aboutInformation = AboutInformation::findOrFail($id);
        $data = $request->validated();
        if ($request->hasFile('image')) {
            // Delete old image
            if (file_exists($aboutInformation->image)) {
                unlink($aboutInformation->image);
            }
            $data['image'] = 'storage/' . $request->file('image')->store('images/about', 'public');
        }
        $aboutInformation->update($data);
        foreach (['history_data_en', 'history_data'] as $key) {
            if (cache()->has($key)) {
                cache()->forget($key);
            }
        }
        return redirect()->route('about-information.index')->with('success', 'Informasi Berhasil Diubah');
    }

    public function destroy($id)
    {
        $aboutInformation = AboutInformation::findOrFail($id);
        if (file_exists($aboutInformation->image)) {
            unlink($aboutInformation->image);
        }
        $aboutInformation->delete();
        foreach (['history_data_en', 'history_data'] as $key) {
            if (cache()->has($key)) {
                cache()->forget($key);
            }
        }
        return redirect()->route('about-information.index')->with('success', 'Informasi Berhasil Dihapus');
    }
}
