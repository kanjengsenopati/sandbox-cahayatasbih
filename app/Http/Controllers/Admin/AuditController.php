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
        $syncStatus = Cache::get('last_db_sync_status');

        return view('admins.admin.audit', compact('results', 'syncStatus'));
    }

    /**
     * Trigger database sync from master manually.
     */
    public function syncMaster(Request $request)
    {
        try {
            Artisan::call('db:sync-master');
            return redirect()->route('admin.audit')->with('success', 'Sinkronisasi database master berhasil dijalankan!');
        } catch (\Throwable $e) {
            return redirect()->route('admin.audit')->with('error', 'Gagal memicu sinkronisasi: ' . $e->getMessage());
        }
    }
}
