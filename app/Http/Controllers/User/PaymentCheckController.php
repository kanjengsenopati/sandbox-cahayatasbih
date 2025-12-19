<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use App\Models\School;
use App\Models\Student;
use App\Models\BillType;
use App\Models\Classroom;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentCheckController extends Controller
{
    /**
     * Display the payment check dashboard.
     */
    public function index(Request $request)
    {
        // 1. Get Master Data for Filters
        $schools = School::orderBy('name')->get(); 

        // 2. Active Academic Year (for SPP Grid)
        $activeYear = AcademicYear::where('is_active', true)->first();
        $startYear = $activeYear ? (int)explode('/', $activeYear->name)[0] : (int)date('Y');
        
        // 3. Query Students with Filters
        $students = collect([]);

        // Default empty or filtered query
        if ($request->has('search') || $request->has('unit') || $request->has('class_id')) {
            $query = Student::query()
                ->select('students.*')
                ->with(['user', 'classroom.school', 'bills' => function($q) use ($activeYear) {
                    $q->where('academic_year_id', $activeYear?->id)
                      ->whereHas('billType', function($bt) {
                          $bt->where('name', 'LIKE', '%SYAHRIAH%');
                      })
                      ->orderBy('year', 'desc')
                      ->orderBy('month', 'asc');
                }]);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nis', 'like', "%{$search}%");
                });
            }

            if ($request->filled('unit')) {
                $query->whereHas('classroom', function($q) use ($request) {
                    $q->where('school_id', $request->unit);
                });
            }

            if ($request->filled('class_id')) {
                $query->where('classroom_id', $request->class_id);
            }

            // Strictly using paginate(15) for performance
            $students = $query->paginate(15);
        } else {
             // Return empty paginator if no filter
             $students = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        }

        // 4. Transform Data for Frontend
        // We handle transformation on the collection inside the paginator to preserve pagination links
        $transformedCollection = $students->getCollection()->map(function ($student) use ($startYear) {
            $months = [];
            $totalBill = 0;
            $totalPaid = 0;
            
            $monthSequence = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
            
            foreach ($monthSequence as $m) {
                $year = ($m >= 7) ? $startYear : $startYear + 1;
                $bill = $student->bills->where('month', $m)->where('year', $year)->first();
                
                $status = 'unpaid';
                $amount = 0;
                $paidDate = '-';
                
                if ($bill) {
                    $amount = $bill->amount;
                    if ($bill->status === 'PAID') {
                        $status = 'paid';
                        $totalPaid += $bill->amount;
                        $paidDate = $bill->paid_date ? Carbon::parse($bill->paid_date)->format('d/m/Y') : '-';
                    } 
                    $totalBill += $bill->amount;
                }

                $dateObj = Carbon::createFromDate($year, $m, 1)->endOfMonth();
                $isPast = $dateObj->isPast();
                
                $monthNames = [
                    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
                    7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
                ];

                $months[] = [
                    'id' => $m,
                    'month_name' => $monthNames[$m],
                    'year' => $year,
                    'name' => $monthNames[$m] . ' ' . substr($year, -2),
                    'status' => $status,
                    'isPast' => $isPast,
                    'amount' => $amount,
                    'amount_formatted' => 'Rp ' . number_format($amount, 0, ',', '.'),
                    'paid_date' => $paidDate
                ];
            }

            $waLink = null;
            if ($student->user && $student->user->phone) {
                $phone = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $student->user->phone));
                if ($phone) {
                    $waLink = "https://wa.me/{$phone}?text=Assalamu'alaikum, berikut tagihan SPP untuk santri {$student->name} (NIS: {$student->nis})";
                }
            }

            return [
                'id' => $student->id,
                'name' => $student->name,
                'nis' => $student->nis ?? '-',
                'class' => $student->classroom->name ?? '-',
                'unit' => $student->classroom->school->name ?? '-',
                'status' => ($totalBill > 0 && $totalBill === $totalPaid) ? 'Lunas' : 'Belum Lunas',
                'months' => $months,
                'wa_link' => $waLink,
                'summary' => [
                    'total' => $totalBill,
                    'paid' => $totalPaid,
                    'total_formatted' => 'Rp ' . number_format($totalBill, 0, ',', '.'),
                    'paid_formatted' => 'Rp ' . number_format($totalPaid, 0, ',', '.'),
                    'remaining_formatted' => 'Rp ' . number_format($totalBill - $totalPaid, 0, ',', '.')
                ]
            ];
        });

        $students->setCollection($transformedCollection);

        // 5. Build Initial Classes List (for SSR dropdown)
        $classes = collect([]);
        if ($request->filled('unit')) {
            $classes = Classroom::where('school_id', $request->unit)->orderBy('name')->get();
        }

        // STRICT AJAX CHECK
        if ($request->ajax()) {
            // Return ONLY the partial for the student list container
            return view('public-payment._student_list', ['students' => $students])->render();
        }

        // Full Page Load
        return view('public-payment.index', [
            'schools' => $schools,
            'classes' => $classes,
            'students' => $students
        ]);
    }

    public function getClasses(Request $request)
    {
        $query = Classroom::query();
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }
        $classes = $query->orderBy('name')->get(['id', 'name']);
        return response()->json($classes);
    }
}
