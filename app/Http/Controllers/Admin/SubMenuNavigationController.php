<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SubMenuNavigationRequest;
use App\Models\SubMenuNavigation;
use Illuminate\Http\Request;

class SubMenuNavigationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.menu-navigation.submenu.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SubMenuNavigationRequest $request)
    {
        $data = $request->validated();
        $data['menu_navigation_id'] = $request->menu_navigation_id;
        $subMenuNavigation = SubMenuNavigation::create($data);
        return redirect()->route('menu-navigation.show', $subMenuNavigation->menu_navigation_id)
            ->with('success', 'Sub Menu berhasil ditambahkan');
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
    public function edit($id)
    {
        $subMenuNavigation = SubMenuNavigation::findOrFail($id);
        return view('admins.menu-navigation.submenu.create-edit', compact('subMenuNavigation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SubMenuNavigationRequest $request, $id)
    {
        $subMenuNavigation = SubMenuNavigation::findOrFail($id);
        $subMenuNavigation->update($request->validated());
        return redirect()->route('menu-navigation.show', $subMenuNavigation->menu_navigation_id)
            ->with('success', 'Sub Menu berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $subMenuNavigation = SubMenuNavigation::findOrFail($id);
        $subMenuNavigation->delete();
        return redirect()->route('menu-navigation.index')->with('success', 'Sub Menu berhasil dihapus');
    }
}
