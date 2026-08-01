<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\School;
use App\Models\Student;
use App\Models\BillType;
use App\Models\Classroom;
use App\Models\SaldoHistory;
use Illuminate\Http\Request;
use App\Models\SavingHistory;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\DataTables;
use App\Imports\StudentImportData;
use App\Models\ApplicationSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\Admin\StudentRequest;
use App\Models\Tahfidz;
use App\Models\Admin;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = Student::with('user', 'classroom.school')->hasSchool()
                ->when(request('school_id'), function ($query) {
                    $query->whereHas('classroom', function ($query) {
                        $query->where('school_id', request('school_id'));
                    });
                })
                ->when(request('classroom_id'), function ($query) {
                    $query->where('classroom_id', request('classroom_id'));
                })
                ->when(request('status'), function ($query) {
                    $query->where('status', request('status'));
                })
                ->latest();
            $activeAy = \App\Models\AcademicYear::where('is_active', true)->first();

            return DataTables::of($data)
                ->editColumn('saldo', function ($data) {
                    return '<span class="badge bg-success">Rp ' . number_format($data->saldo, 0, ',', '.') . '</span>';
                })
                ->addColumn('classroom', function ($data) {
                    return $data->classroom->name ?? 'Belum ada kelas';
                })
                ->addColumn('school', function ($data) {
                    return $data->classroom->school->name ?? 'Belum ada sekolah';
                })
                ->addColumn('status', function ($data) {
                    switch ($data->status) {
                        case 'ACTIVE':
                            return '<span class="badge bg-success">Aktif</span>';
                        case 'INACTIVE':
                            return '<span class="badge bg-danger">Tidak Aktif</span>';
                        case 'GRADUATED':
                            return '<span class="badge bg-warning">Lulus</span>';
                        case 'TRANSFERRED':
                            return '<span class="badge bg-info">Pindah</span>';
                        case 'DROPPED_OUT':
                            return '<span class="badge bg-secondary">Keluar</span>';
                        default:
                            return '<span class="badge bg-secondary">Tidak Diketahui</span>';
                    }
                })
                ->addColumn('student', function ($data) use ($activeAy) {
                    $studentName = $data?->name ? $data->name : '-';
                    $resolvedClass = $data->classroom ?? ($activeAy ? $data->getClassroomForAcademicYear($activeAy->id) : null);
                    $className = $resolvedClass?->name ?? '-';

                    // Use avatar_url accessor for proper absolute URL
                    $avatarUrl = $data->avatar_url ?? asset('assets/media/avatars/default.png');
                    $fallbackUrl = asset('assets/media/avatars/default.png');

                    // Return HTML structure for the card with avatar, name, and class
                    return '<div class="student-card" style="display: flex; align-items: center; gap: 10px;">
                        <img src="' . $avatarUrl . '" onerror="this.src=\'' . $fallbackUrl . '\'" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <div>
                            <div><strong>' . $studentName . '</strong></div>
                            <div>' . $className . '</div>
                        </div>
                    </div>';
                })
                ->addColumn('parent', function ($data) {
                    $userName = $data->user ? $data->user?->name : '-';
                    $userPhone = $data->user ? $data->user?->phone : '-';
                    $jamaahStatus = $data->user ? $data->user->jamaah_status : null;
                    
                    $badgeHtml = '';
                    if ($jamaahStatus === 'JAMAAH') {
                        $badgeHtml = '<div class="mt-1"><span class="badge badge-light-success fw-bolder px-2 py-1">Jamaah</span></div>';
                    } elseif ($jamaahStatus === 'NON_JAMAAH') {
                        $badgeHtml = '<div class="mt-1"><span class="badge badge-light-danger fw-bolder px-2 py-1">Non Jamaah</span></div>';
                    } elseif ($jamaahStatus === 'MUKIMIN') {
                        $badgeHtml = '<div class="mt-1"><span class="badge badge-light-primary fw-bolder px-2 py-1">Mukimin</span></div>';
                    }

                    // Use avatar_url accessor for proper absolute URL
                    $avatarUrl = $data->user?->avatar_url ?? asset('assets/media/avatars/default.png');

                    // Check if the phone number starts with '0'
                    $whatsappLink = null;
                    if ($userPhone !== '-' && substr($userPhone, 0, 1) === '0') {
                        // Replace the leading '0' with the country code (e.g., '62' for Indonesia)
                        $formattedPhone = '62' . substr($userPhone, 1);
                        $whatsappLink = 'https://wa.me/' . $formattedPhone;
                    }

                    // Return HTML structure for the card with avatar, name, and class
                    return '<div class="student-card" style="display: flex; align-items: center; gap: 10px;">
                        <img src="' . $avatarUrl . '" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <div>
                            <div><strong>' . $userName . '</strong></div>
                            <div>' .
                        ($whatsappLink
                            ? '<a href="' . $whatsappLink . '" target="_blank" style="text-decoration: none; color: inherit;">' . $userPhone . '</a>'
                            : $userPhone
                        ) .
                        '</div>' . $badgeHtml . '
                        </div>
                    </div>';
                })
                ->addColumn('action', function ($data) {
                    $actionShow = route('student.show', $data->id);
                    $actionEdit = route('student.edit', $data->id);
                    $actionDelete = route('student.destroy', $data->id);
                    $actionPrint = route('student.generate-student-card', $data->id);

                    $editBtnHtml = '';
                    if (Auth::user()->can('Edit Santri')) {
                        $editBtnHtml = "<button type='button' class='btn btn-icon btn-active-light-primary w-30px h-30px me-3 btn-edit-student' data-id='{$data->id}' data-url='{$actionEdit}' title='Edit Siswa'><i class='fas fa-edit'></i></button>";
                    }

                    return "<div class='d-flex justify-content-center align-items-center'>" .
                        "<button type='button' class='btn btn-icon btn-active-light-primary w-30px h-30px me-3 btn-detail-student' data-id='{$data->id}' data-url='{$actionShow}' title='Detail Siswa'><i class='fa fa-info-circle fs-3'></i></button>" .
                        $editBtnHtml .
                        view('components.action.qr-code', ['action' => $actionPrint, 'label' => 'Cetak Kartu']) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Santri']) .
                        "</div>";
                })
                ->rawColumns(['action', 'saldo', 'classroom', 'school', 'status', 'parent', 'student'])
                ->make(true);
        }
        $schools = School::hasSchool()->orderBy('name')->get();
        return view('admins.student.index', compact('schools'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->can('Create Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $schools = School::hasSchool()->orderBy('name')->get();
        $hosts = Admin::orderBy('name')->get();
        return view('admins.student.create-edit', compact('schools', 'hosts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudentRequest $request)
    {
        if (!Auth::user()->can('Create Santri')) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Maaf, Anda tidak memiliki akses untuk tindakan tersebut.'], 403);
            }
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        if ($request->hasFile('avatar')) {
            $data['avatar'] = 'storage/' . $request->file('avatar')->store('images/avatar', 'public');
        }
        if (empty($data['nickname']) && !empty($data['name'])) {
            $data['nickname'] = explode(' ', trim($data['name']))[0];
        }
        Student::create($data);
        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Siswa berhasil ditambahkan']);
        }
        return redirect()->route('student.index')->with('success', 'Siswa berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!Auth::user()->can('Manage Santri')) {
            if (request()->ajax()) {
                return response()->json(['error' => 'Maaf, Anda tidak memiliki akses untuk data ini.'], 403);
            }
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            try {
                // Validate scope before processing ajax
                $student = Student::with('classroom')->findOrFail($id);
                $admin = Auth::guard('web')->user();
                if ($admin && !$admin->hasRole('Super Admin')) {
                    $schoolIds = $admin->getSchoolIds();
                    if ($student->classroom && !in_array($student->classroom->school_id, $schoolIds)) {
                        return response()->json(['error' => 'Akses ditolak: Santri berada di luar cakupan UPT Anda.'], 403);
                    }
                }

                if (request()->type === 'saldo') {
                    $data = SaldoHistory::with('student')->where('student_id', $id)->latest();
                    return DataTables::of($data)
                        ->editColumn('amount', function ($data) {
                            if ($data->type === 'IN') {
                                return '<span class="badge bg-success">+' . $data->amount . '</span>';
                            } else {
                                return '<span class="badge bg-danger">-' . $data->amount . '</span>';
                            }
                        })
                        ->editColumn('status', function ($data) {
                            if ($data->status === SaldoHistory::STATUS_SUCCESS) {
                                return '<span class="badge bg-success">' . $data->status . '</span>';
                            } elseif ($data->status === SaldoHistory::STATUS_PENDING) {
                                return '<span class="badge bg-warning">' . $data->status . '</span>';
                            } else {
                                return '<span class="badge bg-danger">' . $data->status . '</span>';
                            }
                        })
                        ->rawColumns(['amount', 'status'])
                        ->make(true);
                }

                if (request()->type === 'saving') {
                    $data = SavingHistory::with('student')->where('student_id', $id)->latest();
                    return DataTables::of($data)
                        ->editColumn('date', function ($data) {
                            Carbon::setLocale('id'); // Set locale to Indonesian
                            return Carbon::parse($data->created_at)
                                ->translatedFormat('d F Y'); // Format tanggal dalam bahasa Indonesia
                        })
                        ->editColumn('amount', function ($data) {
                            if ($data->type === 'IN') {
                                return '<span class="badge bg-success">+' . number_format($data->amount, 0, ',', '.') . '</span>';
                            } else {
                                return '<span class="badge bg-danger">-' . number_format($data->amount, 0, ',', '.') . '</span>';
                            }
                        })
                        ->editColumn('status', function ($data) {
                            if ($data->status === SavingHistory::STATUS_SUCCESS) {
                                return '<span class="badge bg-success">' . $data->status . '</span>';
                            } elseif ($data->status === SavingHistory::STATUS_PENDING) {
                                return '<span class="badge bg-warning">' . $data->status . '</span>';
                            } else {
                                return '<span class="badge bg-danger">' . $data->status . '</span>';
                            }
                        })
                        ->rawColumns(['amount', 'status'])
                        ->make(true);
                }

                if (request()->type === 'bill') {
                    $data = BillType::with([
                        'billItem', 
                        'academicYear', 
                        'bills' => function ($query) use ($id) {
                            $query->where('student_id', $id);
                        }
                    ])
                    ->whereHas('bills', fn($query) => $query->where('student_id', $id))
                    ->latest()
                    ->get();

                    return DataTables::of($data)
                        ->addColumn('total_unpaid', function ($data) use ($id) {
                            $totalUnpaid = $data->bills->where('student_id', $id)->sum('remaining_amount');
                            return 'Rp. ' . number_format($totalUnpaid, 0, ',', '.');
                        })
                        ->addColumn('total_paid', function ($data) use ($id) {
                            $totalPaid = $data->bills->where('student_id', $id)->sum('paid_amount');
                            return 'Rp. ' . number_format($totalPaid, 0, ',', '.');
                        })
                        ->addColumn('total', function ($data) use ($id) {
                            $total = $data->bills->where('student_id', $id)->sum('amount');
                            return 'Rp. ' . number_format($total, 0, ',', '.');
                        })
                        ->addColumn('status', function ($data) use ($id) {
                            $totalPaid = $data->bills->where('student_id', $id)->sum('paid_amount');
                            $total = $data->bills->where('student_id', $id)->sum('amount');
                            if ($totalPaid == 0) {
                                return '<span class="badge bg-danger">Belum Bayar</span>';
                            } elseif ($totalPaid < $total) {
                                return '<span class="badge bg-warning">Belum Lunas</span>';
                            } else {
                                return '<span class="badge bg-success">Lunas</span>';
                            }
                        })
                        ->rawColumns(['status'])
                        ->make(true);
                }

                if (request()->type === 'tahfidz') {
                    $data = Tahfidz::with('student')->where('student_id', $id)->latest();
                    return DataTables::of($data)
                        ->editColumn('link', function ($data) {
                            return "<a href='$data->link' target='_blank'><i class='fas fa-external-link-alt'></i></a>";
                        })
                        ->editColumn('deposit_date', function ($data) {
                            return Carbon::parse($data->deposit_date)->format('d M Y');
                        })
                        ->rawColumns(['link'])
                        ->make(true);
                }

                // If AJAX request without type parameter, return partial modal body HTML
                $student = Student::with(['user', 'classroom.school', 'asramaHost', 'asrama.hostAdmin'])->findOrFail($id);
                $saldo = [
                    'IN' => SaldoHistory::where('student_id', $student->id)->where('type', SaldoHistory::TYPE_IN)->sum('amount'),
                    'OUT' => SaldoHistory::where('student_id', $student->id)->where('type', SaldoHistory::TYPE_OUT)->sum('amount'),
                ];
                return response()->json([
                    'html' => view('admins.student.partials.detail-modal-body', compact('student', 'saldo'))->render()
                ]);

            } catch (\Exception $e) {
                Log::error('Student detail tabs AJAX error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
                return response()->json(['error' => 'Internal server error: ' . $e->getMessage()], 500);
            }
        }


        $student = Student::with(['user', 'classroom.school', 'asramaHost', 'asrama.hostAdmin'])->findOrFail($id);
        $admin = Auth::guard('web')->user();
        if ($admin && !$admin->hasRole('Super Admin')) {
            $schoolIds = $admin->getSchoolIds();
            if ($student->classroom && !in_array($student->classroom->school_id, $schoolIds)) {
                return redirect()->back()->with('error', 'Akses ditolak: Santri berada di luar cakupan UPT Anda.');
            }
        }

        $saldo = [
            'IN' => SaldoHistory::where('student_id', $student->id)
                ->where('type', SaldoHistory::TYPE_IN)->sum('amount'),
            'OUT' => SaldoHistory::where('student_id', $student->id)
                ->where('type', SaldoHistory::TYPE_OUT)->sum('amount'),
        ];
        return view('admins.student.show', compact('student', 'saldo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        if (!Auth::user()->can('Edit Santri')) {
            if (request()->ajax()) {
                return response()->json(['error' => 'Maaf, Anda tidak memiliki akses untuk halaman tersebut.'], 403);
            }
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $admin = Auth::guard('web')->user();
        if ($admin && !$admin->hasRole('Super Admin')) {
            $schoolIds = $admin->getSchoolIds();
            if ($student->classroom && !in_array($student->classroom->school_id, $schoolIds)) {
                if (request()->ajax()) {
                    return response()->json(['error' => 'Akses ditolak: Santri berada di luar cakupan UPT Anda.'], 403);
                }
                return redirect()->back()->with('error', 'Akses ditolak: Santri berada di luar cakupan UPT Anda.');
            }
        }

        $student->load(['user', 'classroom.school', 'asramaHost', 'asrama.hostAdmin']);

        $schools = School::hasSchool()->orderBy('name')->get();
        $hosts = Admin::orderBy('name')->get();
        $saldo = [
            'IN' => SaldoHistory::where('student_id', $student->id)
                ->where('type', SaldoHistory::TYPE_IN)->sum('amount'),
            'OUT' => SaldoHistory::where('student_id', $student->id)
                ->where('type', SaldoHistory::TYPE_OUT)->sum('amount'),
        ];

        if (request()->ajax()) {
            return response()->json([
                'html' => view('admins.student.partials.edit-modal-body', compact('student', 'schools', 'saldo', 'hosts'))->render()
            ]);
        }

        return view('admins.student.create-edit', compact('student', 'schools', 'saldo', 'hosts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentRequest $request, Student $student)
    {
        if (!Auth::user()->can('Edit Santri')) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Maaf, Anda tidak memiliki akses untuk halaman tersebut.'], 403);
            }
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $admin = Auth::guard('web')->user();
        if ($admin && !$admin->hasRole('Super Admin')) {
            $schoolIds = $admin->getSchoolIds();
            if ($student->classroom && !in_array($student->classroom->school_id, $schoolIds)) {
                if ($request->ajax()) {
                    return response()->json(['error' => 'Akses ditolak: Santri berada di luar cakupan UPT Anda.'], 403);
                }
                return redirect()->back()->with('error', 'Akses ditolak: Santri berada di luar cakupan UPT Anda.');
            }
        }

        $data = $request->validated();
        if ($request->hasFile('avatar')) {
            file_exists($student->avatar) ? unlink($student->avatar) : '';
            $data['avatar'] = 'storage/' . $request->file('avatar')->store('images/avatar', 'public');
        }
        $oldStatus = $student->status;
        $student->update($data);
        if ($student->status !== Student::STATUS_ACTIVE && $oldStatus !== $student->status) {
            $student->cleanupFutureUnpaidBills();
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Siswa berhasil diubah'
            ]);
        }

        return redirect()->route('student.index')->with('success', 'Siswa berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        if (!Auth::user()->can('Delete Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $admin = Auth::guard('web')->user();
        if ($admin && !$admin->hasRole('Super Admin')) {
            $schoolIds = $admin->getSchoolIds();
            if ($student->classroom && !in_array($student->classroom->school_id, $schoolIds)) {
                return redirect()->back()->with('error', 'Akses ditolak: Santri berada di luar cakupan UPT Anda.');
            }
        }

        file_exists($student->avatar) ? unlink($student->avatar) : '';
        $student->delete();
        return redirect()->route('student.index')->with('success', 'Siswa berhasil dihapus');
    }

    public function generateStudentCard($id)
    {
        if (!Auth::user()->can('Manage Kartu Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $student = Student::with(['classroom', 'classroom.school'])->findOrFail($id);
        
        // Ambil template Kartu Santri aktif
        $template = \App\Models\CardTemplate::where('type', 'student_card')
            ->where('is_active', true)
            ->first();

        $layout = $template ? $template->layout : ApplicationSetting::getDefaultStudentCardLayout();
        $cardImage = $template ? $template->background_image : null;
        
        $background = '';
        if ($cardImage) {
            if (file_exists(public_path($cardImage))) {
                $background = public_path($cardImage);
            } else {
                $background = storage_asset($cardImage);
            }
        }

        // Generate barcode / QR code
        $codeHtml = '';
        if (($layout['code']['show'] ?? true) && $student->barcode) {
            $codeType = $layout['code']['type'] ?? 'barcode';
            if ($codeType === 'qrcode') {
                $qr = QrCode::size(100)->generate($student->barcode);
                $codeHtml = '<img src="data:image/svg+xml;base64,' . base64_encode($qr) . '" />';
            } else {
                $dns1d = new \Milon\Barcode\DNS1D();
                $codeHtml = '<img src="data:image/png;base64,' . $dns1d->getBarcodePNG($student->barcode, 'C128', 2, 40) . '" style="width: 100%; height: 100%; display: block;" />';
            }
        }

        $studentsData = collect([[
            'student' => $student,
            'code_html' => $codeHtml,
        ]]);

        // Log riwayat cetak
        \App\Models\StudentCardPrint::create([
            'student_id' => $student->id,
            'card_template_id' => $template?->id,
            'printed_by' => Auth::id(),
            'print_layout' => 'pvc',
            'printed_at' => now(),
        ]);

        $pdf = PDF::loadView('admins.student-card-setting.pdf-pvc', [
            'studentsData' => $studentsData,
            'layout' => $layout,
            'background' => $background,
        ])->setPaper([0, 0, 242.65, 153.07], 'landscape'); // 85.6mm x 53.98mm in points

        $fileName = 'kartu_santri_' . $student->nis . '_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->stream($fileName);
    }

    public function getClassrooms($id)
    {
        $classrooms = Classroom::where('school_id', $id)->orderByRaw("CAST(name AS UNSIGNED) ASC, name ASC")->get();
        return response()->json($classrooms);
    }

    public function importPreview(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xls,xlsx'
            ]);

            // Save file temporarily
            $file = $request->file('file');
            $fileName = 'student_import_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $tempPath = $file->storeAs('temp_imports', $fileName, 'local');

            $filePath = storage_path('app/' . $tempPath);
            $rows = Excel::toArray(new StudentImportData, $filePath)[0] ?? [];

            if (empty($rows)) {
                Storage::delete($tempPath);
                return redirect()->route('student.index')->with('error', 'File Excel kosong atau tidak terbaca');
            }

            $previewData = [];
            $summary = [
                'total' => 0,
                'valid' => 0,
                'new_wali' => 0,
                'existing_wali' => 0,
                'errors' => 0
            ];

            foreach ($rows as $index => $row) {
                // skip if nis and nama both null (usually empty rows at end of excel)
                if (empty($row['nis']) && empty($row['nama'])) {
                    continue;
                }

                $summary['total']++;
                $rowErrors = [];
                $rowWarnings = [];
                $waliStatus = 'N/A';
                $waliName = $row['nama_wali'] ?? $row['nama_orang_tua'] ?? $row['wali_nama'] ?? null;

                // 1. Validate NIS & Name
                if (empty($row['nis'])) {
                    $rowErrors[] = 'NIS wajib diisi';
                }
                if (empty($row['nama'])) {
                    $rowErrors[] = 'Nama Siswa wajib diisi';
                }

                // 2. Validate Classroom
                $className = trim($row['kelas'] ?? '');
                $classroom = null;
                if (empty($className)) {
                    $rowErrors[] = 'Kelas wajib diisi';
                } else {
                    $classroomQuery = Classroom::where('name', $className);
                    
                    $admin = Auth::user();
                    if ($admin && !$admin->hasRole('Super Admin')) {
                        $schoolIds = method_exists($admin, 'getSchoolIds') ? $admin->getSchoolIds() : ($admin->adminSchool ? $admin->adminSchool->pluck('school_id')->toArray() : []);
                        $classroomQuery->whereIn('school_id', $schoolIds);
                    }
                    
                    $classroom = $classroomQuery->first();
                    if (!$classroom) {
                        $rowErrors[] = "Kelas '{$className}' tidak ditemukan di database (atau Anda tidak memiliki akses ke sekolah kelas ini)";
                    }
                }

                // 3. Process Phone WA / Wali Status
                $phone = $row['no_wali'] ?? null;
                if (!empty($phone) && $phone !== '-') {
                    // Format phone number
                    $phone = preg_replace('/[^0-9]/', '', $phone);
                    if (substr($phone, 0, 1) !== '0' && strlen($phone) > 1) {
                        $phone = '0' . $phone;
                    }
                    if (substr($phone, 0, 2) == '62') {
                        $phone = '0' . substr($phone, 2);
                    }

                    $existingUser = User::where('phone', $phone)->first();
                    if ($existingUser) {
                        $waliStatus = 'Terdaftar';
                        $row['no_wali_formatted'] = $phone;
                        $row['resolved_wali_name'] = $existingUser->name;
                        $summary['existing_wali']++;
                    } else {
                        $waliStatus = 'Baru';
                        $row['no_wali_formatted'] = $phone;
                        $row['resolved_wali_name'] = $waliName ?: 'Wali ' . ($row['nama'] ?? 'Siswa');
                        $summary['new_wali']++;
                    }
                } else {
                    $rowWarnings[] = 'Tidak ada nomor wali (Siswa tidak akan terhubung ke wali)';
                }

                if (count($rowErrors) > 0) {
                    $summary['errors']++;
                    $status = 'ERROR';
                } else {
                    $summary['valid']++;
                    $status = 'READY';
                }

                $previewData[] = [
                    'row_number' => $index + 2, // 1-based + 1 for header
                    'nis' => $row['nis'] ?? '-',
                    'name' => $row['nama'] ?? '-',
                    'class' => $className ?: '-',
                    'gender' => $row['jenis_kelamin'] ?? '-',
                    'wali_name' => $waliName ?: ($row['nama'] ? 'Wali ' . $row['nama'] : '-'),
                    'wali_phone' => $phone ?: '-',
                    'wali_status' => $waliStatus,
                    'status' => $status,
                    'errors' => $rowErrors,
                    'warnings' => $rowWarnings
                ];
            }

            return view('admins.student.import-preview', [
                'previewData' => $previewData,
                'summary' => $summary,
                'tempFile' => $fileName
            ]);

        } catch (\Exception $e) {
            Log::error('Import preview failed: ' . $e->getMessage());
            return redirect()->route('student.index')->with('error', 'Gagal memproses file import: ' . $e->getMessage());
        }
    }

    public function importConfirm(Request $request)
    {
        try {
            $tempFile = $request->input('temp_file');
            if (empty($tempFile)) {
                return redirect()->route('student.index')->with('error', 'File import tidak valid');
            }

            $tempPath = 'temp_imports/' . $tempFile;
            if (!Storage::disk('local')->exists($tempPath)) {
                return redirect()->route('student.index')->with('error', 'File pratinjau import telah kedaluwarsa atau hilang');
            }

            $filePath = storage_path('app/' . $tempPath);
            $rows = Excel::toArray(new StudentImportData, $filePath)[0] ?? [];

            DB::transaction(function () use ($rows) {
                foreach ($rows as $row) {
                    if (empty($row['nis']) && empty($row['nama'])) {
                        continue;
                    }

                    // 1. Normalisasi phone
                    $phone = $row['no_wali'] ?? null;
                    $user = null;
                    if (!empty($phone) && $phone !== '-') {
                        $phone = preg_replace('/[^0-9]/', '', $phone);
                        if (substr($phone, 0, 1) !== '0' && strlen($phone) > 1) {
                            $phone = '0' . $phone;
                        }
                        if (substr($phone, 0, 2) == '62') {
                            $phone = '0' . substr($phone, 2);
                        }

                        // Cek user wali
                        $user = User::where('phone', $phone)->first();
                        if (!$user) {
                            // Map Nama Wali
                            $waliName = $row['nama_wali'] ?? $row['nama_orang_tua'] ?? $row['wali_nama'] ?? null;
                            if (empty($waliName)) {
                                $waliName = 'Wali ' . $row['nama'];
                            }

                            // Map gender wali jika ada
                            $genderWaliInput = strtolower(trim($row['jenis_kelamin_wali'] ?? ''));
                            $genderWali = null;
                            if ($genderWaliInput === 'l' || $genderWaliInput === 'laki-laki' || $genderWaliInput === 'laki laki') {
                                $genderWali = 'L';
                            } elseif ($genderWaliInput === 'p' || $genderWaliInput === 'perempuan') {
                                $genderWali = 'P';
                            }

                            // Map status jamaah wali jika ada
                            $statusJamaahInput = strtoupper(trim($row['status_jamaah_wali'] ?? ''));
                            $statusJamaahNormalized = str_replace([' ', '-'], '_', $statusJamaahInput);
                            
                            $statusJamaah = 'NON_JAMAAH';
                            if ($statusJamaahNormalized === 'JAMAAH') {
                                $statusJamaah = 'JAMAAH';
                            } elseif (in_array($statusJamaahNormalized, ['NON_JAMAAH', 'BUKAN_JAMAAH', 'NONJAMAAH'])) {
                                $statusJamaah = 'NON_JAMAAH';
                            } elseif ($statusJamaahNormalized === 'MUKIMIN') {
                                $statusJamaah = 'MUKIMIN';
                            }

                            $passwordInput = trim($row['password_wali'] ?? $row['password'] ?? '');
                            $password = $passwordInput ? bcrypt($passwordInput) : bcrypt('Wali123');

                            $user = User::create([
                                'name' => $waliName,
                                'email' => null, // Email is optional and hidden
                                'phone' => $phone,
                                'gender' => $genderWali,
                                'status' => 'ACTIVE',
                                'jamaah_status' => $statusJamaah,
                                'password' => $password
                            ]);
                        }
                    }

                    // 2. Cek kelas
                    $className = trim($row['kelas'] ?? '');
                    $classroomQuery = Classroom::where('name', $className);
                    
                    $admin = Auth::user();
                    if ($admin && !$admin->hasRole('Super Admin')) {
                        $schoolIds = method_exists($admin, 'getSchoolIds') ? $admin->getSchoolIds() : ($admin->adminSchool ? $admin->adminSchool->pluck('school_id')->toArray() : []);
                        $classroomQuery->whereIn('school_id', $schoolIds);
                    }
                    
                    $classroom = $classroomQuery->first();
                    if (!$classroom) {
                        Log::warning("Skipped importing student {$row['nama']} because classroom '{$className}' was not found or not accessible.");
                        continue;
                    }

                    // 3. Parsing tanggal lahir
                    $birthDate = null;
                    if (!empty($row['tanggal_lahir'])) {
                        try {
                            if (is_numeric($row['tanggal_lahir'])) {
                                $birthDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tanggal_lahir'])->format('Y-m-d');
                            } else {
                                $birthDate = \Carbon\Carbon::parse($row['tanggal_lahir'])->format('Y-m-d');
                            }
                        } catch (\Exception $e) {
                            Log::warning("Gagal parsing tanggal lahir untuk siswa {$row['nama']}: " . $e->getMessage());
                        }
                    }

                    // 4. Nickname fallback
                    $nickname = trim($row['nama_panggilan'] ?? '');
                    if (empty($nickname)) {
                        $nickname = explode(' ', trim($row['nama']))[0];
                    }

                    // 5. Cek apakah siswa terdaftar
                    $student = Student::where('nis', $row['nis'])->first();
                    $studentData = [
                        'nis' => $row['nis'],
                        'name' => $row['nama'],
                        'nickname' => $nickname,
                        'nisn' => $row['nisn'] ?? null,
                        'born_place' => $row['tempat_lahir'] ?? null,
                        'birth_date' => $birthDate,
                        'address' => $row['alamat'] ?? null,
                        'city' => $row['kota'] ?? null,
                        'province' => $row['provinsi'] ?? null,
                        'user_id' => $user ? $user->id : null,
                        'gender' => strtoupper($row['jenis_kelamin'] ?? '') ?: null,
                        'classroom_id' => $classroom->id,
                        'status' => Student::STATUS_ACTIVE,
                    ];

                    if ($student) {
                        $student->update($studentData);
                    } else {
                        Student::create($studentData);
                    }
                }
            });

            // Hapus file temp
            Storage::disk('local')->delete($tempPath);

            return redirect()->route('student.index')->with('success', 'Data berhasil diimpor dan disinkronkan dengan data Wali.');

        } catch (\Exception $e) {
            Log::error('Import confirm failed: ' . $e->getMessage());
            return redirect()->route('student.index')->with('error', 'Terjadi kesalahan saat memproses final import data: ' . $e->getMessage());
        }
    }

    public function bulkDestroy(Request $request)
    {
        if (!Auth::user()->can('Delete Santri')) {
            return response()->json(['success' => false, 'message' => 'Maaf, Anda tidak memiliki akses untuk menghapus data santri'], 403);
        }

        $ids = $request->input('ids');
        if (empty($ids) || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data santri yang dipilih'], 400);
        }

        try {
            DB::transaction(function () use ($ids) {
                $students = Student::whereIn('id', $ids)->get();
                foreach ($students as $student) {
                    if ($student->avatar && file_exists($student->avatar)) {
                        unlink($student->avatar);
                    }
                    $student->delete();
                }
            });

            return response()->json(['success' => true, 'message' => 'Berhasil menghapus data santri terpilih']);
        } catch (\Exception $e) {
            Log::error('Bulk delete students failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data santri terpilih: ' . $e->getMessage()], 500);
        }
    }
}
