<?php

namespace App\Http\Controllers\Admin;

use App\Models\School;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\SaldoHistory;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\DataTables;
use App\Imports\StudentImportData;
use App\Models\ApplicationSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\Admin\StudentRequest;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = Student::with('user', 'classroom.school')
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
                ->latest()->get();
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
                ->addColumn('action', function ($data) {
                    $actionShow = route('student.show', $data->id);
                    // $actionEdit = route('student.edit', $data->id);
                    $actionDelete = route('student.destroy', $data->id);
                    $actionPrint = route('student.generate-student-card', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        // view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.show', ['action' => $actionShow]) .
                        view('components.action.qr-code', ['action' => $actionPrint, 'label' => 'Cetak Kartu']) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action', 'saldo', 'classroom', 'school', 'status'])
                ->make(true);
        }
        $schools = School::orderBy('name')->get();
        return view('admins.student.index', compact('schools'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $schools = School::orderBy('name')->get();
        return view('admins.student.create-edit', compact('schools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudentRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('avatar')) {
            $data['avatar'] = 'storage/' . $request->file('avatar')->store('images/avatar', 'public');
        }
        Student::create($data);
        return redirect()->route('student.index')->with('success', 'Siswa berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
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
        $schools = School::orderBy('name')->get();
        $saldo = [
            'IN' => SaldoHistory::where('student_id', $student->id)
                ->where('type', SaldoHistory::TYPE_IN)->sum('amount'),
            'OUT' => SaldoHistory::where('student_id', $student->id)
                ->where('type', SaldoHistory::TYPE_OUT)->sum('amount'),
        ];
        return view('admins.student.create-edit', compact('student', 'schools', 'saldo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentRequest $request, Student $student)
    {
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
        file_exists($student->avatar) ? unlink($student->avatar) : '';
        $student->delete();
        return redirect()->route('student.index')->with('success', 'Siswa berhasil dihapus');
    }

    public function generateStudentCard($id)
    {
        $student = Student::findOrFail($id);
        // generate student card here and return the file to download
        $background = ApplicationSetting::first()->student_card_image;
        $qrCode = QrCode::size(300)->generate($student->barcode);
        // Convert the QR code to base64
        $qrCodeBase64 = base64_encode($qrCode);
        $pdf = PDF::loadView('admins.student-card.index', [
            'background' => $background,
            'student' => $student, 'qrCode' => $qrCodeBase64
        ])
            ->setPaper('a4', 'landscape');

        // Generate a random file name
        $fileName = 'student_card_' . $student->id . '_' . uniqid() . '.pdf';

        // Return the PDF file as a download response
        return $pdf->stream($fileName);
    }

    public function getClassrooms($id)
    {
        $classrooms = Classroom::where('school_id', $id)->orderBy('name')->get();
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
