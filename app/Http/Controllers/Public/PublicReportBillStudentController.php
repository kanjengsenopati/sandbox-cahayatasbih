<?php
 
namespace App\Http\Controllers\Public;
 
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\ReportBillStudentController;
use Illuminate\Support\Facades\Crypt;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\BillType;
 
class PublicReportBillStudentController extends Controller
{
    public function index(Request $request, $token)
    {
        try {
            $filters = Crypt::decrypt($token);
            
            // Inject filters into request so buildRekapQuery can use them
            foreach ($filters as $key => $value) {
                $request->merge([$key => $value]);
            }
 
            $adminController = new ReportBillStudentController();
            $query = $adminController->buildRekapQuery();
            $data = $query->get();
 
            // Fetch the individual bill details for the nested table
            $studentIds = $data->pluck('id');
            
            if ($studentIds->isNotEmpty()) {
                $billBreakdown = \App\Models\Bill::with(['billType', 'academicYear'])
                    ->whereIn('student_id', $studentIds)
                    ->when($request->filled('start_date'), function ($q) use ($request) {
                        $startDate = \Carbon\Carbon::parse($request->start_date);
                        $startPeriod = (int)($startDate->year . str_pad($startDate->month, 2, '0', STR_PAD_LEFT));
                        $q->whereRaw('(year * 100 + month) >= ?', [$startPeriod]);
                    })
                    ->when($request->filled('end_date'), function ($q) use ($request) {
                        $endDate = \Carbon\Carbon::parse($request->end_date);
                        $endPeriod = (int)($endDate->year . str_pad($endDate->month, 2, '0', STR_PAD_LEFT));
                        $q->whereRaw('(year * 100 + month) <= ?', [$endPeriod]);
                    })
                    ->when($request->filled('academic_year_id'), fn($q) => $q->where('academic_year_id', $request->academic_year_id))
                    ->when($request->filled('bill_type_id'), fn($q) => $q->whereIn('bill_type_id', $request->bill_type_id))
                    ->get();
 
                $pivotedData = $billBreakdown->groupBy('student_id')->map(function ($studentBills) {
                    // Group by Bill Type and Academic Year
                    return $studentBills->groupBy(function($bill) {
                        return $bill->bill_type_id . '_' . $bill->academic_year_id . '_' . $bill->status;
                    })->map(function($groupedBills) {
                        $first = $groupedBills->first();
                        
                        // Sort bills by period (year * 100 + month)
                        $sorted = $groupedBills->sortBy(function($b) {
                            return $b->year * 100 + $b->month;
                        });

                        return [
                            'bill_type_name' => $first->billType?->name ?? '-',
                            'academic_year'  => $first->academicYear?->name ?? '-',
                            'months'         => $sorted->map(function($b) {
                                return [
                                    'month' => $b->month,
                                    'year'  => $b->year
                                ];
                            })->values()->toArray(),
                            'amount'         => $groupedBills->sum('amount'),
                            'status'         => $first->status,
                        ];
                    })->values();
                });
            } else {
                $pivotedData = collect();
            }
 
            // Calculate totals for public display
            $totals = [
                'total_amount' => $data->sum('total_bill'),
                'total_paid'   => $data->sum('total_paid'),
                'total_unpaid' => $data->sum('total_unpaid'),
            ];
 
            $academicYear = null;
            if ($request->filled('academic_year_id')) {
                $academicYear = AcademicYear::find($request->academic_year_id);
            }
 
            return view('public.report-bill-student.index', compact('data', 'totals', 'filters', 'academicYear', 'pivotedData'));
        } catch (\Exception $e) {
            return response()->view('errors.404', [], 404);
        }
    }
}
