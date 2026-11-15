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
     * Show system diagnostics UI (instant page load with skeleton loaders).
     */
    public function diagnosticsIndex(Request $request)
    {
        if (!Auth::user()->can('Manage Audit dan Sinkron')) {
            return redirect()->route('dashboard')->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        return view('admins.admin.audit.diagnostics');
    }

    /**
     * AJAX endpoint to fetch diagnostic integrity scripts results asynchronously.
     */
    public function ajaxScripts(Request $request)
    {
        if (!Auth::user()->can('Manage Audit dan Sinkron')) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        session()->save();

        if ($request->boolean('refresh')) {
            Cache::forget('audit_diagnostics_results');
        }

        $results = Cache::remember('audit_diagnostics_results', 3600, function () {
            $service = new AuditService();
            return $service->runAll();
        });

        $html = view('admins.admin.audit.partials.scripts-output', compact('results'))->render();

        return response()->json([
            'html' => $html,
        ]);
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

