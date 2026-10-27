<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\PpdbRegistration;
use App\Models\School;
use App\Services\PsbSyncService;
use Illuminate\Http\Request;

class PsbSyncController extends Controller
{
    protected PsbSyncService $syncService;

    public function __construct(PsbSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Halaman Dashboard Sinkronisasi PSB
     */
    public function index(Request $request)
    {
        $connection = $this->syncService->testConnection();
        $pendingCandidates = collect();

        if ($connection['status']) {
            $pendingCandidates = $this->syncService->getPendingAcceptedCandidates(50);
        }

        // Ambil pendaftar yang sudah tersinkron di database SIM
        $syncedRegistrations = PpdbRegistration::whereNotNull('source_psb_id')
            ->orWhere('status', PpdbRegistration::STATUS_ACCEPTED)
            ->latest()
            ->paginate(20);

        $schools = School::orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();

        return view('admins.psb.sync', compact(
            'connection',
            'pendingCandidates',
            'syncedRegistrations',
            'schools',
            'classrooms',
            'academicYears'
        ));
    }

    /**
     * Eksekusi Pull Data dari PSB ke antrean SIM
     */
    public function pull(Request $request)
    {
        $selectedIds = $request->input('selected_ids', []);
        $result = $this->syncService->pullAcceptedCandidates($selectedIds);

        if ($result['status']) {
            return redirect()->route('psb.sync.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('psb.sync.index')
            ->with('error', $result['message']);
    }

    /**
     * Promosikan Calon Santri di antrean SIM menjadi Santri Aktif
     */
    public function promote(Request $request, string $id)
    {
        $request->validate([
            'school_id' => 'nullable|string',
            'classroom_id' => 'nullable|string',
        ]);

        $result = $this->syncService->promoteToStudent(
            $id,
            $request->input('school_id'),
            $request->input('classroom_id')
        );

        if ($result['status']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }
}
