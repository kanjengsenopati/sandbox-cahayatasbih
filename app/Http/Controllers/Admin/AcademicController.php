<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\School;
use App\Models\AcademicYear;

class AcademicController extends Controller
{
    /**
     * Display the unified academic page with tabs.
     */
    public function index(Request $request)
    {
        if (!Auth::user()->canany(['Manage Sekolah', 'Manage Tahun Ajaran', 'Manage Semester', 'Manage Mata Pelajaran', 'Manage Kenaikan Kelas', 'Manage Kelulusan Santri'])) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $schools = School::hasSchool()->orderBy('name', 'asc')->get();
        
        $schoolsGraduation = School::hasSchool()
            ->whereIn('type', [School::TYPE_SMP, School::TYPE_MA])
            ->orderBy('name', 'asc')
            ->get();
            
        $academicYears = AcademicYear::orderBy('name', 'asc')->get();
        
        $allAdmins = \App\Models\Admin::orderBy('name')->get();
        
        $activeTab = $request->input('tab', 'school');

        return view('admins.academic.index', compact('schools', 'schoolsGraduation', 'academicYears', 'allAdmins', 'activeTab'));
    }
}
