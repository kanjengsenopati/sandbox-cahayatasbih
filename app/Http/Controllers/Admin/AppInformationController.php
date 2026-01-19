<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\AppInformation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AppInformationRequest;

class AppInformationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $appInformation = AppInformation::latest()->first();
        return view('admins.term-condition.index', compact('appInformation'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AppInformationRequest $request)
    {
        $appInformation = AppInformation::latest()->first();
        $data = $request->validated();

        $appInformation ? $appInformation->update($data) : AppInformation::create($data);
        return redirect()->back()->with('success', 'Informasi Aplikasi berhasil diubah');
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
