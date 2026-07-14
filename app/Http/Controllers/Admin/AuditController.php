<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class AuditController extends Controller
{
    /**
     * Show audit results (legacy UI).
     */
    public function index(Request $request)
    {
        $service = new AuditService();
        $results = $service->runAll();
        
        // 1. Clean up previous stuck running sync logs in database (older than 15 minutes)
        try {
            \App\Models\DatabaseSyncLog::where('status', 'running')
                ->where('started_at', '<', now()->subMinutes(15))
                ->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                    'duration' => 120,
                    'error' => 'Proses sinkronisasi terhenti secara tidak terduga (Stuck/Timeout/Server Restart).'
                ]);
        } catch (\Throwable $e) {
            // Ignore error
        }

        // 2. Backwards compatibility/latest status from cache or db
        $syncStatus = Cache::get('last_db_sync_status');
        
        // Clean up stuck running status in Cache too
        if ($syncStatus && isset($syncStatus['status']) && $syncStatus['status'] === 'running') {
            $startedAt = isset($syncStatus['started_at']) ? strtotime($syncStatus['started_at']) : 0;
            if (time() - $startedAt > 900) { // 15 minutes
                $syncStatus['status'] = 'failed';
                $syncStatus['finished_at'] = now()->toDateTimeString();
                $syncStatus['error'] = 'Proses sinkronisasi terhenti secara tidak terduga (Stuck/Timeout/Server Restart).';
                Cache::put('last_db_sync_status', $syncStatus, 1800);
            }
        }

        if (!$syncStatus) {
            $latestLog = \App\Models\DatabaseSyncLog::latest('id')->first();
            if ($latestLog) {
                $syncStatus = [
                    'status' => $latestLog->status,
                    'started_at' => $latestLog->started_at->toDateTimeString(),
                    'finished_at' => $latestLog->finished_at?->toDateTimeString(),
                    'duration' => $latestLog->duration,
                    'report' => $latestLog->report ?? [],
                    'error' => $latestLog->error
                ];
            }
        }

        // Fetch full sync history list
        $syncHistory = \App\Models\DatabaseSyncLog::orderBy('id', 'desc')->take(10)->get();

        return view('admins.admin.audit', compact('results', 'syncStatus', 'syncHistory'));
    }

    /**
     * Trigger database sync from master manually.
     */
    public function syncMaster(Request $request)
    {
        try {
            $selectedTables = $request->input('tables', []);
            $params = [];
            if (!empty($selectedTables)) {
                $params['--tables'] = implode(',', $selectedTables);
            }
            Artisan::call('db:sync-master', $params);
            return redirect()->route('admin.audit')->with('success', 'Sinkronisasi database master berhasil dijalankan!');
        } catch (\Throwable $e) {
            return redirect()->route('admin.audit')->with('error', 'Gagal memicu sinkronisasi: ' . $e->getMessage());
        }
    }
}
