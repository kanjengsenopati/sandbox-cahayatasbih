<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\PpdbRegistration;
use App\Http\Controllers\Controller;
use App\Services\SendNotifWaService;
use App\Jobs\SendToWhatsappNotificationJob;
use App\Http\Requests\Admin\PpdbRegistrationRequest;

class PpdbRegistrationController extends Controller
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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $ppdbRegistration = PpdbRegistration::with('ppdb')->findOrFail($id);
        return view('admins.ppdb-registration.show', compact('ppdbRegistration'));
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
    public function update(PpdbRegistrationRequest $request, string $id)
    {
        $ppdbRegistration = PpdbRegistration::findOrFail($id);
        $ppdbRegistration->update($request->validated());

        if ($ppdbRegistration->status === PpdbRegistration::STATUS_APPROVED || $ppdbRegistration->status === PpdbRegistration::STATUS_REJECTED) {
            $message = SendNotifWaService::sendMessageConfirmPpdb($ppdbRegistration);
            dispatch(new SendToWhatsappNotificationJob($ppdbRegistration->user->phone, $message));
        }
        return redirect()->route('ppdb-registration.show', $ppdbRegistration->id)
            ->with('success', 'Status PPDB berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
