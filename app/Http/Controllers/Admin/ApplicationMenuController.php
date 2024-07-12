<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\ApplicationMenu;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
        if (!Auth::user()->can('Manage Menu Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
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
                        view('components.action.status', ['action' => $actionStatus, 'status' => $data->is_active, 'id' => $data->id, 'name' => 'Menu Aplikasi']) . '&nbsp;' .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Menu Aplikasi']) . '&nbsp;' .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Menu Aplikasi']) .
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
        if (!Auth::user()->can('Create Menu Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.application-menu.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ApplicationMenuRequest $request)
    {
        if (!Auth::user()->can('Create Menu Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
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
        if (!Auth::user()->can('Edit Menu Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.application-menu.create-edit', compact('applicationMenu'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ApplicationMenuRequest $request, ApplicationMenu $applicationMenu)
    {
        if (!Auth::user()->can('Edit Menu Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $applicationMenu->update($request->validated());
        return redirect()->route('application-menu.index')->with('success', 'Menu Aplikasi berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ApplicationMenu $applicationMenu)
    {
        if (!Auth::user()->can('Delete Menu Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $applicationMenu->delete();
        return redirect()->route('application-menu.index')->with('success', 'Menu Aplikasi berhasil dihapus');
    }

    public function status(string $id)
    {
        if (!Auth::user()->can('Edit Menu Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $applicationMenu = ApplicationMenu::find($id);
        $applicationMenu->status = !$applicationMenu->status;
        $applicationMenu->save();
        return redirect()->route('application-menu.index')->with('success', 'Status Menu Aplikasi berhasil diperbarui');
    }
}
