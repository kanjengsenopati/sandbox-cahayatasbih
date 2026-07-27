<?php

namespace App\Http\Controllers\Admin;

use App\Models\School;
use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentGraduationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentGraduationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Kelulusan Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = Student::with(['user', 'classroom.school', 'bills.billType', 'bills.academicYear'])
                ->whereHas('classroom.school', function ($query) {
                    $query->whereIn('type', [School::TYPE_SMP, School::TYPE_MA]);
                })
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->whereHas('classroom', function ($sub) {
                            $sub->whereHas('school', function ($sch) {
                                $sch->where('type', School::TYPE_SMP);
                            })->where('name', 'like', '9%');
                        });
                    })->orWhere(function ($q) {
                        $q->whereHas('classroom', function ($sub) {
                            $sub->whereHas('school', function ($sch) {
                                $sch->where('type', School::TYPE_MA);
                            })->where('name', 'like', '12%');
                        });
                    });
                })
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
                ->whereNot('status', Student::STATUS_GRADUATED)
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
                ->addColumn('unpaid_bills', function ($data) {
                    $unpaid = $data->bills->where('status', \App\Models\Bill::STATUS_UNPAID);
                    if ($unpaid->isEmpty()) {
                        return '<span class="badge bg-light-success text-success fw-bolder px-3 py-1">Lunas / Bersih</span>';
                    }

                    $billsArray = [];
                    foreach ($unpaid as $bill) {
                        $billName = $bill->billType->name ?? 'Tagihan';
                        $monthName = $bill->month ? \Carbon\Carbon::parse($bill->year . '-' . sprintf('%02d', $bill->month) . '-01')->translatedFormat('F') : '-';
                        $ayName = $bill->academicYear->name ?? ($bill->year ?? '-');
                        $billsArray[] = [
                            'name' => $billName,
                            'month' => $monthName,
                            'academic_year' => $ayName,
                            'amount' => (float) $bill->amount,
                            'formatted_amount' => 'Rp ' . number_format($bill->amount, 0, ',', '.')
                        ];
                    }

                    $billsJson = htmlspecialchars(json_encode($billsArray), ENT_QUOTES, 'UTF-8');
                    $studentName = htmlspecialchars($data->name, ENT_QUOTES, 'UTF-8');
                    $studentNis = htmlspecialchars($data->nis ?? '-', ENT_QUOTES, 'UTF-8');

                    return '<button type="button" class="btn btn-sm btn-light-danger btn-unpaid-details fw-bolder px-3 py-1 shadow-sm" ' .
                        'data-student-name="' . $studentName . '" ' .
                        'data-student-nis="' . $studentNis . '" ' .
                        'data-bills=\'' . $billsJson . '\'>' .
                        '<i class="fa-solid fa-list-check me-1"></i> Tunggakan (' . $unpaid->count() . ')' .
                        '</button>';
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
                ->rawColumns(['action', 'saldo', 'classroom', 'school', 'status', 'unpaid_bills'])
                ->make(true);
        }

        $schools = School::hasSchool()
            ->whereIn('type', [School::TYPE_SMP, School::TYPE_MA])
            ->orderBy('name', 'asc')
            ->get();
        $academicYears = AcademicYear::orderBy('name', 'asc')->get();
        return view('admins.student-graduation.index', compact('schools', 'academicYears'));
    }

    public function getClassroom(Request $request)
    {
        $schoolId = $request->school_id;
        $school = School::find($schoolId);

        $query = \App\Models\Classroom::where('school_id', $schoolId);

        if ($school) {
            if ($school->type === School::TYPE_SMP) {
                $query->where('name', 'like', '9%');
            } elseif ($school->type === School::TYPE_MA) {
                $query->where('name', 'like', '12%');
            }
        }

        $classrooms = $query->orderByRaw("CAST(name AS UNSIGNED) ASC, name ASC")->get();
        return response()->json([
            'code' => '200',
            'message' => 'Success',
            'data' => $classrooms
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudentGraduationRequest $request)
    {
        $data = $request->validated();
        $studentIds = $data['student_ids'];
        $graduationOption = $request->input('graduation_option') ?? $request->input('next_action') ?? 'lanjut_studi';

        DB::beginTransaction();
        try {
            $students = Student::with(['classroom.school'])->whereIn('id', $studentIds)->get();

            // Cache schools and transit classrooms
            $maSchool = School::where('type', School::TYPE_MA)
                ->orWhere('name', 'like', '%ALIYAH%')
                ->orWhere('name', 'like', '%MA%')
                ->first();

            $maTransitClassId = null;
            if ($maSchool) {
                $maClass = \App\Models\Classroom::firstOrCreate(
                    ['school_id' => $maSchool->id, 'name' => '10-Transit'],
                    ['created_at' => now(), 'updated_at' => now()]
                );
                $maTransitClassId = $maClass->id;
            }

            $pondokSchool = School::where('type', School::TYPE_PONDOK)
                ->orWhere('name', 'like', '%PPTQ%')
                ->orWhere('name', 'like', '%PONDOK%')
                ->first();

            $pondokTransitClassId = null;
            if ($pondokSchool) {
                $pondokClass = \App\Models\Classroom::firstOrCreate(
                    ['school_id' => $pondokSchool->id, 'name' => 'Pondok-Transit'],
                    ['created_at' => now(), 'updated_at' => now()]
                );
                $pondokTransitClassId = $pondokClass->id;
            }

            $countProcessed = 0;

            foreach ($students as $student) {
                $schoolType = $student->classroom->school->type ?? null;

                if ($graduationOption === 'lanjut_studi' || $graduationOption === 'lanjut_pondok') {
                    // Mode Lanjut Studi (Melanjutkan ke UPT Berikutnya)
                    if ($schoolType === School::TYPE_SMP) {
                        // SMP -> MA (Kelas 10-Transit)
                        if ($maTransitClassId) {
                            $student->update([
                                'classroom_id' => $maTransitClassId,
                                'status' => Student::STATUS_ACTIVE,
                            ]);
                        }
                    } elseif ($schoolType === School::TYPE_MA) {
                        // MA -> Pondok (Kelas Pondok-Transit)
                        if ($pondokTransitClassId) {
                            $student->update([
                                'classroom_id' => $pondokTransitClassId,
                                'status' => Student::STATUS_ACTIVE,
                            ]);
                        }
                    } else {
                        // Fallback if Pondok / Other
                        if ($pondokTransitClassId) {
                            $student->update([
                                'classroom_id' => $pondokTransitClassId,
                                'status' => Student::STATUS_ACTIVE,
                            ]);
                        }
                    }
                } else {
                    // Mode Keluar (Lulus Murni / Selesai)
                    $student->update([
                        'status' => Student::STATUS_GRADUATED,
                    ]);
                    // Cleanup future unbilled months beyond graduation date, while preserving all past unpaid bills
                    $student->cleanupFutureUnpaidBills();
                }

                $countProcessed++;
            }

            DB::commit();
            return redirect()->back()->with('success', "Berhasil memproses kelulusan $countProcessed siswa.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing student graduation: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses kelulusan siswa: ' . $e->getMessage());
        }
    }
}
