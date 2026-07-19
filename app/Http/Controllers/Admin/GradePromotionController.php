<?php

namespace App\Http\Controllers\Admin;

use App\Models\School;
use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentClassroomHistory;
use App\Http\Requests\Admin\GradePromotionRequest;
use App\Models\Bill;
use App\Models\PaymentRate;

class GradePromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Kenaikan Kelas')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
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
                ->hasSchool()
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
                ->addColumn('action', function ($data) {
                    $actionEdit = route('student.edit', $data->id);
                    $actionDelete = route('student.destroy', $data->id);
                    $actionPrint = route('student.generate-student-card', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Kenaikan Kelas']) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Kenaikan Kelas']) .
                        view('components.action.qr-code', ['action' => $actionPrint, 'label' => 'Cetak Kartu']) .
                        "</div>";
                })
                ->rawColumns(['action', 'saldo', 'classroom', 'school', 'status'])
                ->make(true);
        }

        $schools = School::hasSchool()->orderBy('name', 'asc')->get();
        $academicYears = AcademicYear::orderBy('name', 'asc')->get();
        return view('admins.grade-promotion.index', compact('schools', 'academicYears'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(GradePromotionRequest $request)
    {
        if (!Auth::user()->can('Create Kenaikan Kelas')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        // Start the database transaction
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // 1. Hapus tagihan belum bayar (UNPAID) siswa pada tahun ajaran target untuk menghindari duplikasi
            Bill::whereIn('student_id', $data['student_ids'])
                ->where('academic_year_id', $data['academic_year_id'])
                ->where('status', Bill::STATUS_UNPAID)
                ->delete();

            // 2. Ambil tarif pembayaran (PaymentRate) reguler untuk kelas baru di tahun ajaran target
            $paymentRates = PaymentRate::where('type', PaymentRate::TYPE_REGULAR)
                ->whereHas('paymentRateClassrooms', function ($q) use ($data) {
                    $q->where('classroom_id', $data['new_classroom_id']);
                })
                ->whereHas('billType', function ($q) use ($data) {
                    $q->where('academic_year_id', $data['academic_year_id']);
                })
                ->with(['paymentRateItems', 'billType'])
                ->get();

            $billsToInsert = [];
            $timestamp = now();

            foreach ($data['student_ids'] as $studentId) {
                $student = Student::with('user')->find($studentId);
                if (!$student) continue;

                // Update kelas dan set status aktif agar tampil di backoffice dan PWA
                $student->update([
                    'classroom_id' => $data['new_classroom_id'],
                    'status' => Student::STATUS_ACTIVE,
                ]);

                // Buat riwayat kelas siswa
                StudentClassroomHistory::create([
                    'student_id' => $studentId,
                    'classroom_id' => $data['new_classroom_id'],
                    'academic_year_id' => $data['academic_year_id'],
                ]);

                // Generate tagihan baru berdasarkan tarif kelas baru
                foreach ($paymentRates as $paymentRate) {
                    // Prevent generating bills for years before the student's entry year
                    $startYear = $paymentRate->billType?->academicYear?->getStartYearSafe();
                    if ($startYear !== null && $student->getEntryYear() > $startYear) {
                        continue;
                    }

                    // Filter berdasarkan gender jika ada
                    if ($paymentRate->gender) {
                        $allowedGenders = array_map('trim', explode(',', $paymentRate->gender));
                        if (!in_array($student->gender, $allowedGenders)) {
                            continue;
                        }
                    }

                    // Filter berdasarkan status jamaah wali jika ada
                    if ($paymentRate->jamaah_status && $student->user) {
                        $allowedStatuses = array_map('trim', explode(',', $paymentRate->jamaah_status));
                        if (!in_array($student->user->jamaah_status, $allowedStatuses)) {
                            continue;
                        }
                    }

                    // Tambahkan tagihan untuk setiap item pembayaran
                    foreach ($paymentRate->paymentRateItems as $item) {
                        if ($item->amount <= 0) continue;

                        $billsToInsert[] = [
                            'id'                   => \Illuminate\Support\Str::uuid()->toString(),
                            'bill_type_id'         => $paymentRate->bill_type_id,
                            'student_id'           => $student->id,
                            'classroom_id'         => $data['new_classroom_id'],
                            'academic_year_id'     => $data['academic_year_id'],
                            'month'                => $item->month,
                            'amount'               => $item->amount,
                            'paid_amount'          => 0,
                            'status'               => Bill::STATUS_UNPAID,
                            'year'                 => $item->year,
                            'payment_rate_item_id' => $item->id,
                            'created_at'           => $timestamp,
                            'updated_at'           => $timestamp,
                        ];
                    }
                }
            }

            // Bulk Insert tagihan untuk performa optimal
            if (!empty($billsToInsert)) {
                foreach (array_chunk($billsToInsert, 500) as $chunk) {
                    Bill::insert($chunk);
                }
            }

            // Commit the transaction
            DB::commit();

            return redirect()->route('grade-promotion.index')->with('success', 'Berhasil Mengubah Kelas Siswa');
        } catch (\Exception $e) {
            // Rollback the transaction if an exception occurs
            Log::error($e);
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal Mengubah Kelas Siswa');
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
