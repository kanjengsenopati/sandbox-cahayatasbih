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
use App\Jobs\SendBillWhatsappNotificationJob;

class ReportBillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $type = request()->type;

            // Query dasar untuk kedua permintaan Ajax
            $query = Student::with('classroom', 'school', 'bills');

            // Filter berdasarkan request parameters
            if (request()->has('school_id')) {
                $query->where('school_id', request()->school_id);
            }

            if (request()->has('classroom_id')) {
                $query->where('classroom_id', request()->classroom_id);
            }

            if (request()->has('academic_year_id')) {
                $query->whereHas('bills', function ($query) {
                    $query->where('academic_year_id', request()->academic_year_id);
                });
            }

            if (request()->has('bill_type_id')) {
                $query->whereHas('bills', function ($query) {
                    $query->where('bill_type_id', request()->bill_type_id);
                });
            }

            // Handle permintaan tipe 'table'
            if ($type == 'table') {
                $data = $query->whereHas('bills')->orderBy('name', 'asc')->get();

                return DataTables::of($data)
                    ->addColumn('action', function ($data) {
                        $actionShow = route('report-bill.show', $data->id);
                        return "<div class='d-flex justify-content-center'>" .
                            view('components.action.show', [
                                'action' => $actionShow, 'label' => 'Cetak',
                                'icon' => 'fa fa-print'
                            ]) .
                            "</div>";
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }

            // Handle permintaan tipe 'total'
            if ($type == 'total') {
                $data = $query->orderBy('name', 'asc')->get();

                $total_paid = $data->where('status', Bill::STATUS_PAID)->count();
                $total = $data->count();

                return response()->json([
                    'total_paid' => $total_paid,
                    'total' => $total,
                ]);
            }
        }

        // Ambil data untuk dropdown
        $schools = School::orderBy('name', 'asc')->get();
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
        $data = BillType::whereHas('bills.student', function ($query) use ($id) {
            $query->where('student_id', $id);
        })->get();
        $student = Student::find($id);
        $data->student = $student;
        $pdf = Pdf::loadView(
            'admins.report-bill.print',
            compact('data')
        );
        return $pdf->stream("Laporan Tagihan" . $data->student->name ?? '' . ".pdf");
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
                ->orderBy('name', 'asc')
                ->get();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionShow = route('report-bill.show', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.show', [
                            'action' => $actionShow, 'label' => 'Cetak',
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
        $query = Student::with('classroom', 'school', 'bills')
            ->whereHas('bills', function ($query) {
                $query->where('status', Bill::STATUS_UNPAID);
            })
            ->when($request->school_id, function ($query) use ($request) {
                $query->where('school_id', $request->school_id);
            })
            ->when($request->classroom_id, function ($query) use ($request) {
                $query->where('classroom_id', $request->classroom_id);
            })
            ->when($request->academic_year_id, function ($query) use ($request) {
                $query->whereHas('bills', function ($query) use ($request) {
                    $query->where('academic_year_id', $request->academic_year_id);
                });
            })
            ->when($request->bill_type_id, function ($query) use ($request) {
                $query->whereHas('bills', function ($query) use ($request) {
                    $query->where('bill_type_id', $request->bill_type_id);
                });
            })
            ->orderBy('name', 'asc');

        $data = $query->get();
        $studentIds = $data->pluck('id');


        $thisMonthInInteger = intval(date('n'));
        $billTypes = BillType::whereHas('bills.student', function ($query) use ($studentIds, $thisMonthInInteger) {
            $query->whereIn('student_id', $studentIds)
                ->where('status', Bill::STATUS_UNPAID)
                ->where('month', '<=', $thisMonthInInteger);
        })->get();

        dispatch(new SendBillWhatsappNotificationJob($data, $billTypes));

        return $this->postSuccessResponse("Berhasil mengirimkan notifikasi tagihan", null);
    }
}
