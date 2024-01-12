<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\AppInformation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TermConditionRequest;

class TermConditionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:content');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $termCondition = AppInformation::latest()->first();
        return view('admins.term-condition.index', compact('termCondition'));
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
    public function store(TermConditionRequest $request)
    {
        $termCondition = AppInformation::latest()->first();
        $data = $request->validated();

        $termCondition ? $termCondition->update($data) : AppInformation::create($data);
        foreach (['terms_and_conditions', 'terms_and_conditions_en'] as $key) {
            if (cache()->has($key)) {
                cache()->forget($key);
            }
        }
        return redirect()->back()->with('success', 'Ketentuan Pengguna berhasil diubah');
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
