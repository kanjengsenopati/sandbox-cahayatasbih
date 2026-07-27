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
                }, function ($query) {
                    // Default to ACTIVE students for migration if no status filter provided
                    $query->where('status', Student::STATUS_ACTIVE);
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
     * Store a newly created resource in storage.
     */
    public function store(GradePromotionRequest $request)
    {
        if (!Auth::user()->can('Create Kenaikan Kelas')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk tindakan tersebut');
        }

        DB::beginTransaction();

        try {
            $data = $request->validated();
            $migrationType = $data['migration_type']; // 'transfer' or 'promotion'
            $studentIds = array_unique($data['student_ids']);
            $newClassroomId = $data['new_classroom_id'];
            $academicYearId = $data['academic_year_id'];

            // Fetch payment rates for the new classroom & target academic year
            $paymentRates = PaymentRate::where('type', PaymentRate::TYPE_REGULAR)
                ->whereHas('paymentRateClassrooms', function ($q) use ($newClassroomId) {
                    $q->where('classroom_id', $newClassroomId);
                })
                ->whereHas('billType', function ($q) use ($academicYearId) {
                    $q->where('academic_year_id', $academicYearId);
                })
                ->with(['paymentRateItems', 'billType'])
                ->get();

            $billsToInsert = [];
            $timestamp = now();

            foreach ($studentIds as $studentId) {
                $student = Student::with('user')->find($studentId);
                if (!$student) continue;

                // Process only active students to protect inactive/graduated student states
                if ($student->status !== Student::STATUS_ACTIVE && $migrationType === 'transfer') {
                    continue;
                }

                // 1. Update Student Classroom & ensure Active status for promotion
                $student->update([
                    'classroom_id' => $newClassroomId,
                    'status'       => Student::STATUS_ACTIVE,
                ]);

                // 2. Upsert Student Classroom History (Prevent duplicate history records)
                StudentClassroomHistory::updateOrCreate(
                    [
                        'student_id'       => $studentId,
                        'academic_year_id' => $academicYearId,
                    ],
                    [
                        'classroom_id'     => $newClassroomId,
                    ]
                );

                if ($migrationType === 'transfer') {
                    // --- MODE 1: PINDAH KELAS / PLOTTING (Tahun Ajaran Sama) ---
                    
                    // 3a. Update classroom_id on ALL existing bills of the student for this academic year
                    Bill::where('student_id', $studentId)
                        ->where('academic_year_id', $academicYearId)
                        ->update(['classroom_id' => $newClassroomId]);

                    // Fetch current bills for checking missing rate items
                    $existingBills = Bill::where('student_id', $studentId)
                        ->where('academic_year_id', $academicYearId)
                        ->get();

                    $existingMap = [];
                    foreach ($existingBills as $eb) {
                        $key = $eb->bill_type_id . '_' . $eb->month . '_' . $eb->year;
                        $existingMap[$key] = $eb;
                    }

                    // Generate missing payment rate items for the new classroom if any
                    foreach ($paymentRates as $paymentRate) {
                        $startYear = $paymentRate->billType?->academicYear?->getStartYearSafe();
                        if ($startYear !== null && $student->getEntryYear() > $startYear) {
                            continue;
                        }

                        if ($paymentRate->gender) {
                            $allowedGenders = array_map('trim', explode(',', $paymentRate->gender));
                            if (!in_array($student->gender, $allowedGenders)) {
                                continue;
                            }
                        }

                        if ($paymentRate->jamaah_status && $student->user) {
                            $allowedStatuses = array_map('trim', explode(',', $paymentRate->jamaah_status));
                            if (!in_array($student->user->jamaah_status, $allowedStatuses)) {
                                continue;
                            }
                        }

                        foreach ($paymentRate->paymentRateItems as $item) {
                            if ($item->amount <= 0) continue;

                            $key = $paymentRate->bill_type_id . '_' . $item->month . '_' . $item->year;

                            if (!isset($existingMap[$key])) {
                                // Missing bill for new classroom rate: Create sterile unpaid bill
                                $billsToInsert[] = [
                                    'id'                   => \Illuminate\Support\Str::uuid()->toString(),
                                    'bill_type_id'         => $paymentRate->bill_type_id,
                                    'student_id'           => $student->id,
                                    'classroom_id'         => $newClassroomId,
                                    'academic_year_id'     => $academicYearId,
                                    'month'                => $item->month,
                                    'amount'               => $item->amount,
                                    'paid_amount'          => 0,
                                    'status'               => Bill::STATUS_UNPAID,
                                    'year'                 => $item->year,
                                    'payment_rate_item_id' => $item->id,
                                    'created_at'           => $timestamp,
                                    'updated_at'           => $timestamp,
                                ];
                            } else {
                                // If bill exists and is purely UNPAID with amount mismatch, update amount to new class rate
                                $existingBill = $existingMap[$key];
                                if ($existingBill->status === Bill::STATUS_UNPAID && (int)$existingBill->paid_amount === 0 && (int)$existingBill->amount !== (int)$item->amount) {
                                    $existingBill->update([
                                        'amount'               => $item->amount,
                                        'payment_rate_item_id' => $item->id,
                                    ]);
                                }
                            }
                        }
                    }

                } else {
                    // --- MODE 2: KENAIKAN KELAS (Tahun Ajaran Baru) ---

                    // 3b. Delete ONLY pure UNPAID bills (paid_amount == 0) for target academic year in case of re-run
                    Bill::where('student_id', $studentId)
                        ->where('academic_year_id', $academicYearId)
                        ->where('status', Bill::STATUS_UNPAID)
                        ->where('paid_amount', 0)
                        ->delete();

                    // Fetch existing PAID / PARTIAL bills to prevent double billing
                    $existingPaidBills = Bill::where('student_id', $studentId)
                        ->where('academic_year_id', $academicYearId)
                        ->get();

                    $paidKeys = [];
                    foreach ($existingPaidBills as $pb) {
                        $key = $pb->bill_type_id . '_' . $pb->month . '_' . $pb->year;
                        $paidKeys[$key] = true;
                    }

                    // Generate new bills for target academic year
                    foreach ($paymentRates as $paymentRate) {
                        $startYear = $paymentRate->billType?->academicYear?->getStartYearSafe();
                        if ($startYear !== null && $student->getEntryYear() > $startYear) {
                            continue;
                        }

                        if ($paymentRate->gender) {
                            $allowedGenders = array_map('trim', explode(',', $paymentRate->gender));
                            if (!in_array($student->gender, $allowedGenders)) {
                                continue;
                            }
                        }

                        if ($paymentRate->jamaah_status && $student->user) {
                            $allowedStatuses = array_map('trim', explode(',', $paymentRate->jamaah_status));
                            if (!in_array($student->user->jamaah_status, $allowedStatuses)) {
                                continue;
                            }
                        }

                        foreach ($paymentRate->paymentRateItems as $item) {
                            if ($item->amount <= 0) continue;

                            $key = $paymentRate->bill_type_id . '_' . $item->month . '_' . $item->year;

                            // STRICT ANTI-DUPLICATE: Skip if a bill already exists for this exact tuple
                            if (isset($paidKeys[$key])) {
                                continue;
                            }

                            $billsToInsert[] = [
                                'id'                   => \Illuminate\Support\Str::uuid()->toString(),
                                'bill_type_id'         => $paymentRate->bill_type_id,
                                'student_id'           => $student->id,
                                'classroom_id'         => $newClassroomId,
                                'academic_year_id'     => $academicYearId,
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
            }

            // Bulk Insert new bills safely in chunks
            if (!empty($billsToInsert)) {
                foreach (array_chunk($billsToInsert, 500) as $chunk) {
                    Bill::insert($chunk);
                }
            }

            DB::commit();

            $msg = $migrationType === 'transfer' ? 'Berhasil Memindahkan Kelas Siswa' : 'Berhasil Memproses Kenaikan Kelas Siswa';
            return redirect()->route('academic.index', ['tab' => 'grade-promotion'])->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Grade Promotion Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request'   => $request->all(),
            ]);

            return redirect()->back()->with('error', 'Gagal Memproses Migrasi Siswa: ' . $e->getMessage());
        }
    }
}
