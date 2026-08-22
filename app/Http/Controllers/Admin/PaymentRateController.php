<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bill;
use App\Models\User;
use App\Models\School;
use App\Models\Student;
use App\Models\BillType;
use App\Models\Classroom;
use App\Models\PaymentRate;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\SendNotifWaService;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\Admin\PaymentRateRequest;

class PaymentRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $billType = BillType::with('billItem')->findOrFail(request()->get('bill_type_id'));
        
        $posBayarName = $billType->billItem?->name;
        $schoolsQuery = School::hasSchool();
        
        if ($posBayarName && School::where('type', $posBayarName)->exists()) {
            $schoolsQuery->where('type', $posBayarName);
        }
        
        $schools = $schoolsQuery->orderBy('name')->get();
        $classroomValue = [];

        $isMatrixMode = $billType->use_wali_filter && $billType->use_alumni_filter;

        if ($isMatrixMode) {
            $existingClassroomIds = [];
        } else {
            $existingClassroomIds = DB::table('payment_rate_classrooms')
                ->join('payment_rates', 'payment_rate_classrooms.payment_rate_id', '=', 'payment_rates.id')
                ->join('bill_types', 'payment_rates.bill_type_id', '=', 'bill_types.id')
                ->where(function($q) use ($billType) {
                    $q->where('payment_rates.bill_type_id', $billType->id)
                      ->orWhere(function($sub) use ($billType) {
                          $sub->where('bill_types.name', $billType->name)
                              ->where('bill_types.academic_year_id', $billType->academic_year_id);
                      });
                })
                ->where('payment_rates.type', PaymentRate::TYPE_REGULAR)
                ->whereNull('payment_rates.deleted_at')
                ->whereNull('payment_rate_classrooms.deleted_at')
                ->pluck('payment_rate_classrooms.classroom_id')
                ->unique()
                ->values()
                ->toArray();
        }

        $studentSubStatuses = \App\Models\StudentSubStatus::where('is_active', true)->get();

        return view('admins.payment-rate.create-edit', compact('billType', 'schools', 'classroomValue', 'existingClassroomIds', 'studentSubStatuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentRateRequest $request)
    {
        // 1. ATOMIC LOCK: Mencegah tombol diklik 2x (Anti Double Submit) & Race Condition antar admin
        // Kunci berbasis kombinasi Tagihan dan Kelas untuk mengizinkan generate tagihan kelas berbeda secara bersamaan
        $classroomsArray = (array)$request->classrooms;
        sort($classroomsArray);
        $classroomsStr = !empty($classroomsArray) ? implode('_', $classroomsArray) : 'all';
        $lockKey = md5($request->school_id . '_' . $request->bill_type_id . '_' . $request->amount . '_' . $classroomsStr);
        $lock = Cache::lock('store_payment_rate_' . $lockKey, 60);

        if (!$lock->get()) {
            return redirect()->back()->with('error', 'Proses sedang berjalan, mohon tunggu sebentar...');
        }

        try {
            DB::beginTransaction();

            $billType = BillType::findOrFail($request->bill_type_id);

            // PPTQ MATRIX MODE LOGIC
            if ($request->has('is_matrix_pptq') && $request->is_matrix_pptq == 1) {
                $matrixPrices = $request->matrix_price_pptq;
                if (!$matrixPrices || empty($request->classrooms)) {
                     throw new \Exception("Data matriks atau kelas belum dipilih.");
                }

                // Backend Protection: Validate classrooms match the selected school
                if ($request->school_id) {
                    $mismatchedClasses = Classroom::whereIn('id', $request->classrooms)
                        ->where('school_id', '!=', $request->school_id)
                        ->pluck('name')
                        ->toArray();
                    if (!empty($mismatchedClasses)) {
                        throw new \Exception("Gagal: Kelas (" . implode(', ', $mismatchedClasses) . ") tidak berada di bawah sekolah yang dipilih.");
                    }
                }

                // SMART DETACH ALGORITHM for PPTQ
                $legacyClassroomLinks = DB::table('payment_rate_classrooms')
                    ->join('payment_rates', 'payment_rate_classrooms.payment_rate_id', '=', 'payment_rates.id')
                    ->where('payment_rates.bill_type_id', $billType->id)
                    ->where('payment_rates.type', PaymentRate::TYPE_REGULAR)
                    ->whereNull('payment_rates.deleted_at')
                    ->whereIn('payment_rate_classrooms.classroom_id', $request->classrooms)
                    ->pluck('payment_rate_classrooms.id');
                    
                if ($legacyClassroomLinks->isNotEmpty()) {
                    DB::table('payment_rate_classrooms')
                        ->whereIn('id', $legacyClassroomLinks)
                        ->update(['deleted_at' => now()]);
                }

                // BULK CREATE MATRIX RATES
                $months = ($billType->type == BillType::TYPE_MONTHLY) ? range(1, 12) : ($request->months ?? [7]);
                $ratesToDispatch = [];

                foreach ($matrixPrices as $subStatusId => $priceInput) {
                    $cleanAmount = (int) preg_replace('/[^0-9]/', '', (string)$priceInput);
                    if ($cleanAmount <= 0) continue;

                    // Create Parent Rate
                    $paymentRate = $billType->paymentRates()->create([
                        'amount' => $cleanAmount,
                        'type' => PaymentRate::TYPE_REGULAR,
                        'gender' => null, 
                        'jamaah_status' => null,
                        'alumni_status' => null,
                        'student_sub_status_id' => $subStatusId,
                    ]);

                    // Attach Classrooms
                    foreach ($request->classrooms as $classroomId) {
                        $paymentRate->paymentRateClassrooms()->create([
                            'classroom_id' => $classroomId,
                        ]);
                    }

                    // Create Items
                    foreach ($months as $month) {
                        if ($billType->type == BillType::TYPE_MONTHLY) {
                            $itemYear = $request->{"tahun_$month"} ?? ($billType->academicYear->start_year ?? date('Y'));
                        } else {
                            $itemYear = $request->year ?? ($billType->academicYear->start_year ?? date('Y'));
                        }
                        $paymentRate->paymentRateItems()->create([
                            'month'  => $month,
                            'year'   => $itemYear,
                            'amount' => $cleanAmount,
                        ]);
                    }

                    $ratesToDispatch[] = $paymentRate->id;
                }

                DB::commit();
                $lock->release();

                // SINKRONISASI LANGSUNG (Synchronous Execution)
                foreach ($ratesToDispatch as $rId) {
                    try {
                        \Illuminate\Support\Facades\Artisan::call('bills:sync-rate', [
                            '--rate' => $rId,
                            '--force' => true,
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning("bills:sync-rate synchronous fallback warning: " . $e->getMessage());
                    }
                }

                return redirect()->route('bill-type.show', $billType->id)
                    ->with('success', 'Matriks Tarif PPTQ berhasil dibuat dan tagihan siswa telah disinkronkan.');
            }
            // MATRIX MODE LOGIC (LEGACY / REGULAR)
            elseif ($request->has('is_matrix') && $request->is_matrix == 1) {
                $matrixPrices = $request->matrix_price;
                if (!$matrixPrices || empty($request->classrooms)) {
                     throw new \Exception("Data matriks atau kelas belum dipilih.");
                }

                // Backend Protection: Validate classrooms match the selected school
                if ($request->school_id) {
                    $mismatchedClasses = Classroom::whereIn('id', $request->classrooms)
                        ->where('school_id', '!=', $request->school_id)
                        ->pluck('name')
                        ->toArray();
                    if (!empty($mismatchedClasses)) {
                        throw new \Exception("Gagal: Kelas (" . implode(', ', $mismatchedClasses) . ") tidak berada di bawah sekolah yang dipilih.");
                    }
                }

                // 1. SMART DETACH ALGORITHM (Legacy Conflict Resolution)
                // Lepaskan (detach) kelas yang dipilih dari tarif-tarif lama di BillType ini
                $legacyClassroomLinks = DB::table('payment_rate_classrooms')
                    ->join('payment_rates', 'payment_rate_classrooms.payment_rate_id', '=', 'payment_rates.id')
                    ->where('payment_rates.bill_type_id', $billType->id)
                    ->where('payment_rates.type', PaymentRate::TYPE_REGULAR)
                    ->whereNull('payment_rates.deleted_at')
                    ->whereIn('payment_rate_classrooms.classroom_id', $request->classrooms)
                    ->pluck('payment_rate_classrooms.id');
                    
                if ($legacyClassroomLinks->isNotEmpty()) {
                    DB::table('payment_rate_classrooms')
                        ->whereIn('id', $legacyClassroomLinks)
                        ->update(['deleted_at' => now()]); // Soft delete from pivot table
                }

                // Juga update alumni_status tarif lama menjadi NON_ALUMNI
                $legacyRates = PaymentRate::where('bill_type_id', $billType->id)
                    ->where('type', PaymentRate::TYPE_REGULAR)
                    ->where(function($q) {
                        $q->whereNull('alumni_status')->orWhere('alumni_status', '');
                    })->get();
                
                foreach ($legacyRates as $legacyRate) {
                    $legacyRate->update(['alumni_status' => 'NON_ALUMNI']);
                }

                // 2. BULK CREATE MATRIX RATES
                $months = ($billType->type == BillType::TYPE_MONTHLY) ? range(1, 12) : ($request->months ?? [7]);
                $ratesToDispatch = [];

                foreach ($matrixPrices as $alumniStatus => $jamaahArr) {
                    foreach ($jamaahArr as $jamaahStatus => $priceInput) {
                        $cleanAmount = (int) preg_replace('/[^0-9]/', '', (string)$priceInput);
                        if ($cleanAmount <= 0) continue;

                        $dbAlumni = ($alumniStatus === 'ALL') ? null : $alumniStatus;
                        $dbJamaah = ($jamaahStatus === 'ALL') ? null : $jamaahStatus;

                        // Create Parent Rate
                        $paymentRate = $billType->paymentRates()->create([
                            'amount' => $cleanAmount,
                            'type' => PaymentRate::TYPE_REGULAR,
                            'gender' => null, 
                            'jamaah_status' => $dbJamaah,
                            'alumni_status' => $dbAlumni,
                        ]);

                        // Attach Classrooms
                        foreach ($request->classrooms as $classroomId) {
                            $paymentRate->paymentRateClassrooms()->create([
                                'classroom_id' => $classroomId,
                            ]);
                        }

                        // Create Items
                        foreach ($months as $month) {
                            if ($billType->type == BillType::TYPE_MONTHLY) {
                                $itemYear = $request->{"tahun_$month"} ?? ($billType->academicYear->start_year ?? date('Y'));
                            } else {
                                $itemYear = $request->year ?? ($billType->academicYear->start_year ?? date('Y'));
                            }
                            $paymentRate->paymentRateItems()->create([
                                'month'  => $month,
                                'year'   => $itemYear,
                                'amount' => $cleanAmount,
                            ]);
                        }

                        $ratesToDispatch[] = $paymentRate->id;
                    }
                }

                // 3. DISPATCH JOBS
                foreach ($ratesToDispatch as $rId) {
                    \Illuminate\Support\Facades\Artisan::queue('bills:sync-rate', [
                        '--rate' => $rId,
                        '--force' => true,
                    ]);
                }

                DB::commit();
                $lock->release();

                return redirect()->route('bill-type.show', $billType->id)
                    ->with('success', 'Matriks Tarif berhasil dibuat. Auto-Migration tarif lama telah dijalankan. Sinkronisasi tagihan siswa sedang diproses di background.');
            }


            // Backend Protection: Validate classrooms match the selected school & block duplicates
            if ($request->type == PaymentRate::TYPE_REGULAR && !empty($request->classrooms)) {
                if ($request->school_id) {
                    $mismatchedClasses = Classroom::whereIn('id', $request->classrooms)
                        ->where('school_id', '!=', $request->school_id)
                        ->pluck('name')
                        ->toArray();

                    if (!empty($mismatchedClasses)) {
                        $mismatchedNames = implode(', ', $mismatchedClasses);
                        DB::rollBack();
                        $lock->release();
                        return redirect()->back()
                            ->with('error', "Gagal: Kelas ({$mismatchedNames}) tidak berada di bawah sekolah yang dipilih.")
                            ->withInput();
                    }
                }

                $reqJamaah = $request->jamaah_status;
                $reqGender = $request->gender;
                $reqSubStatus = $request->student_sub_status_id;
                $reqAlumni = $request->alumni_status;

                $existingRates = PaymentRate::where(function($q) use ($billType) {
                        $q->where('bill_type_id', $billType->id)
                          ->orWhereHas('billType', function($sub) use ($billType) {
                              $sub->where('name', $billType->name)
                                  ->where('academic_year_id', $billType->academic_year_id);
                          });
                    })
                    ->where('type', PaymentRate::TYPE_REGULAR)
                    ->with('paymentRateClassrooms')
                    ->get();

                $conflictingClassroomIds = [];
                foreach ($existingRates as $exRate) {
                    if ($this->isOverlappingFilter($exRate->jamaah_status, $exRate->gender, $exRate->student_sub_status_id, $exRate->alumni_status, $reqJamaah, $reqGender, $reqSubStatus, $reqAlumni)) {
                        foreach ($exRate->paymentRateClassrooms as $prc) {
                            $conflictingClassroomIds[] = $prc->classroom_id;
                        }
                    }
                }
                $conflictingClassroomIds = array_unique($conflictingClassroomIds);

                $duplicatedIds = array_intersect($request->classrooms, $conflictingClassroomIds);
                if (!empty($duplicatedIds)) {
                    $classNames = Classroom::whereIn('id', $duplicatedIds)->pluck('name')->implode(', ');
                    DB::rollBack();
                    $lock->release();
                    return redirect()->back()
                        ->with('error', "Gagal: Kelas ({$classNames}) sudah memiliki tarif dengan kriteria status/gender yang tumpang tindih.")
                        ->withInput();
                }
            }

            // 2. REGULAR vs TRANSFER Logic
            $ratesToDispatch = [];
            
            if ($request->type == PaymentRate::TYPE_REGULAR) {
                // REGULAR LOGIC
                // Buat Parent Payment Rate
                $paymentRate = $billType->paymentRates()->create([
                    'amount' => (int) preg_replace('/[^0-9]/', '', (string)$request->price),
                    'type' => $request->type,
                    'gender' => $request->gender ? implode(',', $request->gender) : null,
                    'jamaah_status' => $request->jamaah_status ? implode(',', $request->jamaah_status) : null,
                    'student_sub_status_id' => $request->student_sub_status_id,
                ]);

                // Attach Classrooms
                foreach ($request->classrooms as $classroomId) {
                    $paymentRate->paymentRateClassrooms()->create([
                        'classroom_id' => $classroomId,
                    ]);
                }

                // Create Payment Rate Items
                $months = ($billType->type == BillType::TYPE_MONTHLY) ? ($request->active_months ?? []) : ($request->months ?? [7]);
                
                foreach ($months as $month) {
                    $year = ($billType->type == BillType::TYPE_MONTHLY) ? $request->{"tahun_$month"} : ($request->year ?? ($billType->academicYear->start_year ?? date('Y')));

                    $paymentRate->paymentRateItems()->create([
                        'month'  => $month,
                        'year'   => $year,
                        'amount' => (int) preg_replace('/[^0-9]/', '', (string)$request->price),
                    ]);
                }
                
                $ratesToDispatch[] = $paymentRate->id;
                
            } else {
                // TRANSFER LOGIC (Repeater)
                $transferNames = $request->transfer_names ?? [];
                $transferPrices = $request->transfer_prices ?? [];
                $transferStudents = $request->transfer_students ?? [];
                
                $months = ($billType->type == BillType::TYPE_MONTHLY) ? range(1, 12) : ($request->months ?? [7]);
                
                foreach ($transferNames as $idx => $name) {
                    if (!isset($transferPrices[$idx]) || !isset($transferStudents[$idx])) continue;
                    
                    $cleanAmount = (int) preg_replace('/[^0-9]/', '', (string)$transferPrices[$idx]);
                    if ($cleanAmount <= 0) continue;
                    
                    // Create Parent Payment Rate for each repeater row
                    $paymentRate = $billType->paymentRates()->create([
                        'name' => $name,
                        'amount' => $cleanAmount,
                        'type' => PaymentRate::TYPE_TRANSFER,
                    ]);
                    
                    // Attach Students
                    foreach ($transferStudents[$idx] as $studentId) {
                        $paymentRate->paymentRateStudents()->create([
                            'student_id' => $studentId,
                        ]);
                    }
                    
                    // Create Payment Rate Items
                    foreach ($months as $month) {
                        $year = ($billType->type == BillType::TYPE_MONTHLY) ? ($request->{"tahun_$month"} ?? ($billType->academicYear->start_year ?? date('Y'))) : ($request->year ?? ($billType->academicYear->start_year ?? date('Y')));
                        
                        // Handle checkboxes correctly for Monthly type. The active_months[] array from frontend is global!
                        if ($billType->type == BillType::TYPE_MONTHLY) {
                            if ($request->active_months && !in_array($month, $request->active_months)) {
                                continue;
                            }
                        }

                        $paymentRate->paymentRateItems()->create([
                            'month'  => $month,
                            'year'   => $year,
                            'amount' => $cleanAmount,
                        ]);
                    }
                    
                    $ratesToDispatch[] = $paymentRate->id;
                }
            }

            DB::commit();
            $lock->release();

            // SINKRONISASI LANGSUNG (Synchronous Execution)
            foreach ($ratesToDispatch as $rId) {
                try {
                    \Illuminate\Support\Facades\Artisan::call('bills:sync-rate', [
                        '--rate' => $rId,
                        '--force' => true,
                    ]);
                } catch (\Throwable $e) {
                    Log::warning("bills:sync-rate synchronous fallback warning: " . $e->getMessage());
                }
            }

            return redirect()->route('bill-type.show', $billType->id)
                ->with('success', 'Tarif pembayaran berhasil dibuat dan tagihan siswa telah disinkronkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            $lock->release();
            Log::error("Error Generate Tagihan: " . $e->getMessage() . "\n" . $e->getTraceAsString());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        @set_time_limit(300);

        if (request()->ajax()) {
            return $this->handleAjaxRequest($id);
        }

        $paymentRate = PaymentRate::findOrFail($id);

        return view('admins.payment-rate.show', compact('paymentRate'));
    }

    private function handleAjaxRequest(string $id)
    {
        @set_time_limit(300);
        $dateRange = $this->getDateRange();

        if (request()->type === 'bill') {
            return $this->getBillData($id, $dateRange);
        }

        if (request()->type === 'total') {
            return $this->getTotalData($id, $dateRange);
        }
    }

    private function getDateRange()
    {
        return [
            'startMonth' => request()->start_date ? date('n', strtotime(request()->start_date)) : null,
            'endMonth' => request()->end_date ? date('n', strtotime(request()->end_date)) : null,
            'startYear' => request()->start_date ? date('Y', strtotime(request()->start_date)) : null,
            'endYear' => request()->end_date ? date('Y', strtotime(request()->end_date)) : null,
        ];
    }

    private function getBillData(string $id, array $dateRange)
    {
        $paymentRate = PaymentRate::with(['billType', 'paymentRateItems', 'paymentRateClassrooms', 'paymentRateStudents'])->findOrFail($id);

        // Get all relevant payment rate item IDs once
        $paymentRateItemIds = $paymentRate->paymentRateItems->pluck('id')->toArray();
        $relatedBillTypeIds = [$paymentRate->bill_type_id];
        if ($paymentRate->billType) {
            $relatedBillTypeIds = BillType::where('name', $paymentRate->billType->name)
                ->where('academic_year_id', $paymentRate->billType->academic_year_id)
                ->pluck('id')
                ->toArray();
        }

        // Base query on Students
        $query = Student::query()
            ->select('students.*')
            ->with(['classroom' => function ($q) {
                // Optimize loading classroom
                $q->select('id', 'name');
            }]);

        // Apply Payment Rate target filter (Classrooms or Students)
        if ($paymentRate->type === PaymentRate::TYPE_REGULAR) {
            $query->where('status', 'ACTIVE');
            $classroomIds = $paymentRate->paymentRateClassrooms->pluck('classroom_id')->toArray();
            
            $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
            if ($paymentRate->billType && $paymentRate->billType->academic_year_id && $activeYear && $paymentRate->billType->academic_year_id != $activeYear->id) {
                // Fetch historical students for this academic year
                $historicalStudentIds = \Illuminate\Support\Facades\DB::table('student_classroom_histories')
                    ->where('academic_year_id', $paymentRate->billType->academic_year_id)
                    ->whereIn('classroom_id', $classroomIds)
                    ->whereNull('deleted_at')
                    ->pluck('student_id')
                    ->toArray();
                $query->whereIn('id', $historicalStudentIds);
            } else {
                $query->whereIn('classroom_id', $classroomIds);
            }

            // Apply Payment Rate Gender Filter
            if ($paymentRate->gender) {
                $query->whereIn('gender', explode(',', $paymentRate->gender));
            }

            // Apply Payment Rate Parent Jamaah Status Filter
            if ($paymentRate->jamaah_status) {
                $statuses = array_map('trim', explode(',', $paymentRate->jamaah_status));
                $query->where(function ($q) use ($statuses) {
                    $q->whereHas('user', function ($userQ) use ($statuses) {
                        $userQ->whereIn('jamaah_status', $statuses);
                    });
                    if (in_array('NON_JAMAAH', $statuses)) {
                        $q->orWhereNull('user_id')
                          ->orWhereDoesntHave('user')
                          ->orWhereHas('user', function ($userQ) {
                              $userQ->whereNull('jamaah_status');
                          });
                    }
                });
            }

            // Apply Payment Rate Alumni Status Filter
            if ($paymentRate->alumni_status) {
                $alumniStatuses = array_map('trim', explode(',', $paymentRate->alumni_status));
                $query->where(function ($q) use ($alumniStatuses) {
                    $alumniSubquery = \Illuminate\Support\Facades\DB::table('student_classroom_histories')
                        ->join('classrooms', 'classrooms.id', '=', 'student_classroom_histories.classroom_id')
                        ->join('schools', 'schools.id', '=', 'classrooms.school_id')
                        ->where('schools.name', 'like', '%SMP%')
                        ->whereNull('student_classroom_histories.deleted_at')
                        ->select('student_id');

                    if (in_array('ALUMNI_SMP_MA', $alumniStatuses)) {
                        $q->orWhere(function ($qAlumni) use ($alumniSubquery) {
                            $qAlumni->whereHas('classroom.school', function ($sq) {
                                $sq->where('name', 'like', '%MA%');
                            })->whereIn('students.id', clone $alumniSubquery);
                        });
                    }

                    if (in_array('NON_ALUMNI', $alumniStatuses)) {
                        $q->orWhere(function ($qNonAlumni) use ($alumniSubquery) {
                            $qNonAlumni->whereDoesntHave('classroom.school', function ($sq) {
                                $sq->where('name', 'like', '%MA%');
                            })->orWhereNotIn('students.id', clone $alumniSubquery);
                        });
                    }
                });
            }

            // Apply School Filter
            if (request()->school_id && request()->school_id !== 'null') {
                $query->whereHas('classroom', function ($q) {
                    $q->where('school_id', request()->school_id);
                });
            }

            // Apply Classroom Filter
            if (request()->classroom_id && request()->classroom_id !== 'null') {
                $query->where('classroom_id', request()->classroom_id);
            }
        } else {
            $studentIds = $paymentRate->paymentRateStudents->pluck('student_id')->toArray();
            $query->whereIn('id', $studentIds);
        }

        // Pre-fetch student bill sums in 1 fast GROUP BY query to avoid DataTables withSum subquery slowdown
        $studentIds = (clone $query)->pluck('students.id')->toArray();

        $billAggregates = collect();
        if (!empty($studentIds) && !empty($paymentRateItemIds)) {
            $billAggregates = DB::table('bills')
                ->select('student_id', DB::raw('SUM(amount) as total'), DB::raw('SUM(paid_amount) as total_paid'))
                ->whereIn('student_id', $studentIds)
                ->whereIn('bill_type_id', $relatedBillTypeIds)
                ->whereNull('deleted_at')
                ->groupBy('student_id')
                ->get()
                ->keyBy('student_id');
        }

        return DataTables::of($query)
            ->addColumn('classroom', fn($student) => $student->classroom->name ?? '-')
            ->addColumn('total_unpaid', function ($student) use ($billAggregates) {
                $agg = $billAggregates->get($student->id);
                $total = $agg?->total ?? 0;
                $totalPaid = $agg?->total_paid ?? 0;
                return $this->formatCurrency(max(0, $total - $totalPaid));
            })
            ->addColumn('total_paid', function ($student) use ($billAggregates) {
                $agg = $billAggregates->get($student->id);
                return $this->formatCurrency($agg?->total_paid ?? 0);
            })
            ->addColumn('total', function ($student) use ($billAggregates) {
                $agg = $billAggregates->get($student->id);
                return $this->formatCurrency($agg?->total ?? 0);
            })
            ->addColumn('status', function ($student) use ($paymentRate, $billAggregates) {
                $agg = $billAggregates->get($student->id);
                $totalPaid = $agg?->total_paid ?? 0;
                $total = $agg?->total ?? 0;
                return $this->getPaymentStatus($totalPaid, $total, $student, $paymentRate);
            })
            ->addColumn('action', fn($student) => $this->renderActions($student, $paymentRate->bill_type_id))
            ->addColumn('id', fn($student) => $student->id)
            ->rawColumns(['status', 'action']) // 'action' and 'status' contain HTML
            ->make(true);
    }

    public function generate($id)
    {
        $paymentRate = PaymentRate::with(['billType', 'paymentRateItems'])->findOrFail($id);

        \Artisan::call('bills:sync-rate', [
            '--rate' => $id,
            '--force' => true,
        ]);

        return redirect()->back()->with('success', "Berhasil me-generate / mengsinkronkan tagihan untuk tarif " . ($paymentRate->billType->name ?? ''));
    }

    public function generateStudent(Request $request)
    {
        $request->validate([
            'payment_rate_id' => 'required|exists:payment_rates,id',
            'student_id' => 'required|exists:students,id',
        ]);

        $rateId = $request->payment_rate_id;
        $studentId = $request->student_id;

        $paymentRate = PaymentRate::with(['billType'])->findOrFail($rateId);
        $student = Student::findOrFail($studentId);

        try {
            DB::transaction(function () use ($rateId, $studentId) {
                \Artisan::call('bills:sync-rate', [
                    '--rate' => $rateId,
                    '--student-id' => $studentId,
                    '--force' => true,
                ]);
            });

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil me-generate tagihan secara sinkron & atomic untuk {$student->name}."
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal me-generate tagihan: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getPaymentStatus($paid, $total, $student = null, $paymentRate = null)
    {
        if ($total == 0 || $total === null) {
            if ($student && $paymentRate && $paymentRate->type === PaymentRate::TYPE_REGULAR) {
                $hasTransferRate = DB::table('payment_rate_students')
                    ->join('payment_rates', 'payment_rate_students.payment_rate_id', '=', 'payment_rates.id')
                    ->where('payment_rates.bill_type_id', $paymentRate->bill_type_id)
                    ->where('payment_rate_students.student_id', $student->id)
                    ->whereNull('payment_rate_students.deleted_at')
                    ->whereNull('payment_rates.deleted_at')
                    ->exists();

                if ($hasTransferRate) {
                    return '<span class="badge badge-light-warning text-dark fw-bolder px-2 py-1" title="Siswa ini terdaftar di Tarif Susulan / Pindahan (Lihat Tab Siswa Pindahan)"><i class="fas fa-user-tag text-warning me-1"></i>Tarif Susulan</span>';
                }
            }
            $generateBtn = '';
            if ($student && $paymentRate) {
                $generateBtn = ' <button type="button" class="btn btn-xs btn-light-primary generate-single-student-btn ms-1 py-1 px-2 hover-scale" data-student-id="' . $student->id . '" data-rate-id="' . $paymentRate->id . '" title="Generate Tagihan Siswa Ini">' .
                    '<i class="fas fa-sync-alt text-primary me-1"></i>Generate' .
                    '</button>';
            }
            return '<div class="d-inline-flex align-items-center"><span class="badge badge-light-secondary text-gray-700 fw-bolder px-2 py-1" title="Tagihan belum di-generate oleh Admin"><i class="fas fa-exclamation-circle text-muted me-1"></i>Belum Di-generate</span>' . $generateBtn . '</div>';
        } elseif ($paid == 0) {
            return '<span class="badge badge-light-danger fw-bolder px-2 py-1">Belum Bayar</span>';
        } elseif ($paid >= $total && $total > 0) {
            return '<span class="badge badge-light-success fw-bolder px-2 py-1">Lunas</span>';
        } else {
            return '<span class="badge badge-light-warning fw-bolder px-2 py-1">Belum Lunas</span>';
        }
    }

    private function getTotalData(string $id, array $dateRange)
    {
        $paymentRate = PaymentRate::with(['paymentRateItems', 'billType'])->findOrFail($id);
        $itemIds = $paymentRate->paymentRateItems->pluck('id')->toArray();

        $relatedBillTypeIds = [$paymentRate->bill_type_id];
        if ($paymentRate->billType) {
            $relatedBillTypeIds = BillType::where('name', $paymentRate->billType->name)
                ->where('academic_year_id', $paymentRate->billType->academic_year_id)
                ->pluck('id')
                ->toArray();
        }

        $billQuery = Bill::whereIn('bill_type_id', $relatedBillTypeIds)
            ->whereHas('student', fn($q) => $q->whereIn('status', [Student::STATUS_ACTIVE, Student::STATUS_GRADUATED]))
            ->when(request()->school_id && request()->school_id !== 'null', $this->schoolBillFilter())
            ->when(request()->classroom_id && request()->classroom_id !== 'null', $this->classroomBillFilter())
            ->when(request()->status === 'UNPAID', fn($query) => $query->where('status', Bill::STATUS_UNPAID))
            ->when(request()->status === 'PAID', fn($query) => $query->where('status', Bill::STATUS_PAID))
            ->when($dateRange['startYear'] && $dateRange['endYear'], $this->dateRangeFilter($dateRange));

        $total = $billQuery->sum('amount');
        $totalPaid = $billQuery->sum('paid_amount');
        $totalUnpaid = $billQuery->sum(\DB::raw('amount - paid_amount'));

        return response()->json([
            'total' => number_format($total, 0, ',', '.'),
            'total_paid' => number_format($totalPaid, 0, ',', '.'),
            'realisasion_percentage' => $total == 0 ? 0 : number_format(($totalPaid / $total) * 100, 2, ',', '.') . '%',
            'total_unpaid' => number_format($totalUnpaid, 0, ',', '.')
        ]);
    }

    private function schoolBillFilter()
    {
        return fn($query) => $query->whereHas('student.classroom', fn($q) => $q->where('school_id', request()->school_id));
    }

    private function classroomBillFilter()
    {
        return fn($query) => $query->whereHas('student', fn($q) => $q->where('classroom_id', request()->classroom_id));
    }

    private function dateRangeFilter(array $dateRange)
    {
        return function ($query) use ($dateRange) {
            $startMonth = $dateRange['startMonth'];
            $endMonth = $dateRange['endMonth'];
            $startYear = $dateRange['startYear'];
            $endYear = $dateRange['endYear'];

            $query->where(function ($q) use ($startMonth, $endMonth, $startYear, $endYear) {
                if ($startYear == $endYear) {
                    $q->where('year', $startYear)
                        ->whereBetween('month', [$startMonth, $endMonth]);
                } else {
                    $q->where(function ($q2) use ($startMonth, $startYear) {
                        $q2->where('year', $startYear)
                            ->where('month', '>=', $startMonth);
                    })->orWhere(function ($q2) use ($endMonth, $endYear) {
                        $q2->where('year', $endYear)
                            ->where('month', '<=', $endMonth);
                    })->orWhereBetween('year', [$startYear + 1, $endYear - 1]);
                }
            });
        };
    }

    private function formatCurrency($amount)
    {
        return 'Rp. ' . number_format($amount, 0, ',', '.');
    }

    private function filterByStatus($query, $status, $billTypeId)
    {
        if ($status === 'UNPAID') {
            return $query->whereHas('bills', fn($q) => $q->where('status', Bill::STATUS_UNPAID)->where('bill_type_id', $billTypeId));
        } elseif ($status === 'PAID') {
            return $query->whereDoesntHave('bills', fn($q) => $q->where('status', Bill::STATUS_UNPAID)->where('bill_type_id', $billTypeId));
        }
        return $query;
    }

    private function renderActions($data, $billTypeId)
    {
        $deleteForm = '<form action="' . route('delete-student-bill') . '" method="POST" style="display:inline;">
            ' . csrf_field() . '
            ' . method_field('DELETE') . '
            <input type="hidden" name="student_id" value="' . $data->id . '">
            <input type="hidden" name="bill_type_id" value="' . $billTypeId . '">
            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Apakah Anda yakin ingin menghapus tagihan siswa ini?\')">Hapus</button>
        </form>';

        return "<div class='d-flex justify-content-center'>" . $deleteForm . "</div>";
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentRate $paymentRate)
    {
        $billType = BillType::with('billItem')->findOrFail($paymentRate->bill_type_id);
        
        $posBayarName = $billType->billItem?->name;
        $schoolsQuery = School::hasSchool();
        
        if ($posBayarName && School::where('type', $posBayarName)->exists()) {
            $schoolsQuery->where('type', $posBayarName);
        }
        
        $schools = $schoolsQuery->orderBy('name')->get();

        $selectedSchoolId = null;
        if ($paymentRate->type == PaymentRate::TYPE_REGULAR && $paymentRate->paymentRateClassrooms->isNotEmpty()) {
            $selectedSchoolId = $paymentRate->paymentRateClassrooms->first()->classroom?->school_id;
        } elseif ($paymentRate->type == PaymentRate::TYPE_TRANSFER && $paymentRate->paymentRateStudents->isNotEmpty()) {
            $selectedSchoolId = $paymentRate->paymentRateStudents->first()->student?->classroom?->school_id;
        }

        $classroomsQuery = Classroom::orderByRaw(\App\Helpers\DbCompat::classroomOrder());
        if ($selectedSchoolId) {
            $classroomsQuery->where('school_id', $selectedSchoolId);
        }
        $classrooms = $classroomsQuery->get();
        
        $studentSubStatuses = \App\Models\StudentSubStatus::where('is_active', true)->get();

        return view('admins.payment-rate.create-edit', compact('paymentRate', 'schools', 'billType', 'classrooms', 'studentSubStatuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaymentRateRequest $request, $id)
    {
        // 1. ATOMIC LOCK berbasis ID payment rate untuk mencegah race condition
        $lock = Cache::lock('update_payment_rate_' . $id, 10);

        if (!$lock->get()) {
            return redirect()->back()->with('error', 'Proses update sedang berjalan, mohon tunggu...');
        }

        DB::beginTransaction();
        try {
            $billType = BillType::findOrFail($request->bill_type_id);
            $paymentRate = PaymentRate::with('paymentRateItems')->findOrFail($id);

            // Update Parent Type, Gender, and Jamaah Status (Support arrays)
            $paymentRate->update([
                'name' => $request->transfer_edit_name ?? $paymentRate->name,
                'type' => $request->type,
                'gender' => $request->gender ? implode(',', $request->gender) : null,
                'jamaah_status' => $request->jamaah_status ? implode(',', $request->jamaah_status) : null,
                'alumni_status' => $request->alumni_status ? implode(',', $request->alumni_status) : null,
                'student_sub_status_id' => $request->student_sub_status_id,
            ]);

            // ------------------------------------------------------------------
            // A. HANDLE RELATIONSHIPS (ADD/REMOVE TARGETS)
            // ------------------------------------------------------------------
            
            // 1. Existing Targets (Data Lama)
            $existingTargetIds = [];
            if ($paymentRate->type == PaymentRate::TYPE_REGULAR) {
                $existingTargetIds = $paymentRate->paymentRateClassrooms()->pluck('classroom_id')->toArray();
            } else {
                $existingTargetIds = $paymentRate->paymentRateStudents()->pluck('student_id')->toArray();
            }

            // 2. Submitted Targets (Data Baru dari Form)
            $submittedTargetIds = [];
            if ($paymentRate->type == PaymentRate::TYPE_REGULAR) {
                $submittedTargetIds = $request->classrooms ?? [];
            } else {
                $submittedTargetIds = $request->students ?? [];
            }

            // 3. Identify Additions & Removals
            $targetsToRemove = array_diff($existingTargetIds, $submittedTargetIds);
            $targetsToAdd    = array_diff($submittedTargetIds, $existingTargetIds);

            // 4. PROCESS REMOVALS (Detaching)
            if (!empty($targetsToRemove)) {
                
                // A. Check for PAID or partially paid bills on these targets
                $queryCheckPaid = Bill::where('bill_type_id', $billType->id)
                                    ->where('paid_amount', '>', 0);

                if ($paymentRate->type == PaymentRate::TYPE_REGULAR) {
                    $queryCheckPaid->whereIn('classroom_id', $targetsToRemove);
                } else {
                    $queryCheckPaid->whereIn('student_id', $targetsToRemove);
                }

                if ($queryCheckPaid->exists()) {
                    // Start: Create detailed error message
                     $failMessage = "Gagal menghapus target karena sudah ada tagihan yang LUNAS/DIBAYAR.";
                     // Optional: Get names for better error
                     if ($paymentRate->type == PaymentRate::TYPE_REGULAR) {
                         $names = Classroom::whereIn('id', $targetsToRemove)->limit(3)->pluck('name')->implode(', ');
                         $failMessage .= " (Kelas: $names...)";
                     }
                     throw new \Exception($failMessage . " Harap batalkan pembayaran terlebih dahulu.");
                }

                // B. Delete UNPAID Bills (Clean up orphan data)
                $queryDeleteBills = Bill::where('bill_type_id', $billType->id)
                                        ->where('status', Bill::STATUS_UNPAID);

                if ($paymentRate->type == PaymentRate::TYPE_REGULAR) {
                    $queryDeleteBills->whereIn('classroom_id', $targetsToRemove);
                } else {
                    $queryDeleteBills->whereIn('student_id', $targetsToRemove);
                }
                
                // Execute Delete (Gunakan forceDelete untuk tagihan UNPAID agar tidak memicu bentrok unique index di MySQL)
                $queryDeleteBills->forceDelete();

                // C. Detach Pivot Relations
                if ($paymentRate->type == PaymentRate::TYPE_REGULAR) {
                    $paymentRate->paymentRateClassrooms()->whereIn('classroom_id', $targetsToRemove)->delete();
                } else {
                    $paymentRate->paymentRateStudents()->whereIn('student_id', $targetsToRemove)->delete();
                }
            }

            // 5. PROCESS ADDITIONS (Attaching)
            if (!empty($targetsToAdd)) {
                if ($paymentRate->type == PaymentRate::TYPE_REGULAR) {
                    foreach ($targetsToAdd as $classId) {
                        $paymentRate->paymentRateClassrooms()->create(['classroom_id' => $classId]);
                    }
                } else {
                    foreach ($targetsToAdd as $studentId) {
                        $paymentRate->paymentRateStudents()->create(['student_id' => $studentId]);
                    }
                }
            }
            
            // ------------------------------------------------------------------
            // B. RE-FETCH STUDENTS FOR BILL SYNC
            // ------------------------------------------------------------------
            // Now that relations are updated, we fetch ALL currently relevant students
            $students = collect([]);
            if ($paymentRate->type == PaymentRate::TYPE_REGULAR) {
                $allClassroomIds = $paymentRate->paymentRateClassrooms()->pluck('classroom_id');
                $students = Student::whereIn('classroom_id', $allClassroomIds)
                                   ->where('status', 'ACTIVE') 
                                   ->when($paymentRate->gender, function($q) use ($paymentRate) {
                                        $q->whereIn('gender', explode(',', $paymentRate->gender));
                                    })
                                   ->when($paymentRate->jamaah_status, function($q) use ($paymentRate) {
                                        $statuses = array_map('trim', explode(',', $paymentRate->jamaah_status));
                                        $q->where(function ($qq) use ($statuses) {
                                            $qq->whereHas('user', function($userQ) use ($statuses) {
                                                $userQ->whereIn('jamaah_status', $statuses);
                                            });
                                            if (in_array('NON_JAMAAH', $statuses)) {
                                                $qq->orWhereNull('user_id')
                                                   ->orWhereDoesntHave('user')
                                                   ->orWhereHas('user', function ($userQ) {
                                                       $userQ->whereNull('jamaah_status');
                                                   });
                                            }
                                        });
                                    })
                                   ->get();
            } else {
                $allStudentIds = $paymentRate->paymentRateStudents()->pluck('student_id');
                $students = Student::whereIn('id', $allStudentIds)->get();
            }

            // ------------------------------------------------------------------
            // C. UPDATE NOMINALS & SYNC BILLS (Create/Update Logic from before)
            // ------------------------------------------------------------------

            // Prepare for loop
            $totalAmount = 0;
            $items = $paymentRate->paymentRateItems->keyBy('month');
            $timestamp = now();

            // LOGIC FOR MONTHLY TYPE
            if ($billType->type == BillType::TYPE_MONTHLY) {
                $activeMonths = $request->input("active_months", []);
                $globalPrice = (int) preg_replace('/[^0-9]/', '', (string)$request->price);
                
                for ($month = 1; $month <= 12; $month++) {
                    $year      = $request->input("tahun_$month") ?? ($billType->academicYear->start_year ?? date('Y'));
                    
                    // Sanitize amount (remove dots)
                    $cleanAmount = in_array($month, $activeMonths) ? $globalPrice : 0;
                    $totalAmount += $cleanAmount;

                    // Get or Create PaymentRateItem
                    $item = $items->get($month);
                    if (!$item) {
                        $item = $paymentRate->paymentRateItems()->create([
                            'month'  => $month,
                            'year'   => $year,
                            'amount' => $cleanAmount,
                        ]);
                    } else {
                        $item->update([
                            'year'   => $year,
                            'amount' => $cleanAmount,
                        ]);
                    }

                    // --- SYNC BILLS LOGIC ---
                    if ($cleanAmount > 0) {
                        // CASE B: UPDATE EXISTING UNPAID BILLS
                        Bill::where('payment_rate_item_id', $item->id)
                            ->where('status', Bill::STATUS_UNPAID)
                            ->update([
                                'amount' => $cleanAmount,
                                'year'   => $year
                            ]);

                        // CASE A: CREATE NEW BILLS FOR MISSING STUDENTS (New Targets OR New Months)
                        // 1. Get IDs of students who ALREADY have a bill for this month/year and bill type
                        $existingBillStudentIds = Bill::where('bill_type_id', $billType->id)
                            ->where('month', $month)
                            ->where('year', $year)
                            ->pluck('student_id')
                            ->toArray();
                        
                        // 2. Find students who need a bill created
                        $studentsToCreate = $students->whereNotIn('id', $existingBillStudentIds);
                        
                        $billsToInsert = [];
                        foreach ($studentsToCreate as $student) {
                            $billsToInsert[] = [
                                'id'                 => Str::uuid()->toString(),
                                'bill_type_id'       => $billType->id,
                                'classroom_id'       => $student->classroom_id,
                                'student_id'         => $student->id,
                                'academic_year_id'   => $billType->academic_year_id,
                                'month'              => $month,
                                'year'               => $year,
                                'amount'             => $cleanAmount,
                                'status'             => Bill::STATUS_UNPAID,
                                'payment_rate_item_id' => $item->id,
                                'created_at'         => $timestamp,
                                'updated_at'         => $timestamp,
                            ];
                        }

                        // Bulk Insert (Chunked for safety)
                        if (!empty($billsToInsert)) {
                            foreach (array_chunk($billsToInsert, 500) as $chunk) {
                                Bill::insert($chunk);
                            }
                        }

                    } else {
                        // CASE C: AMOUNT IS 0 -> DELETE UNPAID BILLS
                         Bill::where('payment_rate_item_id', $item->id)
                            ->where('status', Bill::STATUS_UNPAID)
                            ->forceDelete();
                    }
                }
                
                // Update Total Amount on Parent
                $paymentRate->update(['amount' => $totalAmount]);

            } else {
                // LOGIC FOR FREE / NON-MONTHLY TYPE
                $cleanPrice = (int) str_replace('.', '', $request->price ?? 0);
                
                // Usually Free Type has specific selected months in $request->months
                if (!empty($request->months)) {
                    foreach ($request->months as $monthNum) {
                        $item = $paymentRate->paymentRateItems()->where('month', $monthNum)->first();
                        
                        if (!$item) {
                             $item = $paymentRate->paymentRateItems()->create([
                                'month'  => $monthNum,
                                'amount' => $cleanPrice,
                            ]);
                        } else {
                            $item->update([
                                'amount' => $cleanPrice,
                            ]);
                        }
                        
                        // --- SYNC BILLS ---
                        if ($cleanPrice > 0) {
                            // Update Existing
                             Bill::where('payment_rate_item_id', $item->id)
                                ->where('status', Bill::STATUS_UNPAID)
                                ->update(['amount' => $cleanPrice]);
                            
                            // Create Missing
                            $existingBillStudentIds = Bill::where('bill_type_id', $billType->id)
                                ->where('payment_rate_item_id', $item->id)
                                ->pluck('student_id')
                                ->toArray();
                            $studentsToCreate = $students->whereNotIn('id', $existingBillStudentIds);
                            
                            $billsToInsert = [];
                            foreach ($studentsToCreate as $student) {
                                $billsToInsert[] = [
                                    'id'                 => Str::uuid()->toString(),
                                    'bill_type_id'       => $billType->id,
                                    'classroom_id'       => $student->classroom_id,
                                    'student_id'         => $student->id,
                                    'academic_year_id'   => $billType->academic_year_id,
                                    'month'              => $monthNum,
                                    'amount'             => $cleanPrice,
                                    'status'             => Bill::STATUS_UNPAID,
                                    'payment_rate_item_id' => $item->id,
                                    'created_at'         => $timestamp,
                                    'updated_at'         => $timestamp,
                                ];
                            }
                             if (!empty($billsToInsert)) {
                                foreach (array_chunk($billsToInsert, 500) as $chunk) {
                                    Bill::insert($chunk);
                                }
                            }
                        } else {
                             Bill::where('payment_rate_item_id', $item->id)->where('status', Bill::STATUS_UNPAID)->forceDelete();
                        }
                    }
                }

                $paymentRate->update(['amount' => $cleanPrice]);
            }

            DB::commit();
            $lock->release();

            return redirect()->route('bill-type.show', $billType->id)
                ->with('success', 'Tarif pembayaran berhasil diperbarui. Tagihan siswa telah disinkronkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            $lock->release();
            Log::error("Error Update PaymentRate: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // 1. ATOMIC LOCK berbasis ID payment rate
        $lock = Cache::lock('destroy_payment_rate_' . $id, 10);

        if (!$lock->get()) {
            return redirect()->back()->with('error', 'Proses penghapusan sedang berjalan, mohon tunggu...');
        }

        DB::beginTransaction();
        try {
            $paymentRate = PaymentRate::findOrFail($id);

            // 2. SAFETY CHECK: Cek apakah ada tagihan yang statusnya SUDAH DIBAYAR?
            // PERUBAHAN DI SINI: Gunakan 'paymentRateItems' (pakai 's') sesuai nama function di Model Bill Anda.
            $hasPaidBills = Bill::whereHas('paymentRateItems', function ($q) use ($id) {
                $q->where('payment_rate_id', $id);
            })->where('paid_amount', '>', 0)->exists();

            if ($hasPaidBills) {
                $lock->release();
                return redirect()->back()->with('error', 'GAGAL HAPUS! Terdapat siswa yang sudah membayar tagihan ini. Harap batalkan pembayaran siswa tersebut terlebih dahulu.');
            }

            // 3. HAPUS BILLS (Direct Query)
            // Bagian ini TIDAK PERLU DIUBAH karena langsung pakai nama tabel database ('payment_rate_items'), bukan nama relasi Eloquent.
            Bill::whereIn('payment_rate_item_id', function ($query) use ($id) {
                $query->select('id')
                    ->from('payment_rate_items') // Nama tabel di database (biasanya plural)
                    ->where('payment_rate_id', $id);
            })->forceDelete();

            // 4. Hapus Item & Classrooms & Students
            $paymentRate->paymentRateItems()->delete();
            $paymentRate->paymentRateClassrooms()->delete();
            $paymentRate->paymentRateStudents()->delete();

            // 5. Hapus Induk
            $paymentRate->delete();

            DB::commit();
            $lock->release();

            return redirect()->back()->with('success', 'Data tarif dan seluruh tagihan berhasil dihapus bersih.');
        } catch (\Exception $e) {
            DB::rollBack();
            $lock->release();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function isOverlappingFilter($status1, $gender1, $subStatus1, $alumni1, $status2, $gender2, $subStatus2, $alumni2): bool
    {
        $parseArray = function ($val) {
            if (empty($val)) return [];
            if (is_array($val)) return array_filter(array_map('trim', $val));
            return array_filter(array_map('trim', explode(',', (string)$val)));
        };

        $s1 = $parseArray($status1);
        $s2 = $parseArray($status2);
        if (empty($s1)) $s1 = ['JAMAAH', 'NON_JAMAAH', 'MUKIMIN'];
        if (empty($s2)) $s2 = ['JAMAAH', 'NON_JAMAAH', 'MUKIMIN'];

        $g1 = $parseArray($gender1);
        $g2 = $parseArray($gender2);
        if (empty($g1)) $g1 = ['L', 'P'];
        if (empty($g2)) $g2 = ['L', 'P'];
        
        $a1 = $parseArray($alumni1);
        $a2 = $parseArray($alumni2);
        if (empty($a1)) $a1 = ['ALUMNI_SMP_MA', 'NON_ALUMNI'];
        if (empty($a2)) $a2 = ['ALUMNI_SMP_MA', 'NON_ALUMNI'];

        $sub1 = empty($subStatus1) ? 'ALL' : $subStatus1;
        $sub2 = empty($subStatus2) ? 'ALL' : $subStatus2;

        $intersectStatus = !empty(array_intersect($s1, $s2));
        $intersectGender = !empty(array_intersect($g1, $g2));
        $intersectAlumni = !empty(array_intersect($a1, $a2));
        $intersectSub = ($sub1 === 'ALL' || $sub2 === 'ALL' || $sub1 === $sub2);

        return $intersectStatus && $intersectGender && $intersectAlumni && $intersectSub;
    }

    public function getClassroom(Request $request)
    {
        $school = School::findOrFail($request->school_id);
        $billTypeId = $request->bill_type_id;
        $reqJamaah = $request->jamaah_status;
        $reqGender = $request->gender;
        $reqSubStatus = $request->student_sub_status_id;
        $reqAlumni = $request->alumni_status;

        $isMatrix = $request->is_matrix;
        $conflictingClassroomIds = [];
        if ($billTypeId && !$isMatrix) {
            $billType = BillType::find($billTypeId);
            if ($billType) {
                $existingRates = PaymentRate::where(function($q) use ($billType) {
                        $q->where('bill_type_id', $billType->id)
                          ->orWhereHas('billType', function($sub) use ($billType) {
                              $sub->where('name', $billType->name)
                                  ->where('academic_year_id', $billType->academic_year_id);
                          });
                    })
                    ->where('type', PaymentRate::TYPE_REGULAR)
                    ->with('paymentRateClassrooms')
                    ->get();

                foreach ($existingRates as $exRate) {
                    if ($this->isOverlappingFilter($exRate->jamaah_status, $exRate->gender, $exRate->student_sub_status_id, $exRate->alumni_status, $reqJamaah, $reqGender, $reqSubStatus, $reqAlumni)) {
                        foreach ($exRate->paymentRateClassrooms as $prc) {
                            $conflictingClassroomIds[] = $prc->classroom_id;
                        }
                    }
                }
            }
        }
        $conflictingClassroomIds = array_unique($conflictingClassroomIds);

        $classrooms = Classroom::where('school_id', $school->id)
            ->orderByRaw(\App\Helpers\DbCompat::classroomOrder())
            ->get()
            ->map(function($classroom) use ($conflictingClassroomIds) {
                $classroom->is_already_created = in_array($classroom->id, $conflictingClassroomIds);
                return $classroom;
            });

        return response()->json($classrooms);
    }

    public function getStudent(Request $request)
    {
        $school = School::findOrFail($request->school_id);
        $billTypeId = $request->bill_type_id;
        $gender = $request->gender;
        $jamaahStatus = $request->jamaah_status;

        $students = Student::with('user:id,jamaah_status')
            ->whereHas('classroom', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->when($billTypeId, function($q) use ($billTypeId) {
                $q->whereDoesntHave('bills', function($subQ) use ($billTypeId) {
                    $subQ->where('bill_type_id', $billTypeId);
                });
            })
            ->when($gender, function($q) use ($gender) {
                $genderArray = is_array($gender) ? $gender : explode(',', $gender);
                $q->whereIn('gender', $genderArray);
            })
            ->when($jamaahStatus, function($q) use ($jamaahStatus) {
                $jamaahStatusArray = array_map('trim', is_array($jamaahStatus) ? $jamaahStatus : explode(',', $jamaahStatus));
                $q->whereHas('user', function($userQ) use ($jamaahStatusArray) {
                    $userQ->whereIn('jamaah_status', $jamaahStatusArray);
                });
            })
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->select('id', 'name', 'nis', 'user_id', 'gender')
            ->get();

        $formatted = $students->map(function($student) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'nis' => $student->nis,
                'gender' => $student->gender,
                'jamaah_status' => $student->user?->jamaah_status ?? 'NON_JAMAAH'
            ];
        });

        return response()->json($formatted);
    }

    /**
     * Get bill details for a student
     */
    public function getBillDetails(Request $request)
    {
        try {
            $paymentRate = PaymentRate::with('paymentRateItems')->find($request->payment_rate_id);
            $paymentRateItemIds = $paymentRate ? $paymentRate->paymentRateItems->pluck('id')->toArray() : [];

            $relatedBillTypeIds = [$request->bill_type_id];
            if ($paymentRate && $paymentRate->billType) {
                $relatedBillTypeIds = BillType::where('name', $paymentRate->billType->name)
                    ->where('academic_year_id', $paymentRate->billType->academic_year_id)
                    ->pluck('id')
                    ->toArray();
            }

            $bills = collect();
            if (!empty($relatedBillTypeIds)) {
                $bills = Bill::where('student_id', $request->student_id)
                    ->whereIn('bill_type_id', $relatedBillTypeIds)
                    ->orderByRaw("CASE 
                        WHEN month >= 7 THEN month - 6 
                        ELSE month + 6 
                    END")
                ->orderBy('year')
                ->get()
                ->map(function ($bill) {
                    return [
                        'id' => $bill->id,
                        'month' => $bill->month,
                        'year' => $bill->year,
                        'amount' => $bill->amount,
                        'paid_amount' => $bill->paid_amount ?? 0,
                        'status' => $bill->status,
                        'translated_month' => $bill->translated_month,
                        'status_badge' => $bill->status === Bill::STATUS_PAID
                            ? '<span class="badge bg-success">Lunas</span>'
                            : ($bill->status === 'PARTIAL'
                                ? '<span class="badge bg-warning">Cicilan</span>'
                                : '<span class="badge bg-danger">Belum Lunas</span>')
                    ];
                });
            }

            return response()->json([
                'success' => true,
                'bills' => $bills
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data tagihan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a single bill
     */
    public function deleteBill(Request $request)
    {
        try {
            $bill = Bill::findOrFail($request->bill_id);

            if ($bill->status === Bill::STATUS_PAID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus tagihan yang sudah dibayar'
                ], 400);
            }

            $bill->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Tagihan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Delete Bill Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus tagihan'
            ], 500);
        }
    }

    /**
     * Mass delete bills
     */
    public function deleteBillsMass(Request $request)
    {
        try {
            DB::beginTransaction();

            $billIds = $request->bill_ids;

            if (empty($billIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada tagihan yang dipilih'
                ], 400);
            }

            // Check if any bills are already paid (partially or fully)
            $paidBills = Bill::whereIn('id', $billIds)
                ->where('paid_amount', '>', 0)
                ->count();

            if ($paidBills > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Beberapa tagihan sudah dibayar dan tidak dapat dihapus'
                ], 400);
            }

            // Delete bills (forceDelete unpaid bills to prevent unique active record collisions)
            Bill::whereIn('id', $billIds)->where('status', Bill::STATUS_UNPAID)->forceDelete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menghapus ' . count($billIds) . ' tagihan'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mass Delete Bills Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus tagihan'
            ], 500);
        }
    }

    /**
     * Update a single bill amount
     */
    public function updateBill(Request $request)
    {
        try {
            $request->validate([
                'bill_id' => 'required|exists:bills,id',
                'amount' => 'required|numeric|min:0'
            ]);

            $bill = Bill::findOrFail($request->bill_id);

            if ($bill->status === Bill::STATUS_PAID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengubah tagihan yang sudah dibayar'
                ], 400);
            }

            $bill->update([
                'amount' => $request->amount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tagihan berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            Log::error('Update Bill Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui tagihan'
            ], 500);
        }
    }


}
