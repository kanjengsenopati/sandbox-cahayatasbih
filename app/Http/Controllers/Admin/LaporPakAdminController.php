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

            // Parameter filter
            $statusFilter = $request->query('status', 'all');
            $categoryFilter = $request->query('category', 'all');
            $search = trim($request->query('q', ''));

            $query = LaporPakReport::latest();

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

            $reports = $query->paginate(25)->withQueryString();

            // Total Statistik 4 Milestone
            $allReports = Schema::hasTable('lapor_pak_reports') ? LaporPakReport::all() : collect([]);
            $stats = [
                'total' => $allReports->count(),
                'masukCount' => $allReports->whereIn('status', ['Laporan Masuk', 'Kendala'])->count(),
                'diterimaCount' => $allReports->where('status', 'Diterima')->count(),
                'ditanganiCount' => $allReports->where('status', 'Sedang Ditangani')->count(),
                'selesaiCount' => $allReports->whereIn('status', ['Selesai', 'Teratasi'])->count(),
                'catBreakdown' => collect(self::KENDALA_OPTIONS)->map(function ($cat) use ($allReports) {
                    return [
                        'category' => $cat,
                        'count' => $allReports->where('kendala', $cat)->count(),
                    ];
                }),
            ];

            $milestones = self::MILESTONE_STATUSES;

            return view('admins.laporpak.index', compact(
                'setting',
                'reports',
                'stats',
                'statusFilter',
                'categoryFilter',
                'search',
                'milestones'
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
            $milestones = self::MILESTONE_STATUSES;

            return view('admins.laporpak.index', compact(
                'setting',
                'reports',
                'stats',
                'statusFilter',
                'categoryFilter',
                'search',
                'milestones'
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
        ]);

        $report = LaporPakReport::findOrFail($id);
        $report->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', "Status laporan ID #{$report->id} berhasil diperbarui menjadi '{$validated['status']}'.");
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
