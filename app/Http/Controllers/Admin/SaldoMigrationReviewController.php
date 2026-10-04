<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaldoHistory;
use App\Models\SaldoMigrationBatch;
use App\Models\SaldoMigrationItem;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class SaldoMigrationReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!$user || (!$user->hasRole('Super Admin') && !$user->can('Manage Audit dan Sinkron') && !$user->can('Manage Saldo Santri'))) {
                abort(403, 'Akses terbatas untuk Administrator / Pengelola Saldo.');
            }
            return $next($request);
        });
    }

    /**
     * Halaman review konfirmasi migrasi saldo santri dari aplikasi lama.
     */
    public function index(Request $request)
    {
        $pendingBatches = SaldoMigrationBatch::where('status', 'PENDING')->latest()->get();

        $selectedBatchId = $request->query('batch_id');
        if ($selectedBatchId) {
            $latestBatch = SaldoMigrationBatch::find($selectedBatchId);
        } else {
            $latestBatch = $pendingBatches->first() ?: SaldoMigrationBatch::latest()->first();
        }

        $historyBatches = SaldoMigrationBatch::where('status', '!=', 'PENDING')
            ->latest()
            ->take(10)
            ->get();

        $summary = null;
        if ($latestBatch) {
            $totalOldSaldo = (int) $latestBatch->items()->sum('old_saldo');
            $totalLocalSaldo = (int) $latestBatch->items()->sum('current_local_saldo');
            $totalDiff = $totalOldSaldo - $totalLocalSaldo;

            $summary = [
                'batch_id' => $latestBatch->id,
                'batch_title' => $latestBatch->notes ?: 'Batch Migrasi',
                'total_students' => $latestBatch->total_students,
                'total_old_saldo' => $totalOldSaldo,
                'total_local_saldo' => $totalLocalSaldo,
                'total_diff' => $totalDiff,
                'sent_by' => $latestBatch->sent_by,
                'sent_at' => $latestBatch->sent_at,
                'status' => $latestBatch->status,
            ];
        }

        return view('admins.migration-saldo.index', compact('latestBatch', 'pendingBatches', 'historyBatches', 'summary'));
    }

    /**
     * DataTables endpoint untuk daftar santri pada batch migrasi.
     */
    public function datatable(Request $request, $batchId)
    {
        $items = SaldoMigrationItem::where('batch_id', $batchId)
            ->select('id', 'nis', 'name', 'classroom', 'old_saldo', 'current_local_saldo', 'diff_saldo', 'status');

        return DataTables::of($items)
            ->addIndexColumn()
            ->editColumn('old_saldo', fn($r) => 'Rp ' . number_format($r->old_saldo, 0, ',', '.'))
            ->editColumn('current_local_saldo', fn($r) => 'Rp ' . number_format($r->current_local_saldo, 0, ',', '.'))
            ->editColumn('diff_saldo', function ($r) {
                $cls = $r->diff_saldo > 0 ? 'text-success' : ($r->diff_saldo < 0 ? 'text-danger' : 'text-muted');
                $prefix = $r->diff_saldo > 0 ? '+' : '';
                return '<span class="fw-bold ' . $cls . '">' . $prefix . 'Rp ' . number_format($r->diff_saldo, 0, ',', '.') . '</span>';
            })
            ->editColumn('status', function ($r) {
                if ($r->status === 'APPLIED') {
                    return '<span class="badge badge-light-success">Diterapkan</span>';
                } elseif ($r->status === 'REJECTED') {
                    return '<span class="badge badge-light-danger">Ditolak</span>';
                }
                return '<span class="badge badge-light-warning">Menunggu Konfirmasi</span>';
            })
            ->rawColumns(['diff_saldo', 'status'])
            ->make(true);
    }

    /**
     * Terima dan Terapkan Saldo ke Database Santri Aplikasi Baru.
     */
    public function apply(Request $request, $batchId)
    {
        @set_time_limit(300);

        $batch = SaldoMigrationBatch::findOrFail($batchId);
        if ($batch->status !== 'PENDING') {
            return redirect()->back()->with('error', "Batch migrasi ini sudah berstatus: {$batch->status}. Tidak dapat diterapkan ulang.");
        }

        try {
            DB::transaction(function () use ($batch) {
                $items = $batch->items()->get();
                $now = now();
                $adminName = Auth::user()->name ?? 'Administrator';

                foreach ($items as $item) {
                    $student = Student::find($item->student_id);
                    if (!$student && $item->nis) {
                        $student = Student::where('nis', $item->nis)->first();
                    }

                    if ($student) {
                        $oldBalance = (int) $student->saldo;
                        $newBalance = (int) $item->old_saldo;

                        // 1. Update saldo dan saving santri
                        $student->update([
                            'saldo' => $newBalance,
                            'saving' => (int) $item->old_saving,
                            'updated_at' => $now,
                        ]);

                        // 2. Catat audit trail resmi di saldo_histories
                        SaldoHistory::create([
                            'id' => (string) Str::uuid(),
                            'student_id' => $student->id,
                            'type' => 'IN',
                            'usage' => 'MIGRATION',
                            'amount' => abs($newBalance - $oldBalance) ?: $newBalance,
                            'description' => "Migrasi Saldo Awal dari Aplikasi Lama (Batch Ref: {$batch->id})",
                            'balance_before' => $oldBalance,
                            'balance_after' => $newBalance,
                            'status' => 'SUCCESS',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);

                        $item->update(['status' => 'APPLIED']);
                    }
                }

                $batch->update([
                    'status' => 'APPLIED',
                    'applied_at' => $now,
                    'applied_by' => $adminName,
                    'notes' => "Diterima dan diterapkan oleh {$adminName} pada {$now->toDateTimeString()}",
                ]);

                Log::info("[MigrationReview] Batch {$batch->id} berhasil diterapkan ke " . count($items) . " santri.");
            });

            return redirect()->route('admin.migration-saldo.index')->with('success', "✅ SELAMAT! Saldo migrasi {$batch->total_students} santri (Total: Rp " . number_format($batch->total_saldo, 0, ',', '.') . ") BERHASIL DITERIMA DAN DITERAPKAN ke database!");
        } catch (\Throwable $e) {
            Log::error("[MigrationReview] Gagal menerapkan batch: " . $e->getMessage());
            return redirect()->back()->with('error', "Gagal menerapkan saldo migrasi: " . $e->getMessage());
        }
    }

    /**
     * Terima dan Terapkan SEMUA Batch Migrasi Saldo Sekaligus.
     */
    public function applyAll(Request $request)
    {
        @set_time_limit(600);

        $batches = SaldoMigrationBatch::where('status', 'PENDING')->get();
        if ($batches->isEmpty()) {
            return redirect()->back()->with('info', "Tidak ada paket migrasi berstatus PENDING yang perlu diterapkan.");
        }

        try {
            $totalApplied = 0;
            $now = now();
            $adminName = Auth::user()->name ?? 'Administrator';

            DB::transaction(function () use ($batches, $now, $adminName, &$totalApplied) {
                foreach ($batches as $batch) {
                    $items = $batch->items()->get();
                    foreach ($items as $item) {
                        $student = null;
                        if ($item->student_id) {
                            $student = Student::find($item->student_id);
                        }
                        if (!$student && $item->nis) {
                            $student = Student::where('nis', $item->nis)->first();
                        }
                        if (!$student && $item->name) {
                            $student = Student::where('name', $item->name)->first();
                        }

                        if ($student) {
                            $oldBalance = (int) $student->saldo;
                            $newBalance = (int) $item->old_saldo;
                            $diff = $newBalance - $oldBalance;
                            $type = $diff >= 0 ? SaldoHistory::TYPE_IN : SaldoHistory::TYPE_OUT;

                            $student->update([
                                'saldo' => $newBalance,
                                'saving' => (int) $item->old_saving,
                                'updated_at' => $now,
                            ]);

                            SaldoHistory::create([
                                'id' => (string) Str::uuid(),
                                'student_id' => $student->id,
                                'type' => $type,
                                'usage' => 'MIGRATION',
                                'amount' => abs($diff),
                                'description' => "Migrasi Saldo Awal dari Aplikasi Lama (Batch Ref: {$batch->id})",
                                'balance_before' => $oldBalance,
                                'balance_after' => $newBalance,
                                'status' => 'SUCCESS',
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);

                            $item->update(['status' => 'APPLIED']);
                            $totalApplied++;
                        }
                    }

                    $batch->update([
                        'status' => 'APPLIED',
                        'applied_at' => $now,
                        'applied_by' => $adminName,
                        'notes' => "Diterima dan diterapkan sekaligus oleh {$adminName} pada {$now->toDateTimeString()}",
                    ]);
                }
            });

            return redirect()->route('admin.migration-saldo.index')->with('success', "✅ BERHASIL! Seluruh " . $batches->count() . " paket kelas ({$totalApplied} santri) telah BERHASIL DITERIMA DAN DITERAPKAN ke database!");
        } catch (\Throwable $e) {
            Log::error("[MigrationReview] Gagal menerapkan semua batch: " . $e->getMessage());
            return redirect()->back()->with('error', "Gagal menerapkan seluruh saldo migrasi: " . $e->getMessage());
        }
    }

    /**
     * Tolak / Batalkan Batch Migrasi.
     */
    public function reject(Request $request, $batchId)
    {
        $batch = SaldoMigrationBatch::findOrFail($batchId);
        $adminName = Auth::user()->name ?? 'Administrator';

        $batch->update([
            'status' => 'REJECTED',
            'notes' => "Ditolak oleh {$adminName} pada " . now()->toDateTimeString(),
        ]);

        $batch->items()->update(['status' => 'REJECTED']);

        return redirect()->route('admin.migration-saldo.index')->with('info', "Batch migrasi {$batch->id} telah dibatalkan / ditolak.");
    }
}
