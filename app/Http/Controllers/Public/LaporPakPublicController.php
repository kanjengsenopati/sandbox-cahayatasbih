<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LaporPakReport;
use App\Models\LaporPakSetting;
use App\Models\Student;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LaporPakPublicController extends Controller
{
    public function index(Request $request)
    {
        $setting = LaporPakSetting::getSetting();
        $now = Carbon::now();

        // Cek apakah form aktif dan berada di dalam periode waktu
        $isOpen = $setting->is_active;

        if ($setting->start_datetime && $now->lt($setting->start_datetime)) {
            $isOpen = false;
        }

        if ($setting->end_datetime && $now->gt($setting->end_datetime)) {
            $isOpen = false;
        }

        if (!$isOpen) {
            return view('public.laporpak.closed', compact('setting'));
        }

        // Search pada Tab 2 (Progress Laporan) berdasarkan Nama Wali atau Nama Siswa
        $search = trim($request->query('search', ''));
        $tab = $request->query('tab', 'form');

        $reportsQuery = LaporPakReport::latest();

        if (!empty($search)) {
            $reportsQuery->where(function ($q) use ($search) {
                $q->where('parent_name', 'like', "%{$search}%")
                  ->orWhere('student_name', 'like', "%{$search}%");
            });
        }

        $reports = $reportsQuery->get();

        return view('public.laporpak.index', compact('setting', 'reports', 'search', 'tab'));
    }

    public function searchStudents(Request $request)
    {
        $q = trim($request->query('q', ''));

        if (empty($q)) {
            return response()->json([]);
        }

        $students = Student::with(['school', 'classroom'])
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('nis', 'like', "%{$q}%");
            })
            ->take(15)
            ->get()
            ->map(function ($student) {
                $schoolName = $student->school ? $student->school->name : 'Sekolah';
                $className = $student->classroom ? $student->classroom->name : 'Kelas';
                
                // Prioritaskan nama wali dari relasi/user jika tersedia
                $parentName = 'Wali Santri';
                $parentPhone = '';

                if ($student->user) {
                    $parentName = $student->user->name ?? 'Wali Santri';
                    $parentPhone = $student->user->phone ?? '';
                }

                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'school' => $schoolName,
                    'class' => $className,
                    'displayLabel' => "{$student->name} — {$schoolName} — {$className}",
                    'parentName' => $parentName,
                    'parentPhone' => $parentPhone,
                ];
            });

        return response()->json($students);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kendala' => 'required|string',
            'keterangan' => 'nullable|string',
            'student_id' => 'nullable',
            'student_name' => 'required|string',
            'school' => 'required|string',
            'class_name' => 'required|string',
            'parent_name' => 'required|string',
            'parent_phone' => 'required|string',
            'is_parent_updated' => 'nullable|boolean',
        ]);

        $report = LaporPakReport::create([
            'student_id' => $validated['student_id'] ?? null,
            'student_name' => $validated['student_name'],
            'school' => $validated['school'],
            'class_name' => $validated['class_name'],
            'parent_name' => $validated['parent_name'],
            'parent_phone' => $validated['parent_phone'],
            'is_parent_updated' => $request->has('is_parent_updated') ? (bool)$request->is_parent_updated : false,
            'kendala' => $validated['kendala'],
            'keterangan' => $validated['keterangan'] ?? null,
            'status' => 'Kendala',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Laporan Anda telah berhasil terkirim.',
                'data' => $report,
            ]);
        }

        return redirect()->route('public.laporpak.index', ['tab' => 'progress', 'search' => $report->parent_name])
            ->with('success', 'Laporan Anda telah berhasil terkirim!');
    }
}
