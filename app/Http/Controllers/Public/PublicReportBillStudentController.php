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
 
            return view('public.report-bill-student.index', compact('data', 'totals', 'filters', 'academicYear'));
        } catch (\Exception $e) {
            return response()->view('errors.404', [], 404);
        }
    }
}
