<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaporPakReport;
use App\Models\LaporPakSetting;
use Illuminate\Http\Request;

class LaporPakAdminController extends Controller
{
    public const KENDALA_OPTIONS = [
        'Kendala Login',
        'Kendala Download dan Install',
        'Kendala Tagihan Belum Sesuai',
        'Kendala Nominal Saldo Belum Sesuai',
        'Kendala Lainnya',
    ];

    public function index(Request $request)
    {
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

        // Total Statistik
        $allReports = LaporPakReport::all();
        $stats = [
            'total' => $allReports->count(),
            'kendalaCount' => $allReports->where('status', 'Kendala')->count(),
            'teratasiCount' => $allReports->where('status', 'Teratasi')->count(),
            'catBreakdown' => collect(self::KENDALA_OPTIONS)->map(function ($cat) use ($allReports) {
                return [
                    'category' => $cat,
                    'count' => $allReports->where('kendala', $cat)->count(),
                ];
            }),
        ];

        return view('admins.laporpak.index', compact(
            'setting',
            'reports',
            'stats',
            'statusFilter',
            'categoryFilter',
            'search'
        ));
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

    public function toggleStatus($id)
    {
        $report = LaporPakReport::findOrFail($id);
        $nextStatus = $report->status === 'Kendala' ? 'Teratasi' : 'Kendala';
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
