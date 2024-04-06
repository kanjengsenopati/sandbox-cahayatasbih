<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\MenuNavigation;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MenuNavigationRequest;
use App\Models\SubMenuNavigation;

class MenuNavigationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = MenuNavigation::orderBy('order')->get();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('menu-navigation.edit', $data->id);
                    $actionDelete = route('menu-navigation.destroy', $data->id);
                    $actionShow = route('menu-navigation.show', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.show', ['action' => $actionShow, 'label' => 'SubMenu']) .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.menu-navigation.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.menu-navigation.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MenuNavigationRequest $request)
    {
        MenuNavigation::create($request->validated());
        return redirect()->route('menu-navigation.index')->with('success', 'Menu Navigasi berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (request()->ajax()) {
            $data = SubMenuNavigation::where('menu_navigation_id', $id)->orderBy('order')->get();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('submenu-navigation.edit', $data->id);
                    $actionDelete = route('submenu-navigation.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $menuNavigation = MenuNavigation::findOrFail($id);
        return view('admins.menu-navigation.submenu.index', compact('menuNavigation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MenuNavigation $menuNavigation)
    {
        return view('admins.menu-navigation.create-edit', compact('menuNavigation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MenuNavigationRequest $request, MenuNavigation $menuNavigation)
    {
        $menuNavigation->update($request->validated());
        return redirect()->route('menu-navigation.index')->with('success', 'Menu Navigasi berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MenuNavigation $menuNavigation)
    {
        $menuNavigation->delete();
        return redirect()->route('menu-navigation.index')->with('success', 'Menu Navigasi berhasil dihapus');
    }
}
