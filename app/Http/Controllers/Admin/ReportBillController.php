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

class ReportBillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
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
}
