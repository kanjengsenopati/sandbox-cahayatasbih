<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\School;
use App\Models\Classroom;
use App\Models\ApplicationSetting;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentCardSettingRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Milon\Barcode\DNS1D;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StudentCardSettingController extends Controller
{
    /**
     * Show the student card design & print management page.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Pengaturan Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $setting = ApplicationSetting::first();
        $layout = $setting->student_card_layout ?? ApplicationSetting::getDefaultStudentCardLayout();
        $background = $setting->student_card_image ?? '';
        $schools = School::orderBy('name')->get();

        return view('admins.student-card-setting.index', compact('setting', 'layout', 'background', 'schools'));
    }

    /**
     * Save the student card layout configuration.
     */
    public function store(StudentCardSettingRequest $request)
    {
        if (!Auth::user()->can('Edit Pengaturan Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $setting = ApplicationSetting::firstOrCreate([]);

        // Handle background image upload
        if ($request->hasFile('student_card_image')) {
            // Delete previous image safely using Storage facade
            if ($setting->student_card_image) {
                $oldPath = str_replace('storage/', '', $setting->student_card_image);
                Storage::disk('public')->delete($oldPath);
            }
            $imagePath = $request->file('student_card_image')->store('images/student-card', 'public');
            $setting->student_card_image = 'storage/' . $imagePath;
        }

        // Build layout config from form inputs
        $layout = [
            'logo' => [
                'show' => $request->boolean('layout.logo.show'),
                'top' => (float) $request->input('layout.logo.top', 5),
                'left' => (float) $request->input('layout.logo.left', 5),
                'width' => (float) $request->input('layout.logo.width', 25),
                'height' => (float) $request->input('layout.logo.height', 8),
            ],
            'title' => [
                'show' => $request->boolean('layout.title.show'),
                'text' => $request->input('layout.title.text', 'Kartu Santri'),
                'color' => $request->input('layout.title.color', '#FFFF00'),
                'font_size' => (int) $request->input('layout.title.font_size', 12),
                'top' => (float) $request->input('layout.title.top', 5),
                'left' => (float) $request->input('layout.title.left', 45),
                'text_align' => $request->input('layout.title.text_align', 'right'),
                'font_weight' => $request->input('layout.title.font_weight', 'bold'),
            ],
            'subtitle' => [
                'show' => $request->boolean('layout.subtitle.show'),
                'text' => $request->input('layout.subtitle.text', 'PPTQ Cahaya Tasbih'),
                'color' => $request->input('layout.subtitle.color', '#FFFFFF'),
                'font_size' => (int) $request->input('layout.subtitle.font_size', 10),
                'top' => (float) $request->input('layout.subtitle.top', 10),
                'left' => (float) $request->input('layout.subtitle.left', 45),
                'text_align' => $request->input('layout.subtitle.text_align', 'right'),
                'font_weight' => $request->input('layout.subtitle.font_weight', 'bold'),
            ],
            'photo' => [
                'show' => $request->boolean('layout.photo.show'),
                'top' => (float) $request->input('layout.photo.top', 18),
                'left' => (float) $request->input('layout.photo.left', 5),
                'width' => (float) $request->input('layout.photo.width', 18),
                'height' => (float) $request->input('layout.photo.height', 24),
                'border_radius' => (float) $request->input('layout.photo.border_radius', 2),
            ],
            'name' => [
                'show' => $request->boolean('layout.name.show'),
                'color' => $request->input('layout.name.color', '#FFFFFF'),
                'font_size' => (int) $request->input('layout.name.font_size', 12),
                'top' => (float) $request->input('layout.name.top', 20),
                'left' => (float) $request->input('layout.name.left', 25),
                'font_weight' => $request->input('layout.name.font_weight', 'bold'),
            ],
            'nis' => [
                'show' => $request->boolean('layout.nis.show'),
                'color' => $request->input('layout.nis.color', '#FFFFFF'),
                'font_size' => (int) $request->input('layout.nis.font_size', 14),
                'top' => (float) $request->input('layout.nis.top', 27),
                'left' => (float) $request->input('layout.nis.left', 25),
                'font_weight' => $request->input('layout.nis.font_weight', 'bold'),
                'font_family' => $request->input('layout.nis.font_family', 'Kredit'),
            ],
            'classroom' => [
                'show' => $request->boolean('layout.classroom.show'),
                'color' => $request->input('layout.classroom.color', '#FFFFFF'),
                'font_size' => (int) $request->input('layout.classroom.font_size', 9),
                'top' => (float) $request->input('layout.classroom.top', 35),
                'left' => (float) $request->input('layout.classroom.left', 25),
                'font_weight' => $request->input('layout.classroom.font_weight', 'bold'),
            ],
            'school' => [
                'show' => $request->boolean('layout.school.show'),
                'color' => $request->input('layout.school.color', '#FFFFFF'),
                'font_size' => (int) $request->input('layout.school.font_size', 9),
                'top' => (float) $request->input('layout.school.top', 40),
                'left' => (float) $request->input('layout.school.left', 25),
                'font_weight' => $request->input('layout.school.font_weight', 'bold'),
            ],
            'code' => [
                'show' => $request->boolean('layout.code.show'),
                'type' => $request->input('layout.code.type', 'barcode'),
                'top' => (float) $request->input('layout.code.top', 42),
                'left' => (float) $request->input('layout.code.left', 55),
                'width' => (float) $request->input('layout.code.width', 26),
                'height' => (float) $request->input('layout.code.height', 8),
            ],
        ];

        $setting->student_card_layout = $layout;
        $setting->save();

        return redirect()->route('student-card-setting.index')->with('success', 'Desain kartu santri berhasil disimpan');
    }

    /**
     * Fetch students by school/classroom for the print tab via AJAX.
     */
    public function getStudents(Request $request)
    {
        $query = Student::with(['classroom', 'classroom.school', 'cardPrints.admin']);

        if ($request->filled('school_id')) {
            $query->whereHas('classroom', function ($q) use ($request) {
                $q->where('school_id', $request->school_id);
            });
        }

        if ($request->filled('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('nis', 'like', '%' . $search . '%');
            });
        }

        $students = $query->orderBy('name')->get();

        return response()->json($students->map(function ($s) {
            $lastPrint = $s->cardPrints->first();
            return [
                'id' => $s->id,
                'name' => $s->name,
                'nis' => $s->nis,
                'barcode' => $s->barcode,
                'classroom' => $s->classroom?->name ?? '-',
                'school' => $s->classroom?->school?->name ?? '-',
                'avatar' => $s->avatar ? asset($s->avatar) : null,
                'print_count' => $s->cardPrints->count(),
                'last_printed_at' => $lastPrint ? $lastPrint->printed_at->format('d M Y H:i') : null,
                'last_printed_by' => $lastPrint?->admin?->name ?? null,
            ];
        }));
    }

    /**
     * Generate and stream a PDF of selected student cards.
     */
    public function print(Request $request)
    {
        if (!Auth::user()->can('Manage Pengaturan Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
            'print_layout' => 'required|in:pvc,a4_2x4,a4_2x5',
        ]);

        $students = Student::with(['classroom', 'classroom.school'])
            ->whereIn('id', $request->student_ids)
            ->orderBy('name')
            ->get();

        $setting = ApplicationSetting::first();
        $layout = $setting->student_card_layout ?? ApplicationSetting::getDefaultStudentCardLayout();
        $background = $setting->student_card_image ? asset($setting->student_card_image) : '';
        $printLayout = $request->print_layout;

        // Log the printing event for each student
        foreach ($students as $student) {
            \App\Models\StudentCardPrint::create([
                'student_id' => $student->id,
                'printed_by' => Auth::id(),
                'print_layout' => $printLayout,
                'printed_at' => now(),
            ]);
        }

        // Generate barcodes / QR codes for each student
        $studentsData = $students->map(function ($student) use ($layout) {
            $codeHtml = '';
            if (($layout['code']['show'] ?? true) && $student->barcode) {
                $codeType = $layout['code']['type'] ?? 'barcode';
                if ($codeType === 'qrcode') {
                    $qr = QrCode::size(100)->generate($student->barcode);
                    $codeHtml = '<img src="data:image/svg+xml;base64,' . base64_encode($qr) . '" />';
                } else {
                    $codeHtml = DNS1D::getBarcodeHTML($student->barcode, 'C128', 1.2, 25);
                }
            }

            return [
                'student' => $student,
                'code_html' => $codeHtml,
            ];
        });

        if ($printLayout === 'pvc') {
            $pdf = PDF::loadView('admins.student-card-setting.pdf-pvc', [
                'studentsData' => $studentsData,
                'layout' => $layout,
                'background' => $background,
            ])->setPaper([0, 0, 242.65, 153.07], 'landscape'); // 85.6mm x 53.98mm in points
        } else {
            $cols = 2;
            $rows = $printLayout === 'a4_2x5' ? 5 : 4;
            $pdf = PDF::loadView('admins.student-card-setting.pdf-a4', [
                'studentsData' => $studentsData,
                'layout' => $layout,
                'background' => $background,
                'cols' => $cols,
                'rows' => $rows,
            ])->setPaper('a4', 'portrait');
        }

        $fileName = 'kartu_santri_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->stream($fileName);
    }
}
