<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Classroom;
use App\Services\AdvancedSyncService;
use Illuminate\Support\Facades\Auth;

class AdvancedSyncController extends Controller
{
    protected $syncService;

    public function __construct(AdvancedSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Display the index page for Advanced Sync.
     */
    public function index()
    {
        $schools = School::orderBy('name')->get();
        $classrooms = Classroom::with('school')->orderBy('name')->get();
        
        return view('admins.admin.audit.advanced_sync', compact('schools', 'classrooms'));
    }

    /**
     * Generate preview via AJAX for DataTables.
     */
    public function preview(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'school_id', 'classroom_id', 'search']);
        
        $result = $this->syncService->generatePreview($filters);
        
        return response()->json([
            'data' => $result['data'],
            'preview_id' => $result['preview_id'],
            'recordsTotal' => $result['data']->count(),
            'recordsFiltered' => $result['data']->count()
        ]);
    }

    /**
     * Execute the merge based on the preview ID.
     */
    public function execute(Request $request)
    {
        @set_time_limit(600);
        @ini_set('memory_limit', '512M');

        try {
            $request->validate([
                'preview_id' => 'required|string',
                'selected_students' => 'nullable|array',
                'selected_students.*' => 'string'
            ]);

            $adminId = (string) Auth::id(); // Get currently logged in admin ID
            $selectedStudents = $request->input('selected_students');
            
            $result = $this->syncService->executeSync($request->preview_id, $adminId, $selectedStudents);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json($result);
            }

            if ($result['status']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("AdvancedSyncController Exception: " . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal mengeksekusi sinkronisasi: ' . $e->getMessage());
        }
    }

    /**
     * Execute full clean reconciliation from master (cahayatasbihdb).
     */
    public function cleanReconcile(Request $request, \App\Services\CleanReconciliationService $cleanService)
    {
        @set_time_limit(3600);
        @ini_set('memory_limit', '2048M');

        try {
            $options = $request->only(['class_id', 'school_id', 'student_id']);
            $result = $cleanService->execute($options);
            return response()->json($result);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("CleanReconciliation Exception: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal rekonsiliasi bersih: ' . $e->getMessage()
            ], 500);
        }
    }
}
