<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Information;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WaliDashboardController extends Controller
{
    public function index()
    {
        $informations = Information::with(
            'informationCategory'
        )->where('is_active', true)->latest()->take(5)->get();
        $students = Student::where('user_id', Auth::guard('wali')->user()->id)->orderBy('name', 'asc')->get();
        return view('users.dashboard.index', compact('informations', 'students'));
    }

    public function app()
    {
        $informations = Information::with(
            'informationCategory'
        )->where('is_active', true)->latest()->take(5)->get();
        
        $user = Auth::guard('wali')->user();
        $students = Student::where('user_id', $user->id)->orderBy('name', 'asc')->get();
        
        // Handle active student for PWA view
        $activeStudentId = session('active_student_id');
        $activeStudent = $students->where('id', $activeStudentId)->first() ?: $students->first();
        
        return view('users.dashboard.app', compact('informations', 'students', 'activeStudent'));
    }

    public function switchStudent($id)
    {
        session(['active_student_id' => $id]);
        return redirect()->back();
    }
}
