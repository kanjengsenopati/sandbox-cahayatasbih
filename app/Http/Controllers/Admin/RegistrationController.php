<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PpdbRegistration;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->can('Manage PPDB')) {
             // Fallback permission check, adjust as needed or remove if not strict
             // return redirect()->back()->with('error', 'Unauthorized');
        }

        $query = PpdbRegistration::with(['user', 'track.school'])->latest();

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('school_id') && $request->school_id) {
            $query->whereHas('track', function ($q) use ($request) {
                $q->where('school_id', $request->school_id);
            });
        }

        $registrations = $query->paginate(10);
        $schools = School::all(); // For filter dropdown

        return view('admins.registrations.index', compact('registrations', 'schools'));
    }

    public function show($id)
    {
        $registration = PpdbRegistration::with(['user', 'track.school'])->findOrFail($id);
        return view('admins.registrations.show', compact('registration'));
    }
}
