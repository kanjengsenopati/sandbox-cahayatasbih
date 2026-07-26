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
        $request->validate([
            'preview_id' => 'required|string',
            'selected_students' => 'nullable|array',
            'selected_students.*' => 'string'
        ]);

        $adminId = Auth::id(); // Get currently logged in admin ID
        
        $selectedStudents = $request->input('selected_students');
        
        $result = $this->syncService->executeSync($request->preview_id, $adminId, $selectedStudents);

        if ($result['status']) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }
}
