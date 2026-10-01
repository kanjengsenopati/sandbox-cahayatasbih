<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaporPakReport;
use App\Models\LaporPakSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class LaporPakAdminController extends Controller
{
    public const KENDALA_OPTIONS = [
        'Kendala Login',
        'Kendala Download dan Install',
        'Kendala Tagihan Belum Sesuai',
        'Kendala Nominal Saldo Belum Sesuai',
        'Kendala Lainnya',
    ];

    public const MILESTONE_STATUSES = [
        'Laporan Masuk',
        'Diterima',
        'Sedang Ditangani',
        'Selesai',
    ];

    public function index(Request $request)
    {
        try {
            $setting = LaporPakSetting::getSetting();

            // Parameter filter & sorting
            $statusFilter = $request->query('status', 'all');
            $categoryFilter = $request->query('category', 'all');
            $search = trim($request->query('q', ''));
            $sortBy = $request->query('sort_by', 'created_at');
            $sortDir = strtolower($request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

            $allowedSorts = ['parent_name', 'student_name', 'kendala', 'created_at'];
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'created_at';
            }

            $query = LaporPakReport::query();

            if ($statusFilter !== 'all') {
                $query->where('status', $statusFilter);
            }

            if ($categoryFilter !== 'all') {
                $query->where('kendala', $categoryFilter);
            }

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('parent_name', 'like', "%{$search}%")
                      ->orWhere('parent_phone', 'like', "%{$search}%")
                      ->orWhere('student_name', 'like', "%{$search}%")
                      ->orWhere('keterangan', 'like', "%{$search}%");
                });
            }

            $query->orderBy($sortBy, $sortDir);

            $reports = $query->paginate(25)->withQueryString();

            // Total Statistik 4 Milestone via SQL aggregation
            if (Schema::hasTable('lapor_pak_reports')) {
                $rawStats = LaporPakReport::selectRaw("
                    COUNT(*) as total,
                    COUNT(CASE WHEN status IN ('Laporan Masuk', 'Kendala') THEN 1 END) as masuk_count,
                    COUNT(CASE WHEN status = 'Diterima' THEN 1 END) as diterima_count,
                    COUNT(CASE WHEN status = 'Sedang Ditangani' THEN 1 END) as ditangani_count,
                    COUNT(CASE WHEN status IN ('Selesai', 'Teratasi') THEN 1 END) as selesai_count
                ")->first();

                $categoryCounts = LaporPakReport::selectRaw('kendala, COUNT(*) as count')
                    ->whereNotNull('kendala')
                    ->groupBy('kendala')
                    ->pluck('count', 'kendala')
                    ->toArray();

                $stats = [
                    'total' => (int)($rawStats->total ?? 0),
                    'masukCount' => (int)($rawStats->masuk_count ?? 0),
                    'diterimaCount' => (int)($rawStats->diterima_count ?? 0),
                    'ditanganiCount' => (int)($rawStats->ditangani_count ?? 0),
                    'selesaiCount' => (int)($rawStats->selesai_count ?? 0),
                    'catBreakdown' => collect(self::KENDALA_OPTIONS)->map(function ($cat) use ($categoryCounts) {
                        return [
                            'category' => $cat,
                            'count' => (int)($categoryCounts[$cat] ?? 0),
                        ];
                    }),
                ];
            } else {
                $stats = [
                    'total' => 0,
                    'masukCount' => 0,
                    'diterimaCount' => 0,
                    'ditanganiCount' => 0,
                    'selesaiCount' => 0,
                    'catBreakdown' => collect([]),
                ];
            }

            $milestones = self::MILESTONE_STATUSES;

            return view('admins.laporpak.index', compact(
                'setting',
                'reports',
                'stats',
                'statusFilter',
                'categoryFilter',
                'search',
                'milestones',
                'sortBy',
                'sortDir'
            ));
        } catch (\Throwable $e) {
            Log::error('Error rendering LaporPakAdminController index: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            
            // Safety fallback return view or error response
            $setting = LaporPakSetting::getSetting();
            $reports = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25);
            $stats = [
                'total' => 0,
                'masukCount' => 0,
                'diterimaCount' => 0,
                'ditanganiCount' => 0,
                'selesaiCount' => 0,
                'catBreakdown' => collect([]),
            ];
            $statusFilter = 'all';
            $categoryFilter = 'all';
            $search = '';
            $sortBy = 'created_at';
            $sortDir = 'desc';
            $milestones = self::MILESTONE_STATUSES;

            return view('admins.laporpak.index', compact(
                'setting',
                'reports',
                'stats',
                'statusFilter',
                'categoryFilter',
                'search',
                'milestones',
                'sortBy',
                'sortDir'
            ));
        }
    }

    public function updateSetting(Request $request)
    {
        $validated = $request->validate([
            'is_active' => 'nullable|boolean',
            'start_datetime' => 'nullable|date',
            'end_datetime' => 'nullable|date',
            'closed_message' => 'nullable|string',
        ]);

        $setting = LaporPakSetting::getSetting();
        $setting->update([
            'is_active' => $request->has('is_active') ? true : false,
            'start_datetime' => $validated['start_datetime'] ?? null,
            'end_datetime' => $validated['end_datetime'] ?? null,
            'closed_message' => $validated['closed_message'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Pengaturan periode pengaduan Lapor Pak berhasil diperbarui.');
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:Laporan Masuk,Diterima,Sedang Ditangani,Selesai',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $report = LaporPakReport::findOrFail($id);
        $updateData = ['status' => $validated['status']];
        
        if ($request->has('admin_note')) {
            $updateData['admin_note'] = $validated['admin_note'];
        }

        $report->update($updateData);

        return redirect()->back()->with('success', "Status & catatan laporan ID #{$report->id} berhasil diperbarui.");
    }

    public function toggleStatus($id)
    {
        $report = LaporPakReport::findOrFail($id);
        $nextStatus = match($report->status) {
            'Laporan Masuk' => 'Diterima',
            'Diterima' => 'Sedang Ditangani',
            'Sedang Ditangani' => 'Selesai',
            default => 'Laporan Masuk',
        };
        $report->update(['status' => $nextStatus]);

        return redirect()->back()->with('success', "Status laporan ID #{$report->id} berhasil diubah menjadi {$nextStatus}.");
    }

    public function destroy($id)
    {
        $report = LaporPakReport::findOrFail($id);
        $report->delete();

        return redirect()->back()->with('success', 'Laporan pengaduan berhasil dihapus.');
    }
}
