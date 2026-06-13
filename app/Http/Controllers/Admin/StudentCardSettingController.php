<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\School;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\BillType;
use App\Models\Bill;
use App\Models\CardTemplate;
use App\Models\ApplicationSetting;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentCardSettingRequest;
use App\Http\Requests\Admin\CardTemplateRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Milon\Barcode\DNS1D;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StudentCardSettingController extends Controller
{
    /**
     * Show the card template management and print page.
     */
    public function index()
    {
        $canSantri = Auth::user()->can('Manage Kartu Santri');
        $canUjian = Auth::user()->can('Manage Kartu Ujian');

        if (!$canSantri && !$canUjian) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        // Ambil template berdasarkan permission
        $query = CardTemplate::with('academicYear');
        if (!$canSantri) {
            $query->where('type', 'exam_card');
        } elseif (!$canUjian) {
            $query->where('type', 'student_card');
        }
        $templates = $query->orderBy('type')->orderBy('created_at', 'desc')->get();

        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        $billTypes = BillType::with(['academicYear', 'billItem'])->orderBy('name')->get();
        $schools = School::hasSchool()->orderBy('name')->get();

        // Cari template aktif default untuk pencetakan awal
        $activeTemplates = CardTemplate::where('is_active', true)->get();

        return view('admins.student-card-setting.index', compact(
            'templates', 'academicYears', 'billTypes', 'schools', 'activeTemplates', 'canSantri', 'canUjian'
        ));
    }

    /**
     * Store a new card template metadata.
     */
    public function storeTemplate(CardTemplateRequest $request)
    {
        $type = $request->type;
        $permission = $type === 'student_card' ? 'Manage Kartu Santri' : 'Manage Kartu Ujian';

        if (!Auth::user()->can($permission)) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk membuat template jenis ini');
        }

        $data = $request->validated();
        
        // Default layout kosong dari setting
        $data['layout'] = ApplicationSetting::getDefaultStudentCardLayout();
        $data['is_active'] = $request->boolean('is_active');

        // Jika diset aktif, nonaktifkan template lain bertipe sama
        if ($data['is_active']) {
            CardTemplate::where('type', $type)->update(['is_active' => false]);
        }

        CardTemplate::create($data);

        return redirect()->route('student-card-setting.index')->with('success', 'Template kartu baru berhasil dibuat');
    }

    /**
     * Update card template metadata.
     */
    public function updateTemplate(CardTemplateRequest $request, $id)
    {
        $template = CardTemplate::findOrFail($id);
        $permission = $template->type === 'student_card' ? 'Manage Kartu Santri' : 'Manage Kartu Ujian';

        if (!Auth::user()->can($permission)) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk mengubah template ini');
        }

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        // Jika tipe diubah (walau jarang), sesuaikan permission
        $newPermission = $data['type'] === 'student_card' ? 'Manage Kartu Santri' : 'Manage Kartu Ujian';
        if (!Auth::user()->can($newPermission)) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk mengubah tipe template');
        }

        // Jika diset aktif, nonaktifkan template lain bertipe sama
        if ($data['is_active']) {
            CardTemplate::where('type', $data['type'])->where('id', '!=', $id)->update(['is_active' => false]);
        }

        $template->update($data);

        return redirect()->route('student-card-setting.index')->with('success', 'Metadata template kartu berhasil diperbarui');
    }

    /**
     * Delete a template.
     */
    public function destroyTemplate($id)
    {
        $template = CardTemplate::findOrFail($id);
        $permission = $template->type === 'student_card' ? 'Manage Kartu Santri' : 'Manage Kartu Ujian';

        if (!Auth::user()->can($permission)) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk menghapus template ini');
        }

        // Hapus background image dari disk secara aman
        if ($template->background_image) {
            $oldPath = str_replace('storage/', '', $template->background_image);
            Storage::disk('public')->delete($oldPath);
        }

        $template->delete();

        return redirect()->route('student-card-setting.index')->with('success', 'Template kartu berhasil dihapus');
    }

    /**
     * Toggle the active status of a template.
     */
    public function toggleActive($id)
    {
        $template = CardTemplate::findOrFail($id);
        $permission = $template->type === 'student_card' ? 'Manage Kartu Santri' : 'Manage Kartu Ujian';

        if (!Auth::user()->can($permission)) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk mengubah status template ini');
        }

        $newStatus = !$template->is_active;

        if ($newStatus) {
            // Matikan yang lain bertipe sama
            CardTemplate::where('type', $template->type)->update(['is_active' => false]);
        }

        $template->is_active = $newStatus;
        $template->save();

        return redirect()->route('student-card-setting.index')->with('success', 'Status keaktifan template berhasil diubah');
    }

    /**
     * Show the designer page for a template.
     */
    public function design($id)
    {
        $template = CardTemplate::findOrFail($id);
        $permission = $template->type === 'student_card' ? 'Manage Kartu Santri' : 'Manage Kartu Ujian';

        if (!Auth::user()->can($permission)) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk mendesain template ini');
        }

        $layout = $template->layout ?? ApplicationSetting::getDefaultStudentCardLayout();
        $background = $template->background_image ?? '';

        return view('admins.student-card-setting.design', compact('template', 'layout', 'background'));
    }

    /**
     * Save the designer layout for a template.
     */
    public function storeDesign(StudentCardSettingRequest $request, $id)
    {
        $template = CardTemplate::findOrFail($id);
        $permission = $template->type === 'student_card' ? 'Manage Kartu Santri' : 'Manage Kartu Ujian';

        if (!Auth::user()->can($permission)) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk mendesain template ini');
        }

        // Handle background image upload
        if ($request->hasFile('student_card_image')) {
            if ($template->background_image) {
                $oldPath = str_replace('storage/', '', $template->background_image);
                Storage::disk('public')->delete($oldPath);
            }
            $imagePath = $request->file('student_card_image')->store('images/student-card', 'public');
            $template->background_image = 'storage/' . $imagePath;
        }

        // Build layout coordinates from inputs
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
                'text' => $request->input('layout.title.text', $template->type === 'exam_card' ? 'Kartu Ujian' : 'Kartu Santri'),
                'color' => $request->input('layout.title.color', '#FFFF00'),
                'font_size' => (int) $request->input('layout.title.font_size', 12),
                'top' => (float) $request->input('layout.title.top', 5),
                'left' => (float) $request->input('layout.title.left', 45),
                'text_align' => $request->input('layout.title.text_align', 'right'),
                'font_weight' => $request->input('layout.title.font_weight', 'bold'),
                'font_family' => $request->input('layout.title.font_family', 'Raleway'),
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
                'font_family' => $request->input('layout.subtitle.font_family', 'Raleway'),
            ],
            'photo' => [
                'show' => $request->boolean('layout.photo.show'),
                'top' => (float) $request->input('layout.photo.top', 18),
                'left' => (float) $request->input('layout.photo.left', 5),
                'width' => (float) $request->input('layout.photo.width', 18),
                'height' => (float) $request->input('layout.photo.height', 24),
                'border_radius' => (float) $request->input('layout.photo.border_radius', 0),
            ],
            'name' => [
                'show' => $request->boolean('layout.name.show'),
                'color' => $request->input('layout.name.color', '#FFFFFF'),
                'font_size' => (int) $request->input('layout.name.font_size', 12),
                'top' => (float) $request->input('layout.name.top', 20),
                'left' => (float) $request->input('layout.name.left', 25),
                'font_weight' => $request->input('layout.name.font_weight', 'bold'),
                'font_family' => $request->input('layout.name.font_family', 'Raleway'),
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
                'font_family' => $request->input('layout.classroom.font_family', 'Raleway'),
            ],
            'school' => [
                'show' => $request->boolean('layout.school.show'),
                'color' => $request->input('layout.school.color', '#FFFFFF'),
                'font_size' => (int) $request->input('layout.school.font_size', 9),
                'top' => (float) $request->input('layout.school.top', 40),
                'left' => (float) $request->input('layout.school.left', 25),
                'font_weight' => $request->input('layout.school.font_weight', 'bold'),
                'font_family' => $request->input('layout.school.font_family', 'Raleway'),
            ],
            'nickname' => [
                'show' => $request->boolean('layout.nickname.show'),
                'color' => $request->input('layout.nickname.color', '#FFFFFF'),
                'font_size' => (int) $request->input('layout.nickname.font_size', 10),
                'top' => (float) $request->input('layout.nickname.top', 24),
                'left' => (float) $request->input('layout.nickname.left', 25),
                'font_weight' => $request->input('layout.nickname.font_weight', 'normal'),
                'font_family' => $request->input('layout.nickname.font_family', 'Raleway'),
            ],
            'city' => [
                'show' => $request->boolean('layout.city.show'),
                'color' => $request->input('layout.city.color', '#FFFFFF'),
                'font_size' => (int) $request->input('layout.city.font_size', 9),
                'top' => (float) $request->input('layout.city.top', 31),
                'left' => (float) $request->input('layout.city.left', 25),
                'font_weight' => $request->input('layout.city.font_weight', 'normal'),
                'font_family' => $request->input('layout.city.font_family', 'Raleway'),
            ],
            'province' => [
                'show' => $request->boolean('layout.province.show'),
                'color' => $request->input('layout.province.color', '#FFFFFF'),
                'font_size' => (int) $request->input('layout.province.font_size', 9),
                'top' => (float) $request->input('layout.province.top', 31),
                'left' => (float) $request->input('layout.province.left', 50),
                'font_weight' => $request->input('layout.province.font_weight', 'normal'),
                'font_family' => $request->input('layout.province.font_family', 'Raleway'),
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

        $template->layout = $layout;
        $template->save();

        return redirect()->route('student-card-setting.index')->with('success', 'Desain layout template kartu berhasil disimpan');
    }

    /**
     * Fetch students by school/classroom with bill eligibility checking via AJAX.
     */
    public function getStudents(Request $request)
    {
        try {
            $hasCardPrintsTable = \Illuminate\Support\Facades\Schema::hasTable('student_card_prints');
            
            $eagerLoads = ['classroom', 'classroom.school'];
            if ($hasCardPrintsTable) {
                $eagerLoads[] = 'cardPrints.admin';
            }

            $query = Student::with($eagerLoads)->hasSchool()->select('students.*');
 
            if ($request->filled('school_id')) {
                $query->whereHas('classroom', function ($q) use ($request) {
                    $q->where('school_id', $request->school_id);
                });
            }
 
            if ($request->filled('classroom_id')) {
                $query->where('students.classroom_id', $request->classroom_id);
            }
 
            if ($request->filled('q')) {
                $search = $request->q;
                $query->where(function ($q) use ($search) {
                    $q->where('students.name', 'like', '%' . $search . '%')
                      ->orWhere('students.nis', 'like', '%' . $search . '%');
                });
            }
 
            $limit = (int) $request->input('limit', 10);
            if (!in_array($limit, [10, 20, 40])) {
                $limit = 10;
            }

            // Sorting logic
            $sortBy = $request->input('sort_by', 'name');
            $sortDir = $request->input('sort_dir', 'asc');
            $sortDirection = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'asc';

            if ($sortBy === 'classroom') {
                $query->leftJoin('classrooms', 'students.classroom_id', '=', 'classrooms.id')
                      ->select('students.*')
                      ->orderBy('classrooms.name', $sortDirection);
            } elseif ($sortBy === 'school') {
                $query->leftJoin('classrooms', 'students.classroom_id', '=', 'classrooms.id')
                      ->leftJoin('schools', 'classrooms.school_id', '=', 'schools.id')
                      ->select('students.*')
                      ->orderBy('schools.name', $sortDirection);
            } elseif ($sortBy === 'print_count') {
                if ($hasCardPrintsTable) {
                    $query->withCount('cardPrints')->orderBy('card_prints_count', $sortDirection);
                } else {
                    $query->orderBy('students.name', $sortDirection);
                }
            } else {
                $sortColumn = $sortBy === 'nis' ? 'nis' : 'name';
                $query->orderBy('students.' . $sortColumn, $sortDirection);
            }
 
            // Muat syarat tagihan dari template jika exam_card
            $requiredBillTypeIds = [];
            $template = null;
            if ($request->filled('template_id')) {
                $template = CardTemplate::find($request->template_id);
                if ($template && $template->type === 'exam_card') {
                    $requiredBillTypeIds = $template->exam_bill_requirements ?? [];
                }
            }

            // Filter lunas jika eligible_only aktif dan bertipe exam_card
            if ($request->boolean('eligible_only') && !empty($requiredBillTypeIds)) {
                $query->whereDoesntHave('bills', function ($q) use ($requiredBillTypeIds) {
                    $q->whereIn('bill_type_id', $requiredBillTypeIds)
                      ->where('status', Bill::STATUS_UNPAID);
                });
            }

            $students = $query->paginate($limit);

            return response()->json([
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
                'from' => $students->firstItem(),
                'to' => $students->lastItem(),
                'data' => $students->getCollection()->map(function ($s) use ($hasCardPrintsTable, $requiredBillTypeIds) {
                    $printCount = 0;
                    $lastPrintedAt = null;
                    $lastPrintedBy = null;

                    if ($hasCardPrintsTable) {
                        $lastPrint = $s->cardPrints->first();
                        $printCount = $s->cardPrints->count();
                        $lastPrintedAt = $lastPrint ? $lastPrint->printed_at->format('d M Y H:i') : null;
                        $lastPrintedBy = $lastPrint?->admin?->name ?? null;
                    }

                    // Deteksi tunggakan tagihan kustom (historis semua tahun ajaran sebelumnya)
                    $isEligible = true;
                    $unpaidBills = [];
                    if (!empty($requiredBillTypeIds)) {
                        $unpaid = Bill::where('student_id', $s->id)
                            ->whereIn('bill_type_id', $requiredBillTypeIds)
                            ->where('status', Bill::STATUS_UNPAID)
                            ->with(['billType.billItem', 'billType.academicYear'])
                            ->get();

                        if ($unpaid->count() > 0) {
                            $isEligible = false;
                            $unpaidBills = $unpaid->map(function ($b) {
                                if (!$b->billType) return 'Tagihan';
                                $unit = $b->billType->billItem->name ?? '';
                                $year = $b->billType->academicYear->name ?? '';
                                $suffix = array_filter([$unit, $year]);
                                return $b->billType->name . (!empty($suffix) ? ' (' . implode(' - ', $suffix) . ')' : '');
                            })->unique()->toArray();
                        }
                    }

                    return [
                        'id' => $s->id,
                        'name' => $s->name,
                        'nis' => $s->nis,
                        'barcode' => $s->barcode,
                        'classroom' => $s->classroom?->name ?? '-',
                        'school' => $s->classroom?->school?->name ?? '-',
                        'avatar' => $s->avatar ? asset($s->avatar) : null,
                        'print_count' => $printCount,
                        'last_printed_at' => $lastPrintedAt,
                        'last_printed_by' => $lastPrintedBy,
                        'is_eligible' => $isEligible,
                        'unpaid_bills' => array_values($unpaidBills),
                    ];
                })
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('getStudents error: ' . $e->getMessage());
            return response()->json([
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 10,
                'total' => 0,
                'from' => null,
                'to' => null,
                'data' => [],
                'error' => 'Gagal memuat data santri: ' . $e->getMessage(),
            ], 200);
        }
    }

    /**
     * Generate and stream a PDF of selected cards.
     */
    public function print(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
            'print_layout' => 'required|in:pvc,a4_1x1,a4_2x2,a4_2x3,a4_2x4,a4_2x5',
            'template_id' => 'required|exists:card_templates,id',
        ]);

        $template = CardTemplate::findOrFail($request->template_id);
        $permission = $template->type === 'student_card' ? 'Manage Kartu Santri' : 'Manage Kartu Ujian';

        if (!Auth::user()->can($permission)) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk mencetak jenis kartu ini');
        }

        $students = Student::with(['classroom', 'classroom.school'])
            ->whereIn('id', $request->student_ids)
            ->orderBy('name')
            ->get();

        $layout = $template->layout ?? ApplicationSetting::getDefaultStudentCardLayout();
        $cardImage = $template->background_image;
        
        $background = '';
        if ($cardImage) {
            if (file_exists(public_path($cardImage))) {
                $background = public_path($cardImage);
            } else {
                $background = storage_asset($cardImage);
            }
        }
        $printLayout = $request->print_layout;

        // Log printing events for each student
        foreach ($students as $student) {
            \App\Models\StudentCardPrint::create([
                'student_id' => $student->id,
                'card_template_id' => $template->id,
                'printed_by' => Auth::id(),
                'print_layout' => $printLayout,
                'printed_at' => now(),
            ]);
        }

        // Generate barcodes / QR codes
        $dns1d = new DNS1D();
        $studentsData = $students->map(function ($student) use ($layout, $dns1d) {
            $codeHtml = '';
            if (($layout['code']['show'] ?? true) && $student->barcode) {
                $codeType = $layout['code']['type'] ?? 'barcode';
                if ($codeType === 'qrcode') {
                    $qr = QrCode::size(100)->generate($student->barcode);
                    $codeHtml = '<img src="data:image/svg+xml;base64,' . base64_encode($qr) . '" />';
                } else {
                    $codeHtml = '<img src="data:image/png;base64,' . $dns1d->getBarcodePNG($student->barcode, 'C128', 2, 40) . '" style="width: 100%; height: 100%; display: block;" />';
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
            if ($printLayout === 'a4_1x1') {
                $cols = 1;
                $rows = 1;
            } elseif ($printLayout === 'a4_2x2') {
                $cols = 2;
                $rows = 2;
            } elseif ($printLayout === 'a4_2x3') {
                $cols = 2;
                $rows = 3;
            } elseif ($printLayout === 'a4_2x5') {
                $cols = 2;
                $rows = 5;
            } else { // a4_2x4
                $cols = 2;
                $rows = 4;
            }

            $pdf = PDF::loadView('admins.student-card-setting.pdf-a4', [
                'studentsData' => $studentsData,
                'layout' => $layout,
                'background' => $background,
                'cols' => $cols,
                'rows' => $rows,
            ])->setPaper('a4', 'portrait');
        }

        $fileName = 'kartu_' . $template->type . '_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->stream($fileName);
    }
}
