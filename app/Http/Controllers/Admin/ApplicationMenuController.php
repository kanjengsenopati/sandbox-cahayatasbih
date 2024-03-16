<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\ApplicationMenu;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApplicationMenuRequest;

class ApplicationMenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = ApplicationMenu::latest()->get();
            return DataTables::of($data)
                ->addColumn('status', function ($data) {
                    return $data->status ? '<span class="badge badge-success">Aktif</span>'
                        : '<span class="badge badge-danger">Tidak Aktif</span>';
                })
                ->addColumn('action', function ($data) {
                    $actionStatus = route('application-menu.status', $data->id);
                    $actionEdit = route('application-menu.edit', $data->id);
                    $actionDelete = route('application-menu.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.status', ['action' => $actionStatus, 'status' => $data->is_active, 'id' => $data->id]) .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        return view('admins.application-menu.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.application-menu.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ApplicationMenuRequest $request)
    {
        ApplicationMenu::create($request->validated());
        return redirect()->route('application-menu.index')->with('success', 'Menu Aplikasi berhasil ditambahkan');
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
    public function edit(ApplicationMenu $applicationMenu)
    {
        return view('admins.application-menu.create-edit', compact('applicationMenu'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ApplicationMenuRequest $request, ApplicationMenu $applicationMenu)
    {
        $applicationMenu->update($request->validated());
        return redirect()->route('application-menu.index')->with('success', 'Menu Aplikasi berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ApplicationMenu $applicationMenu)
    {
        $applicationMenu->delete();
        return redirect()->route('application-menu.index')->with('success', 'Menu Aplikasi berhasil dihapus');
    }

    public function status(string $id)
    {
        $applicationMenu = ApplicationMenu::find($id);
        $applicationMenu->status = !$applicationMenu->status;
        $applicationMenu->save();
        return redirect()->route('application-menu.index')->with('success', 'Status Menu Aplikasi berhasil diperbarui');
    }
}
