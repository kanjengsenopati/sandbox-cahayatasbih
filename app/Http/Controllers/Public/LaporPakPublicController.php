<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LaporPakReport;
use App\Models\LaporPakSetting;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
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

        // Search pada Tab 2 (Pantau Aduan) berdasarkan Nama Wali atau Nama Siswa
        $search = trim($request->query('search', ''));
        $tab = $request->query('tab', 'form');

        $reports = collect([]);

        try {
            if (Schema::hasTable('lapor_pak_reports')) {
                $reportsQuery = LaporPakReport::latest();

                if (!empty($search)) {
                    $reportsQuery->where(function ($q) use ($search) {
                        $q->where('parent_name', 'like', "%{$search}%")
                          ->orWhere('student_name', 'like', "%{$search}%");
                    });
                }

                $reports = $reportsQuery->paginate(5)->withQueryString();
            }
        } catch (\Throwable $e) {
            Log::error('Error fetching LaporPak reports: ' . $e->getMessage());
            $reports = collect([]);
        }

        return view('public.laporpak.index', compact('setting', 'reports', 'search', 'tab'));
    }

    public function searchStudents(Request $request)
    {
        try {
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
                    
                    $parentName = 'Wali Santri';
                    $parentPhone = '';

                    if ($student->user) {
                        $parentName = $student->user->name ?? 'Wali Santri';
                        $parentPhone = $student->user->phone ?? '';
                    }

                    return [
                        'id' => (string)$student->id,
                        'name' => $student->name,
                        'school' => $schoolName,
                        'class' => $className,
                        'displayLabel' => "{$student->name} — {$schoolName} — {$className}",
                        'parentName' => $parentName,
                        'parentPhone' => $parentPhone,
                    ];
                });

            return response()->json($students);
        } catch (\Throwable $e) {
            Log::error('Error in searchStudents LaporPak: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kendala' => 'required|string',
                'keterangan' => 'nullable|string',
                'student_id' => 'nullable',
                'student_name' => 'required|string',
                'school' => 'required|string',
                'class_name' => 'required|string',
                'parent_name' => 'required|string',
                'parent_phone' => 'required|string',
                'is_parent_updated' => 'nullable',
            ]);

            $studentId = !empty($validated['student_id']) ? (string)$validated['student_id'] : null;

            $report = LaporPakReport::create([
                'student_id' => $studentId,
                'student_name' => $validated['student_name'],
                'school' => $validated['school'],
                'class_name' => $validated['class_name'],
                'parent_name' => $validated['parent_name'],
                'parent_phone' => $validated['parent_phone'],
                'is_parent_updated' => !empty($request->is_parent_updated) && $request->is_parent_updated !== '0',
                'kendala' => $validated['kendala'],
                'keterangan' => $validated['keterangan'] ?? null,
                'status' => 'Laporan Masuk',
            ]);

            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Laporan Anda telah berhasil terkirim.',
                    'data' => $report,
                    'created_at_formatted' => $report->created_at ? $report->created_at->format('d M Y, H:i') : '-',
                ]);
            }

            return redirect()->route('public.laporpak.index', ['tab' => 'progress', 'search' => $report->parent_name])
                ->with('success', 'Laporan Anda telah berhasil terkirim!');
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first() ?? 'Data form pengaduan tidak lengkap.';
            
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $firstError,
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            Log::error('Error storing LaporPak report: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem saat menyimpan laporan: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat menyimpan laporan.')->withInput();
        }
    }
}
