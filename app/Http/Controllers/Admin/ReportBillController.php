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

    public function index()
    {
        if (!Auth::user()->can('Manage Laporan Tagihan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            // Extract the start and end dates
            $startDate = request()->start_date ? date('Y-m', strtotime(request()->start_date)) : null;
            $endDate = request()->end_date ? date('Y-m', strtotime(request()->end_date)) : null;

            // Build the query using joins
            $data = BillType::select('bill_types.*')
                ->join('bills', 'bills.bill_type_id', '=', 'bill_types.id')
                ->join('students', 'students.id', '=', 'bills.student_id')
                ->join('classrooms', 'classrooms.id', '=', 'students.classroom_id');

            // Apply the date filters by comparing the concatenated year and month in MySQL
            if ($startDate && $endDate) {
                $data->whereRaw("STR_TO_DATE(CONCAT(bills.year, '-', LPAD(bills.month, 2, '0'), '-01'), '%Y-%m-%d') BETWEEN ? AND ?", [$startDate . '-01', $endDate . '-31']);
            }

            // Add filtering by school, academic year, and classroom if available
            if (request()->school_id && request()->school_id != 'null') {
                $data->where('classrooms.school_id', request()->school_id);
            }

            if (request()->academic_year_id && request()->academic_year_id != 'null') {
                $data->where('bills.academic_year_id', request()->academic_year_id);
            }

            if (request()->classroom_id && request()->classroom_id != 'null') {
                $data->where('students.classroom_id', request()->classroom_id);
            }

            // Group by and finalize the query
            $data->groupBy('bill_types.id')->latest();

            if (request()->type == 'bill') {
                return DataTables::of($data)
                    ->addColumn('academic_year', fn($data) => $data->academicYear->name)
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
                // Calculate totals without loading entire collections
                $total = Bill::whereIn('bill_type_id', $data->pluck('id')->toArray())->sum('amount');
                $totalPaid = Bill::whereIn('bill_type_id', $data->pluck('id')->toArray())
                    ->where('status', Bill::STATUS_PAID)
                    ->sum('amount');

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
        // $billTypes = BillType::orderBy('name', 'asc')->get();

        return view('admins.report-bill.index', compact('schools', 'academicYears'));
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
        $this->authorize('Manage Laporan Tagihan');

        if (request()->ajax()) {
            return $this->handleAjaxRequest($id);
        }

        $billType = BillType::findOrFail($id);
        $schools = School::hasSchool()->orderBy('name', 'asc')->get();

        return view('admins.report-bill.show', compact('billType', 'schools'));
    }

    private function handleAjaxRequest(string $id)
    {
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
        $students = $this->getFilteredStudents($id, $dateRange);
        $this->calculateStudentTotals($students, $id);

        return DataTables::of($students)
            ->addColumn('total_unpaid', fn($student) => $this->formatCurrency($student->total_unpaid))
            ->addColumn('total_paid', fn($student) => $this->formatCurrency($student->total_paid))
            ->addColumn('total', fn($student) => $this->formatCurrency($student->total))
            ->addColumn('status', fn($student) => $student->status)
            ->addColumn('action', fn($data) => $this->renderActions($data, $id))
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    private function getFilteredStudents(string $id, array $dateRange)
    {
        return Student::select('students.*')
            ->join('bills', 'bills.student_id', '=', 'students.id')
            ->where('bills.bill_type_id', $id)
            // ->whereBetween('bills.month', [$dateRange['startMonth'], $dateRange['endMonth']])
            // ->whereBetween('bills.year', [$dateRange['startYear'], $dateRange['endYear']])
            ->when(request()->school_id && request()->school_id !== 'null', $this->schoolFilter())
            ->when(request()->classroom_id && request()->classroom_id !== 'null', $this->classroomFilter())
            ->when(request()->status, fn($query) => $this->filterByStatus($query, request()->status, $id))
            // ->when($dateRange['startYear'] && $dateRange['endYear'], $this->dateRangeFilter($dateRange))
            ->hasSchool()
            ->orderBy('students.name', 'asc')
            ->distinct()
            ->get();
    }

    private function schoolFilter()
    {
        return function ($query) {
            return $query->join('classrooms', 'classrooms.id', '=', 'students.classroom_id')
                ->where('classrooms.school_id', request()->school_id);
        };
    }

    private function classroomFilter()
    {
        return fn($query) => $query->where('students.classroom_id', request()->classroom_id);
    }

    private function dateRangeFilter(array $dateRange)
    {
        return function ($query) use ($dateRange) {
            $query->whereBetween('bills.year', [$dateRange['startYear'], $dateRange['endYear']])
                ->whereBetween('bills.month', [$dateRange['startMonth'], $dateRange['endMonth']]);
        };
    }

    private function calculateStudentTotals(&$students, string $id)
    {
        $totalPaidSum = 0;
        $totalUnpaidSum = 0;

        $students->map(function ($student) use (&$totalPaidSum, &$totalUnpaidSum, $id) {
            $total = $student->bills->where('bill_type_id', $id)->sum('amount');
            $studentTotalPaid = $student->bills->where('bill_type_id', $id)->where('status', Bill::STATUS_PAID)->sum('amount');
            $studentTotalUnpaid = $total - $studentTotalPaid;

            $totalPaidSum += $studentTotalPaid;
            $totalUnpaidSum += $studentTotalUnpaid;

            $student->total_paid = $studentTotalPaid;
            $student->total_unpaid = $studentTotalUnpaid;
            $student->total = $total;
            $student->status = $this->getPaymentStatus($studentTotalPaid, $total);

            return $student;
        });
    }

    private function getPaymentStatus($paid, $total)
    {
        if ($paid === 0) {
            return '<span class="badge bg-danger">Belum Bayar</span>';
        } elseif ($paid === $total) {
            return '<span class="badge bg-success">Lunas</span>';
        } else {
            return '<span class="badge bg-warning">Belum Lunas</span>';
        }
    }

    private function getTotalData(string $id, array $dateRange)
    {
        $billQuery = Bill::where('bill_type_id', $id)
            ->when(request()->school_id && request()->school_id !== 'null', $this->schoolBillFilter())
            ->when(request()->classroom_id && request()->classroom_id !== 'null', $this->classroomBillFilter())
            ->when(request()->status === 'UNPAID', fn($query) => $query->where('status', Bill::STATUS_UNPAID))
            ->when(request()->status === 'PAID', fn($query) => $query->where('status', Bill::STATUS_PAID))
            ->when($dateRange['startYear'] && $dateRange['endYear'], $this->dateRangeFilter($dateRange));

        $total = $billQuery->sum('amount');
        $totalPaid = $billQuery->where('status', Bill::STATUS_PAID)->sum('amount');
        $totalUnpaid = $total - $totalPaid;

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
