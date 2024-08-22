<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bill;
use App\Models\School;
use App\Models\Student;
use App\Models\BillType;
use App\Models\Classroom;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BillItemRequest;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendBillWhatsappNotificationJob;

class ReportBillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     if (!Auth::user()->can('Manage Laporan Tagihan')) {
    //         return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
    //     }
    //     if (request()->ajax()) {
    //         $startMonth = request()->start_date ? date('n', strtotime(request()->start_date)) : null;
    //         $endMonth = request()->end_date ? date('n', strtotime(request()->end_date)) : null;
    //         $startYear = request()->start_date ? date('Y', strtotime(request()->start_date)) : null;
    //         $endYear = request()->end_date ? date('Y', strtotime(request()->end_date)) : null;
    //         $data = BillType::with('billItem', 'academicYear', 'bills')
    //             ->when(request()->start_date && request()->end_date, function ($query) use ($startMonth, $endMonth, $startYear, $endYear) {
    //                 $query->whereHas('bills', function ($query) use ($startMonth, $endMonth, $startYear, $endYear) {
    //                     $query->where('year', '>=', $startYear)
    //                         ->where('year', '<=', $endYear)
    //                         ->where('month', '>=', $startMonth)
    //                         ->where('month', '<=', $endMonth);
    //                 });
    //             })
    //             ->when(request()->school_id && request()->school_id != 'null', function ($query) {
    //                 $query->whereHas('bills.student', function ($query) {
    //                     $query->whereHas('classroom', function ($query) {
    //                         $query->where('school_id', request()->school_id);
    //                     });
    //                 });
    //             })
    //             ->when(request()->academic_year_id && request()->academic_year_id != 'null', function ($query) {
    //                 $query->whereHas('bills', function ($query) {
    //                     $query->where('academic_year_id', request()->academic_year_id);
    //                 });
    //             })
    //             ->when(request()->classroom_id && request()->classroom_id != 'null', function ($query) {
    //                 $query->whereHas('bills.student', function ($query) {
    //                     $query->where('classroom_id', request()->classroom_id);
    //                 });
    //             })
    //             // ->whereHas('bills', fn ($query) => $query->where('student_id', $id))
    //             ->latest();
    //         if (request()->type == 'bill') {
    //             return DataTables::of($data)
    //                 ->addColumn('total_bill', fn($data) => $data->bills->count())
    //                 ->addColumn('student_count', fn($data) => $data->bills->pluck('student_id')->unique()->count())
    //                 ->editColumn('type', fn($data) => $data->type === BillType::TYPE_MONTHLY
    //                     ? '<span class="badge badge-primary">Bulanan</span>'
    //                     : '<span class="badge badge-secondary">Bebas</span>')
    //                 ->addColumn('action', fn($data) => "<div class='d-flex justify-content-center'>
    //                         <a href='" . route('report-bill.show', $data->id) . "' class='btn btn-primary btn-sm'>Lihat Detail</a>
    //                     </div>")
    //                 ->rawColumns(['action', 'type'])
    //                 ->make(true);
    //         } elseif (request()->type == 'total') {
    //             $data = $data->get();
    //             $total = $data->sum(fn($billType) => $billType->bills->sum('amount'));
    //             $totalPaid = $data->sum(fn($billType) => $billType->bills->where('status', Bill::STATUS_PAID)->sum('amount'));

    //             return response()->json([
    //                 'total' => number_format($total, 0, ',', '.'),
    //                 'total_paid' => number_format($totalPaid, 0, ',', '.'),
    //                 'realisasion_percentage' => $total == 0 ? '0%' : number_format(($totalPaid / $total) * 100, 2, ',', '.') . '%',
    //                 'total_unpaid' => number_format($total - $totalPaid, 0, ',', '.')
    //             ]);
    //         }
    //     }

    //     $schools = School::hasSchool()->orderBy('name', 'asc')->get();
    //     $academicYears = AcademicYear::orderBy('name', 'asc')->get();
    //     $billTypes = BillType::orderBy('name', 'asc')->get();

    //     return view('admins.report-bill.index', compact('schools', 'academicYears', 'billTypes'));
    // }

