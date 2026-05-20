<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    /**
     * Show audit results (legacy UI).
     */
    public function index(Request $request)
    {
        $service = new AuditService();
        $results = $service->runAll();
        return view('admin.audit', compact('results'));
    }
}
