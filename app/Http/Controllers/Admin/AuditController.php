<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\AuditComparisonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class AuditController extends Controller
{
    /**
     * Show database sync UI.
     */
    public function syncIndex(Request $request)
    {
        if (!Auth::user()->can('Manage Audit dan Sinkron')) {
            return redirect()->route('dashboard')->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

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

        $schools = \Illuminate\Support\Facades\DB::connection('mysql_master')->table('schools')->whereNull('deleted_at')->get();
        $classrooms = \Illuminate\Support\Facades\DB::connection('mysql_master')->table('classrooms')->whereNull('deleted_at')->get();
        
        $academicYears = \Illuminate\Support\Facades\DB::connection('mysql_master')->table('academic_years')->whereNull('deleted_at')->orderBy('name', 'desc')->get();
        $billTypes = \Illuminate\Support\Facades\DB::connection('mysql_master')->table('bill_types')->whereNull('deleted_at')->orderBy('name', 'asc')->get();

        $localClassroomIds = \Illuminate\Support\Facades\DB::connection('mysql')->table('classrooms')->pluck('id')->toArray();

        return view('admins.admin.audit.sync', compact('syncStatus', 'syncHistory', 'schools', 'classrooms', 'academicYears', 'billTypes', 'localClassroomIds'));
    }

    /**
     * Show system diagnostics UI.
     */
    public function diagnosticsIndex(Request $request, AuditComparisonService $comparisonService)
    {
        if (!Auth::user()->can('Manage Audit dan Sinkron')) {
            return redirect()->route('dashboard')->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        // Cache diagnostic CLI script execution results for 5 minutes unless refresh parameter is requested
        if ($request->has('refresh')) {
            Cache::forget('audit_diagnostics_results');
        }

        $results = Cache::remember('audit_diagnostics_results', 300, function () {
            $service = new AuditService();
            return $service->runAll();
        });

        $search = $request->input('search');
        $page = (int) $request->input('page', 1);

        // Fast O(1) bulk comparison data (< 200ms) with search & pagination (default 10)
        $comparison = $comparisonService->getComparisonData($search, $page, 10);

        return view('admins.admin.audit.diagnostics', compact('results', 'comparison'));
    }

    /**
     * Synchronize selected students from master database.
     */
    public function syncSelectedStudents(Request $request)
    {
        if (!Auth::user()->can('Manage Audit dan Sinkron')) {
            return redirect()->route('dashboard')->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $studentIds = $request->input('student_ids', []);
        if (empty($studentIds) || !is_array($studentIds)) {
            return redirect()->back()->with('error', 'Silakan pilih minimal satu siswa untuk disinkronkan.');
        }

        try {
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $masterStudents = \Illuminate\Support\Facades\DB::connection('mysql_master')->table('students')
                ->whereIn('id', $studentIds)
                ->whereNull('deleted_at')
                ->get();

            $syncedCount = 0;
            foreach ($masterStudents as $ms) {
                // Ensure classroom exists in local DB if linked
                if (!empty($ms->classroom_id)) {
                    $masterClassroom = \Illuminate\Support\Facades\DB::connection('mysql_master')->table('classrooms')->where('id', $ms->classroom_id)->first();
                    if ($masterClassroom) {
                        \Illuminate\Support\Facades\DB::connection('mysql')->table('classrooms')->updateOrInsert(
                            ['id' => $masterClassroom->id],
                            (array) $masterClassroom
                        );
                    }
                }

                \Illuminate\Support\Facades\DB::connection('mysql')->table('students')->updateOrInsert(
                    ['id' => $ms->id],
                    (array) $ms
                );

                // Sync student_classroom_histories
                $masterHistories = \Illuminate\Support\Facades\DB::connection('mysql_master')->table('student_classroom_histories')
                    ->where('student_id', $ms->id)
                    ->get();
                foreach ($masterHistories as $mh) {
                    \Illuminate\Support\Facades\DB::connection('mysql')->table('student_classroom_histories')->updateOrInsert(
                        ['id' => $mh->id],
                        (array) $mh
                    );
                }

                // Sync bills
                $masterBills = \Illuminate\Support\Facades\DB::connection('mysql_master')->table('bills')
                    ->where('student_id', $ms->id)
                    ->get();
                foreach ($masterBills as $mb) {
                    \Illuminate\Support\Facades\DB::connection('mysql')->table('bills')->updateOrInsert(
                        ['id' => $mb->id],
                        (array) $mb
                    );
                }

                $syncedCount++;
            }

            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // Clear caches
            Cache::forget('audit_diagnostics_results');

            return redirect()->back()->with('success', "Berhasil menyinkronkan data {$syncedCount} siswa terpilih dari Database Master.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal menyinkronkan siswa terpilih: ' . $e->getMessage());
        }
    }

    /**
     * Preview Data Pull & Diff calculation from Master Database without overwriting local DB.
     */
    public function previewPullMaster(Request $request, \App\Services\MasterIngestionBridgeService $bridgeService)
    {
        if (!Auth::user()->can('Manage Audit dan Sinkron')) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $module = $request->input('module', 'students');
        $limit = (int) $request->input('limit', 50);
        $schoolId = $request->input('school_id');
        $classroomId = $request->input('classroom_id');
        $academicYearId = $request->input('academic_year_id');
        $billTypeId = $request->input('bill_type_id');

        $diffAnalysis = $bridgeService->analyzeModuleDiff($module, $limit, $schoolId, $classroomId, $academicYearId, $billTypeId);

        return response()->json($diffAnalysis);
    }

    /**
     * Execute verified merge for selected records from preview.
     */
    public function confirmMergeMaster(Request $request, \App\Services\MasterIngestionBridgeService $bridgeService)
    {
        if (!Auth::user()->can('Manage Audit dan Sinkron')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $module = $request->input('module', 'students');
        $selectedIds = $request->input('selected_ids', []);
        $academicYearId = $request->input('academic_year_id');
        $billTypeId = $request->input('bill_type_id');

        $result = $bridgeService->executeVerifiedMerge($module, $selectedIds, $academicYearId, $billTypeId);

        if ($result['status'] === 'success') {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * AJAX endpoint to generate AI Insight asynchronously without blocking HTTP response.
     */
    public function ajaxAiInsight(Request $request, AuditComparisonService $comparisonService)
    {
        if (!Auth::user()->can('Manage Audit dan Sinkron')) {
            return response()->json(['html' => 'Akses ditolak.'], 403);
        }

        $comparison = $comparisonService->getComparisonData();
        
        $aiInsight = Cache::remember('audit_ai_insight_' . count($comparison['discrepancies']), 1800, function () use ($comparisonService, $comparison) {
            return $comparisonService->generateAiInsight($comparison);
        });

        return response()->json(['html' => $aiInsight]);
    }

    /**
     * Show duplicate students UI.
     */
    public function duplicatesIndex(Request $request)
    {
        if (!Auth::user()->can('Manage Audit dan Sinkron')) {
            return redirect()->route('dashboard')->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        // Cari siswa duplikat berdasarkan nama yang sama persis
        $duplicateNames = \App\Models\Student::select('name')
            ->groupBy('name')
            ->havingRaw('COUNT(name) > 1')
            ->pluck('name');

        $duplicateStudents = \App\Models\Student::with(['classroom.school', 'user'])
            ->whereIn('name', $duplicateNames)
            ->orderBy('name')
            ->get()
            ->groupBy('name');

        return view('admins.admin.audit.duplicate-students', compact('duplicateStudents'));
    }

    /**
     * Trigger database sync from master manually.
     */
    public function syncMaster(Request $request)
    {
        if (!Auth::user()->can('Manage Audit dan Sinkron')) {
            return redirect()->route('dashboard')->with('error', 'Maaf, Anda tidak memiliki akses untuk tindakan ini');
        }

        try {
            $selectedTables = $request->input('tables', []);
            $params = [];
            if (!empty($selectedTables)) {
                $params['--tables'] = implode(',', $selectedTables);
            }
            Artisan::call('db:sync-master', $params);
            return redirect()->route('admin.audit.sync')->with('success', 'Sinkronisasi database master berhasil dijalankan!');
        } catch (\Throwable $e) {
            return redirect()->route('admin.audit.sync')->with('error', 'Gagal memicu sinkronisasi: ' . $e->getMessage());
        }
    }

    /**
     * Merge duplicate student profiles and migrate histories.
     */
    public function mergeStudents(Request $request)
    {
        if (!Auth::user()->can('Manage Audit dan Sinkron')) {
            return redirect()->route('dashboard')->with('error', 'Maaf, Anda tidak memiliki akses untuk tindakan ini');
        }

        $request->validate([
            'source_id' => 'required|exists:students,id',
            'target_id' => 'required|exists:students,id',
        ]);

        $sourceId = $request->source_id;
        $targetId = $request->target_id;

        if ($sourceId === $targetId) {
            return redirect()->back()->with('error', 'Siswa asal dan target tidak boleh sama!');
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $source = \App\Models\Student::findOrFail($sourceId);
            $target = \App\Models\Student::findOrFail($targetId);

            // 1. Pindahkan tagihan (bills)
            \App\Models\Bill::where('student_id', $sourceId)->update(['student_id' => $targetId]);

            // 2. Pindahkan transaksi (transactions)
            \App\Models\Transaction::where('student_id', $sourceId)->update(['student_id' => $targetId]);

            // 3. Pindahkan riwayat saldo (saldo_histories)
            \App\Models\SaldoHistory::where('student_id', $sourceId)->update(['student_id' => $targetId]);

            // 4. Pindahkan riwayat tabungan (saving_histories)
            \App\Models\SavingHistory::where('student_id', $sourceId)->update(['student_id' => $targetId]);

            // 5. Pindahkan tahfidz (tahfidzs)
            if (\Schema::hasTable('tahfidzs')) {
                \App\Models\Tahfidz::where('student_id', $sourceId)->update(['student_id' => $targetId]);
            }

            // 6. Pindahkan student_card_prints (jika ada)
            if (\Schema::hasTable('student_card_prints')) {
                \DB::table('student_card_prints')->where('student_id', $sourceId)->update(['student_id' => $targetId]);
            }

            // 7. Pindahkan student_classroom_histories
            if (\Schema::hasTable('student_classroom_histories')) {
                \DB::table('student_classroom_histories')->where('student_id', $sourceId)->update(['student_id' => $targetId]);
            }

            // 8. Pindahkan student_bill_notifications
            if (\Schema::hasTable('student_bill_notifications')) {
                \DB::table('student_bill_notifications')->where('student_id', $sourceId)->update(['student_id' => $targetId]);
            }

            // 9. Pindahkan point_of_sale_transactions
            if (\Schema::hasTable('point_of_sale_transactions')) {
                \DB::table('point_of_sale_transactions')->where('student_id', $sourceId)->update(['student_id' => $targetId]);
            }

            // 10. Pindahkan attendances (polymorphic)
            if (\Schema::hasTable('attendances')) {
                \DB::table('attendances')
                    ->where('presensiable_id', $sourceId)
                    ->where('presensiable_type', \App\Models\Student::class)
                    ->update(['presensiable_id' => $targetId]);
            }

            // 11. Pindahkan biometric_mappings (polymorphic)
            if (\Schema::hasTable('biometric_mappings')) {
                \DB::table('biometric_mappings')
                    ->where('presensiable_id', $sourceId)
                    ->where('presensiable_type', \App\Models\Student::class)
                    ->update(['presensiable_id' => $targetId]);
            }

            // 12. Pindahkan data prestasi (student_achievements)
            if (\Schema::hasTable('student_achievements')) {
                \DB::table('student_achievements')->where('student_id', $sourceId)->update(['student_id' => $targetId]);
            }

            // 13. Pindahkan nilai konseling (student_counseling_scores)
            if (\Schema::hasTable('student_counseling_scores')) {
                \DB::table('student_counseling_scores')->where('student_id', $sourceId)->update(['student_id' => $targetId]);
            }

            // 14. Akumulasikan saldo & tabungan
            $target->saldo += $source->saldo;
            $target->saving += $source->saving;
            $target->save();

            // 15. Hapus siswa asal (soft delete)
            $source->delete();

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('admin.audit.duplicates')
                ->with('success', "Berhasil menggabungkan data siswa {$source->name} ke {$target->name}. Seluruh saldo, tabungan, riwayat tagihan, dan transaksi telah dipindahkan.");

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Merge students failed: " . $e->getMessage());
            return redirect()->back()->with('error', "Gagal menggabungkan data siswa: " . $e->getMessage());
        }
    }

    /**
     * Show draft simulation audit UI.
     */
    public function simulationIndex(Request $request)
    {
        if (!Auth::user()->can('Manage Audit dan Sinkron')) {
            return redirect()->route('dashboard')->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $search = $request->input('search');
        $query = \Illuminate\Support\Facades\DB::table('saldo_audit_simulations')
            ->orderBy('saldo_diff', 'desc')
            ->orderBy('student_name', 'asc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('student_nis', 'like', "%{$search}%");
            });
        }

        $simulations = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => \Illuminate\Support\Facades\DB::table('saldo_audit_simulations')->count(),
            'mismatches' => \Illuminate\Support\Facades\DB::table('saldo_audit_simulations')->where('saldo_diff', '!=', 0)->count(),
            'applied' => \Illuminate\Support\Facades\DB::table('saldo_audit_simulations')->where('status', 'APPLIED')->count(),
            'negatives' => \Illuminate\Support\Facades\DB::table('saldo_audit_simulations')->where('issue_type', 'NEGATIVE_BALANCE')->count(),
        ];

        return view('admins.admin.audit.simulation', compact('simulations', 'stats', 'search'));
    }

    /**
     * Trigger fresh draft simulation.
     */
    public function runSimulation(Request $request)
    {
        if (!Auth::user()->can('Manage Audit dan Sinkron')) {
            return redirect()->route('dashboard')->with('error', 'Maaf, Anda tidak memiliki akses untuk tindakan ini');
        }

        try {
            $service = new \App\Services\SaldoSimulationService();
            $res = $service->runSimulation();
            return redirect()->route('admin.audit.simulation')
                ->with('success', "Simulasi draft berhasil diperbarui! ({$res['total_processed']} siswa diproses, {$res['total_mismatches']} selisih terdeteksi).");
        } catch (\Throwable $e) {
            return redirect()->route('admin.audit.simulation')
                ->with('error', "Gagal menjalankan simulasi: " . $e->getMessage());
        }
    }

    /**
     * Apply simulation fix for a specific student.
     */
    public function applySimulation(Request $request)
    {
        if (!Auth::user()->can('Manage Audit dan Sinkron')) {
            return redirect()->route('dashboard')->with('error', 'Maaf, Anda tidak memiliki akses untuk tindakan ini');
        }

        $studentId = $request->input('student_id');
        try {
            $service = new \App\Services\SaldoSimulationService();
            $service->applyFixForStudent($studentId, Auth::id());
            return redirect()->back()->with('success', 'Perbaikan saldo siswa berhasil diterapkan dan snapshot backup telah disimpan!');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal menerapkan perbaikan: ' . $e->getMessage());
        }
    }

    /**
     * Rollback applied fix for a specific student.
     */
    public function rollbackSimulation(Request $request)
    {
        if (!Auth::user()->can('Manage Audit dan Sinkron')) {
            return redirect()->route('dashboard')->with('error', 'Maaf, Anda tidak memiliki akses untuk tindakan ini');
        }

        $studentId = $request->input('student_id');
        try {
            $service = new \App\Services\SaldoSimulationService();
            $service->rollbackFixForStudent($studentId);
            return redirect()->back()->with('success', 'Saldo siswa berhasil dikembalikan (rollback) ke kondisi snapshot sebelum perbaikan!');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal mengembalikan saldo: ' . $e->getMessage());
        }
    }
}