    public function index()
    {
        if (!Auth::user()->can('Manage Laporan Tagihan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            // Extract and simplify date-related logic
            $startMonth = request()->start_date ? date('n', strtotime(request()->start_date)) : null;
            $endMonth = request()->end_date ? date('n', strtotime(request()->end_date)) : null;
            $startYear = request()->start_date ? date('Y', strtotime(request()->start_date)) : null;
            $endYear = request()->end_date ? date('Y', strtotime(request()->end_date)) : null;
            $data = BillType::with('billItem', 'academicYear', 'bills')
                ->when(request()->start_date && request()->end_date, function ($query) use ($startMonth, $endMonth, $startYear, $endYear) {
                    $query->whereHas('bills', function ($query) use ($startMonth, $endMonth, $startYear, $endYear) {
                        $query->where('year', '>=', $startYear)
                            ->where('year', '<=', $endYear)
                            ->where('month', '>=', $startMonth)
                            ->where('month', '<=', $endMonth);
                    });
                })
                ->when(
                    request()->school_id && request()->school_id != 'null',
                    function ($query) {
                        $query->whereHas('bills.student', function ($query) {
                            $query->whereHas('classroom', function ($query) {
                                $query->where('school_id', request()->school_id);
                            });
                        });
                    }
                )
                ->when(request()->academic_year_id && request()->academic_year_id != 'null', function ($query) {
                    $query->whereHas('bills', function ($query) {
                        $query->where('academic_year_id', request()->academic_year_id);
                    });
                })
                ->when(request()->classroom_id && request()->classroom_id != 'null', function ($query) {
                    $query->whereHas('bills.student', function ($query) {
                        $query->where('classroom_id', request()->classroom_id);
                    });
                })
                ->latest();
            // ->get(); // Fetch data once here for both types

            // Differentiate handling based on request type
            if (request()->type == 'bill') {
                return DataTables::of($data)
                    ->addColumn('total_bill', fn($data) => $data->bills->count())
                    ->addColumn('student_count', fn($data) => $data->bills->pluck('student_id')->unique()->count())
                    ->editColumn('type', fn($data) => $data->type === BillType::TYPE_MONTHLY
                        ? '<span class="badge badge-primary">Bulanan</span>'
                        : '<span class="badge badge-secondary">Bebas</span>')
                    ->addColumn('action', fn($data) => "<div class='d-flex justify-content-center'>
                            <a href='" . route('report-bill.show', $data->id) . "' class='btn btn-primary btn-sm'>Lihat Detail</a>
                        </div>")
                    ->rawColumns(['action', 'type'])
                    ->make(true);
            } elseif (request()->type == 'total') {
                // Calculate totals
                $total = $data->sum(fn($billType) => $billType->bills->sum('amount'));
                $totalPaid = $data->sum(fn($billType) => $billType->bills->where('status', Bill::STATUS_PAID)->sum('amount'));

                return response()->json([
                    'total' => number_format($total, 0, ',', '.'),
                    'total_paid' => number_format($totalPaid, 0, ',', '.'),
                    'realisasion_percentage' => $total == 0 ? '0%' : number_format(($totalPaid / $total) * 100, 2, ',', '.') . '%',
                    'total_unpaid' => number_format($total - $totalPaid, 0, ',', '.')
                ]);
            }
        }

        // Retrieve dropdown data outside of the ajax block
        $schools = School::hasSchool()->orderBy('name', 'asc')->get();
        $academicYears = AcademicYear::orderBy('name', 'asc')->get();
        $billTypes = BillType::orderBy('name', 'asc')->get();

        return view('admins.report-bill.index', compact('schools', 'academicYears', 'billTypes'));
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */


    public function show(string $id)
    {
        if (!Auth::user()->can('Manage Laporan Tagihan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax() && request()->type === 'bill') {
            $students = Student::with(['classroom', 'school', 'bills' => fn($query) => $query->where('bill_type_id', $id)])
                ->whereHas('bills', fn($query) => $query->where('bill_type_id', $id))
                ->when(
                    request()->school_id && request()->school_id !== 'null',
                    fn($query) => $query->whereHas('classroom', fn($query) => $query->where('school_id', request()->school_id))
                )
                ->when(
                    request()->classroom_id && request()->classroom_id !== 'null',
                    fn($query) => $query->where('classroom_id', request()->classroom_id)
                )
                ->when(
                    request()->status,
                    fn($query) => $this->filterByStatus($query, request()->status, $id)
                )
                ->hasSchool()
                ->orderBy('name', 'asc')
                ->get();

            // Initialize overall totals
            $totalPaidSum = 0;
            $totalUnpaidSum = 0;

            // Loop over the students to calculate totals and payment status in memory
            $students->map(function ($student) use (&$totalPaidSum, &$totalUnpaidSum, $id) {
                // Initialize the student's unpaid and paid totals
                $studentTotalUnpaid = 0;
                $studentTotalPaid = 0;
                $total = 0;

                // Calculate total unpaid and paid for each student from the bills
                foreach ($student->bills as $bill) {
                    $total += $bill->amount;
                    if ($bill->status == Bill::STATUS_PAID) {
                        $studentTotalPaid += $bill->amount;
                    } elseif ($bill->status == Bill::STATUS_UNPAID) {
                        $studentTotalUnpaid += $bill->amount;
                    }
                }

                // Update overall totals
                $totalPaidSum += $studentTotalPaid;
                $totalUnpaidSum += $studentTotalUnpaid;

                // Assign the totals and status to the student object for use in DataTables
                $student->total_paid = $studentTotalPaid;
                $student->total_unpaid = $studentTotalUnpaid;
                $student->total = $total;

                // Determine the payment status
                if ($studentTotalPaid === 0) {
                    $student->status = '<span class="badge bg-danger">Belum Bayar</span>';
                } elseif ($studentTotalPaid === $total) {
                    $student->status = '<span class="badge bg-success">Lunas</span>';
                } else {
                    $student->status = '<span class="badge bg-warning">Belum Lunas</span>';
                }

                return $student;
            });

            // Pass data to DataTables
            return DataTables::of($students)
                ->addColumn('total_unpaid', fn($student) => $this->formatCurrency($student->total_unpaid))
                ->addColumn('total_paid', fn($student) => $this->formatCurrency($student->total_paid))
                ->addColumn('total', fn($student) => $this->formatCurrency($student->total))
                ->addColumn('status', fn($student) => $student->status) // Status already calculated in map
                ->addColumn('action', fn($data) => $this->renderActions($data, $id))
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        if (request()->ajax() && request()->type === 'total') {

            $total = Bill::where('bill_type_id', $id)
                ->when(request()->school_id && request()->school_id != 'null', fn($query) => $query->whereHas('student', fn($query) => $query->whereHas('classroom', fn($query) => $query->where('school_id', request()->school_id))))
                ->when(request()->classroom_id && request()->classroom_id != 'null', fn($query) => $query->whereHas('student', fn($query) => $query->where('classroom_id', request()->classroom_id)))
                ->when(request()->status === 'UNPAID', fn($query) => $query->where('status', Bill::STATUS_UNPAID))
                ->when(request()->status === 'PAID', fn($query) => $query->where('status', Bill::STATUS_PAID))
                ->sum('amount');

            $totalPaid = Bill::where('bill_type_id', $id)
                ->where('status', Bill::STATUS_PAID)
                ->when(request()->school_id && request()->school_id != 'null', fn($query) => $query->whereHas('student', fn($query) => $query->whereHas('classroom', fn($query) => $query->where('school_id', request()->school_id))))
                ->when(request()->classroom_id && request()->classroom_id != 'null', fn($query) => $query->whereHas('student', fn($query) => $query->where('classroom_id', request()->classroom_id)))
                ->sum('amount');

            return response()->json([
                'total' => number_format($total, 0, ',', '.'),
                'total_paid' => number_format($totalPaid, 0, ',', '.'),
                'realisasion_percentage' => $total == 0 ? 0 : number_format(($totalPaid / $total) * 100, 2, ',', '.') . '%',
                'total_unpaid' => number_format($total - $totalPaid, 0, ',', '.')
            ]);
        }

        $billType = BillType::findOrFail($id);
        $schools = School::hasSchool()->orderBy('name', 'asc')->get();
        return view('admins.report-bill.show', compact('billType', 'schools'));
    }

    private function getBillTotal($studentId, $billTypeId, $status = null)
    {
        $query = Bill::where('student_id', $studentId)
            ->where('bill_type_id', $billTypeId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->sum('amount');
    }

    private function formatCurrency($amount)
    {
        return 'Rp. ' . number_format($amount, 0, ',', '.');
    }

    // private function getPaymentStatus($studentId, $billTypeId)
    // {
    //     $totalPaid = Bill::where('student_id', $studentId)
    //         ->where('status', Bill::STATUS_PAID)
    //         ->where('bill_type_id', $billTypeId)
    //         ->sum('amount');
    //     $total = Bill::where('student_id', $studentId)->where('bill_type_id', $billTypeId)->sum('amount');

    //     if ($totalPaid === 0) {
    //         return '<span class="badge bg-danger">Belum Bayar</span>';
    //     } elseif ($totalPaid === $total) {
    //         return '<span class="badge bg-success">Lunas</span>';
    //     } elseif ($totalPaid < $total) {
    //         return '<span class="badge bg-warning">Belum Lunas</span>';
    //     }

    //     return '<span class="badge bg-success">Lunas</span>';
    // }

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
        $actionDetail = route('bill.summary-bill', ['student_id' => $data->id, 'bill_type_id' => $billTypeId]);
        $notificationRoute = route('report-bill.send-bill-notification');

        return "
        <div class='d-flex gap-2 flex-nowrap justify-content-center'>
            <a href='{$actionDetail}' class='btn btn-primary btn-sm'>Detail</a>
            <form action='{$notificationRoute}' method='POST'>
                " . csrf_field() . "
                <input type='hidden' name='bill_type_id' value='{$billTypeId}'>
                <input type='hidden' name='student_id[]' value='{$data->id}'>
                <button type='submit' class='btn btn-success btn-sm'>Kirim Notifikasi</button>
            </form>
        </div>";
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

    public function getClassroom(Request $request)
    {
        $schoolId = $request->school_id;
        $classrooms = Classroom::where('school_id', $schoolId)->orderBy('name', 'asc')->get();
        return $this->getSuccessResponse($classrooms);
    }

    public function getData()
    {
        if (request()->ajax() && request()->has('school_id')) {
            $data = Student::with('classroom', 'school', 'bills')
                ->whereHas('bills')
                ->when(request()->school_id, function ($query) {
                    $query->where('school_id', request()->school_id);
                })
                ->when(request()->classroom_id, function ($query) {
                    $query->where('classroom_id', request()->classroom_id);
                })
                ->when(request()->academic_year_id, function ($query) {
                    $query->whereHas('bills', function ($query) {
                        $query->where('academic_year_id', request()->academic_year_id);
                    });
                })
                ->when(request()->bill_type_id, function ($query) {
                    $query->whereHas('bills', function ($query) {
                        $query->where('bill_type_id', request()->bill_type_id);
                    });
                })
                ->hasSchool()
                ->orderBy('name', 'asc')
                ->get();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionShow = route('report-bill.show', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.show', [
                            'action' => $actionShow,
                            'label' => 'Cetak',
                            'icon' => 'fa fa-print'
                        ]) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function sendBillNotification(Request $request)
    {
        $billTypeId = $request->bill_type_id;
        $studentIds = $request->student_id;

        // Pastikan $studentIds adalah array
        if (!is_array($studentIds)) {
            $studentIds = [$studentIds];
        }

        $thisMonthInInteger = intval(date('n'));

        $billTypes = BillType::with('bills.student')->where('id', $billTypeId)->get();

        // Pastikan $studentIds adalah array sebelum mengirimkannya ke job
        dispatch(new SendBillWhatsappNotificationJob($studentIds, $billTypes));

        return redirect()->back()->with('success', 'Notifikasi tagihan berhasil dikirim');
    }

    public function sendWa($id)
    {
        $billTypes = BillType::with('bills.student')->where('id', $id)->get();
        // get student_id where status bill is unpaid from month 1 and month now
        $students = Student::whereHas('bills', function ($query) use ($id) {
            $query->where('bill_type_id', $id)
                ->where('status', Bill::STATUS_UNPAID)
                ->where('month', '>=', 1)
                ->where('month', '<=', date('n'))
                ->where('year', '<=', date('Y'));
        })->pluck('id')->toArray();

        dispatch(new SendBillWhatsappNotificationJob($students, $billTypes));

        return $this->postSuccessResponse('Notifikasi tagihan berhasil dikirim');
    }
}
