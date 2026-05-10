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
 
            // Fetch the bill breakdown for dynamic columns
            $studentIds = $data->pluck('id');
            
            if ($studentIds->isNotEmpty()) {
                $billBreakdown = \App\Models\Bill::whereIn('student_id', $studentIds)
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
                    ->select('student_id', 'bill_type_id', \DB::raw('SUM(amount) as total'))
                    ->groupBy('student_id', 'bill_type_id')
                    ->get();
 
                $billTypeIds = $billBreakdown->pluck('bill_type_id')->unique();
                $billTypes = BillType::whereIn('id', $billTypeIds)->get()->keyBy('id');
 
                $pivotedData = $billBreakdown->groupBy('student_id')->map(function ($items) {
                    return $items->pluck('total', 'bill_type_id');
                });
            } else {
                $billTypes = collect();
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
 
            return view('public.report-bill-student.index', compact('data', 'totals', 'filters', 'academicYear', 'billTypes', 'pivotedData'));
        } catch (\Exception $e) {
            return response()->view('errors.404', [], 404);
        }
    }
}
