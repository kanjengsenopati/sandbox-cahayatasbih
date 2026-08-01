<?php

namespace App\Http\Controllers\Admin;

use App\Models\School;
use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Exports\StudentExport;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportStudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Laporan Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $schools = School::hasSchool()->orderBy('name', 'asc')->get();
        $academicYears = AcademicYear::orderBy('name', 'asc')->get();

        if (request()->ajax()) {
            $baseQuery = Student::select('students.*')
                ->selectRaw("(SELECT COUNT(*) FROM bills WHERE bills.student_id = students.id AND bills.status = 'UNPAID' AND bills.deleted_at IS NULL) as unpaid_bills_count")
                ->hasSchool()
                ->when(request()->school_id, function ($q) {
                    $q->where('students.school_id', request()->school_id);
                })
                ->when(request()->classroom_id, function ($q) {
                    $q->where('students.classroom_id', request()->classroom_id);
                })
                ->when(request()->academic_year_id, function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereHas('classroomHistories', function ($historyQuery) {
                            $historyQuery->where('academic_year_id', request()->academic_year_id);
                        })
                        ->orWhereHas('bills', function ($billQuery) {
                            $billQuery->where('academic_year_id', request()->academic_year_id);
                        });
                    });
                })
                ->when(request()->filled('student_name'), function ($q) {
                    $search = strtolower(trim(request()->student_name));
                    $q->where(function ($sub) use ($search) {
                        $sub->whereRaw('LOWER(students.name) LIKE ?', ['%' . $search . '%'])
                            ->orWhereRaw('LOWER(students.nis) LIKE ?', ['%' . $search . '%'])
                            ->orWhereRaw('LOWER(students.nisn) LIKE ?', ['%' . $search . '%']);
                    });
                });

            $tab = request()->input('tab', 'total');
            $dataQuery = clone $baseQuery;
            if ($tab === 'active') {
                $dataQuery->where('students.status', Student::STATUS_ACTIVE);
            } elseif ($tab === 'graduated') {
                $dataQuery->where('students.status', Student::STATUS_GRADUATED);
            } elseif ($tab === 'dropped_out') {
                $dataQuery->where('students.status', Student::STATUS_DROPPED_OUT);
            }

            $summary = [
                'total' => (clone $dataQuery)->count(),
                'total_male' => (clone $dataQuery)->where('students.gender', 'L')->count(),
                'total_female' => (clone $dataQuery)->where('students.gender', 'P')->count(),
            ];

            $data = $dataQuery->with(['classroom', 'school', 'bills.billType'])
                ->orderBy('students.name', 'asc');

            return DataTables::of($data)
                ->addColumn('unpaid_bills', function ($data) {
                    $unpaid = $data->bills->where('status', \App\Models\Bill::STATUS_UNPAID);
                    if ($unpaid->isEmpty()) {
                        $billsJson = json_encode([]);
                        return '<span class="badge bg-light-success text-success cursor-pointer btn-show-tunggakan" style="font-weight: 700;" data-name="' . e($data->name) . '" data-bills="' . e($billsJson) . '">Tunggakan (0)</span>';
                    }
                    
                    $billsArray = [];
                    foreach ($unpaid as $bill) {
                        $billName = $bill->billType->name ?? 'Tagihan';
                        $monthName = $bill->month ? \Carbon\Carbon::parse($bill->year . '-' . $bill->month . '-01')->translatedFormat('F') : '';
                        $billsArray[] = [
                            'bill_type' => $billName,
                            'month' => $monthName,
                            'year' => $bill->year,
                            'amount' => (int) $bill->amount
                        ];
                    }
                    
                    $billsJson = json_encode($billsArray);
                    return '<span class="badge bg-light-danger text-danger cursor-pointer btn-show-tunggakan" style="font-weight: 700;" data-name="' . e($data->name) . '" data-bills="' . e($billsJson) . '">Tunggakan (' . $unpaid->count() . ')</span>';
                })
                ->addColumn('action', function ($data) {
                    $actionShow = route('report-bill.show', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.show', [
                            'action' => $actionShow, 'label' => 'Cetak',
                            'icon' => 'fa fa-print'
                        ]) .
                        "</div>";
                })
                ->orderColumn('unpaid_bills', function ($query, $order) {
                    $query->reorder()->orderBy('unpaid_bills_count', $order);
                })
                ->rawColumns(['action', 'unpaid_bills'])
                ->with('summary', $summary)
                ->make(true);
        }

        // Global summaries for initial load
        $baseQuery = Student::hasSchool();
        $summary = [
            'total' => (clone $baseQuery)->count(),
            'total_male' => (clone $baseQuery)->where('gender', 'L')->count(),
            'total_female' => (clone $baseQuery)->where('gender', 'P')->count(),
        ];

        return view('admins.report-student.index', compact('schools', 'academicYears', 'summary'));
    }


    public function export(Request $request)
    {
        if (!Auth::user()->can('Manage Laporan Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return Excel::download(new StudentExport(), "Laporan Data User." . $request->type);
    }
}
