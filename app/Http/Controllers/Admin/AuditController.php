<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
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

        return view('admins.admin.audit.sync', compact('syncStatus', 'syncHistory'));
    }

    /**
     * Show system diagnostics UI.
     */
    public function diagnosticsIndex(Request $request)
    {
        if (!Auth::user()->can('Manage Audit dan Sinkron')) {
            return redirect()->route('dashboard')->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $service = new AuditService();
        $results = $service->runAll();

        return view('admins.admin.audit.diagnostics', compact('results'));
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
}

