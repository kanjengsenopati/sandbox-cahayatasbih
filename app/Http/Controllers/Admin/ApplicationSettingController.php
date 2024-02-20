<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\ApplicationSetting;
use App\Http\Controllers\Controller;

class ApplicationSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $applicationSetting = ApplicationSetting::first();
        return view('admins.application-setting.index', compact('applicationSetting'));
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
    public function store(Request $request)
    {
        $data = $request->validate([
            'payment_fee' => 'required|numeric',
            'student_card_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('student_card_image')) {
            $data['student_card_image'] = $this->storeStudentCardImage($request->file('student_card_image'));
        }

        ApplicationSetting::updateOrCreate([], $data);

        return redirect()->route('application-setting.index')->with('success', 'Berhasil mengubah pengaturan aplikasi');
    }

    private function storeStudentCardImage($file)
    {
        $imagePath = $file->store('images/student-card', 'public');

        $previousImage = ApplicationSetting::value('student_card_image');
        if ($previousImage && file_exists($previousImage)) {
            unlink($previousImage);
        }

        return 'storage/' . $imagePath;
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
