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
                ->addColumn('student', function ($data) {
                    $studentName = $data?->name ? $data->name : '-';
                    $className = $data->classroom?->name ? $data->classroom->name : '-';

                    // Use avatar_url accessor for proper absolute URL
                    $avatarUrl = $data->avatar_url ?? asset('assets/media/avatars/default.png');

                    // Return HTML structure for the card with avatar, name, and class
                    return '<div class="student-card" style="display: flex; align-items: center; gap: 10px;">
                        <img src="' . $avatarUrl . '" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <div>
                            <div><strong>' . $studentName . '</strong></div>
                            <div>' . $className . '</div>
                        </div>
                    </div>';
                })
                ->addColumn('parent', function ($data) {
                    $userName = $data->user ? $data->user?->name : '-';
                    $userPhone = $data->user ? $data->user?->phone : '-';

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
                        '</div>
                        </div>
                    </div>';
                })
                ->addColumn('action', function ($data) {
                    $actionShow = route('student.show', $data->id);
                    // $actionEdit = route('student.edit', $data->id);
                    $actionDelete = route('student.destroy', $data->id);
                    $actionPrint = route('student.generate-student-card', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        // view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.show', ['action' => $actionShow]) .
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
        return redirect()->route('student.index')->with('success', 'Siswa berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!Auth::user()->can('Manage Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax() && request()->type === 'saldo') {
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

        if (request()->ajax() && request()->type === 'saving') {
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
        if (request()->ajax() && request()->type === 'bill') {
            $data = BillType::with('billItem', 'academicYear', 'bills')
                ->whereHas('bills', fn($query) => $query->where('student_id', $id))
                ->latest()
                ->get();

            return DataTables::of($data)
                ->addColumn('total_unpaid', function ($data) use ($id) {
                    $totalUnpaid = $data->bills->where('student_id', $id)->where('status', 'UNPAID')->sum('amount');
                    return 'Rp. ' . number_format($totalUnpaid, 0, ',', '.');
                })
                ->addColumn('total_paid', function ($data) use ($id) {
                    $totalPaid = $data->bills->where('student_id', $id)->where('status', 'PAID')->sum('amount');
                    return 'Rp. ' . number_format($totalPaid, 0, ',', '.');
                })
                ->addColumn('total', function ($data) use ($id) {
                    $total = $data->bills->where('student_id', $id)->sum('amount');
                    return 'Rp. ' . number_format($total, 0, ',', '.');
                })
                ->addColumn('status', function ($data) use ($id) {
                    $totalPaid = $data->bills->where('student_id', $id)->where('status', 'PAID')->sum('amount');
                    $total = $data->bills->where('student_id', $id)->sum('amount');
                    if ($totalPaid === 0) {
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

        if (request()->ajax() && request()->type === 'tahfidz') {
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


        $student = Student::with('user', 'classroom.school')->findOrFail($id);
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
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $schools = School::hasSchool()->orderBy('name')->get();
        $hosts = Admin::orderBy('name')->get();
        $saldo = [
            'IN' => SaldoHistory::where('student_id', $student->id)
                ->where('type', SaldoHistory::TYPE_IN)->sum('amount'),
            'OUT' => SaldoHistory::where('student_id', $student->id)
                ->where('type', SaldoHistory::TYPE_OUT)->sum('amount'),
        ];
        return view('admins.student.create-edit', compact('student', 'schools', 'saldo', 'hosts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentRequest $request, Student $student)
    {
        if (!Auth::user()->can('Edit Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        if ($request->hasFile('avatar')) {
            file_exists($student->avatar) ? unlink($student->avatar) : '';
            $data['avatar'] = 'storage/' . $request->file('avatar')->store('images/avatar', 'public');
        }
        $student->update($data);
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

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xls,xlsx'
            ]);

            return DB::transaction(function () use ($request) {
                Excel::import(new StudentImportData, $request->file('file'));
                return redirect()->route('student.index')->with('success', 'Data berhasil diimpor');
            });
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Import failed: ' . $e->getMessage());

            // Return with an error message or handle the exception as needed
            return redirect()->route('student.index')->with('error', 'Terjadi kesalahan dalam mengimpor data');
        }
    }
}
