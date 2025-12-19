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
        $schools = School::whereNotIn('id', ["37ca75d4-4a87-4856-be8e-f78e2672134f", "ca3d1ef1-a2ec-4a2b-81ce-72a2299e068c"])->orderBy('name')->get();

        // New Logic: Fetch Academic Years correctly sorted (Active First, then Newest)
        $academicYears = AcademicYear::orderByRaw('is_active DESC, created_at DESC')->get();

        // 2. Active Academic Year (for SPP Grid)
        // If filter is present, use it. Otherwise default to current active year.
        if ($request->filled('academic_year_id')) {
            $selectedYear = $academicYears->firstWhere('id', $request->academic_year_id);
        } else {
            // Default to Active
            $selectedYear = $academicYears->firstWhere('is_active', true) ?? $academicYears->first();
        }

        // Determine Start Year for grid generation
        $startYear = $selectedYear ? (int)explode('/', $selectedYear->name)[0] : (int)date('Y');
        
        // 3. Query Students with Filters
        $students = collect([]);

        // Default empty or filtered query
        if ($request->has('search') || $request->has('unit') || $request->has('class_id') || $request->has('academic_year_id')) {
            $query = Student::query()
                ->select('students.*')
                ->with(['user', 'classroom.school', 'bills' => function($q) use ($selectedYear) {
                    
                    // Filter by selected Academic Year ID
                    if ($selectedYear) {
                         $q->where('academic_year_id', $selectedYear->id);
                    }
                      
                     $q->whereHas('billType', function($bt) {
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
        $realNow = Carbon::now();
        $transformedCollection = $students->getCollection()->map(function ($student) use ($startYear, $realNow) {
            $months = [];
            $totalBill = 0;
            $totalPaid = 0;
            $currentDue = 0;
            
            $monthSequence = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
            
            foreach ($monthSequence as $m) {
                $year = ($m >= 7) ? $startYear : $startYear + 1;
                $bill = $student->bills->where('month', $m)->where('year', $year)->first();
                
                $status = 'unpaid';
                $amount = 0;
                $paidDate = '-';

                // Check if this month is Due (Past or Current Month)
                // Logic: Year < CurrentYear OR (Year == CurrentYear AND Month <= CurrentMonth)
                $isDue = ($year < $realNow->year) || ($year == $realNow->year && $m <= $realNow->month);
                
                if ($bill) {
                    $amount = $bill->amount;
                    if ($bill->status === 'PAID') {
                        $status = 'paid';
                        $totalPaid += $bill->amount;
                        $paidDate = $bill->paid_date ? Carbon::parse($bill->paid_date)->format('d/m/Y') : '-';
                    } else {
                        // Unpaid and Due/Overdue
                        if ($isDue) {
                             $currentDue += $bill->amount;
                        }
                    }
                    $totalBill += $bill->amount;
                }

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
                    'isPast' => $isDue, // Passing 'isDue' logic as 'isPast' to frontend
                    'amount' => $amount,
                    'amount_formatted' => 'Rp ' . number_format($amount, 0, ',', '.'),
                    'paid_date' => $paidDate
                ];
            }

            $waLink = null;
            if ($student->user && $student->user->phone) {
                $phone = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $student->user->phone));
                
                if ($phone) {
                    // Generate Detail Message
                    $unpaidBills = $student->bills->where('status', '!=', 'PAID')->sortBy(function($bill) {
                        return $bill->year * 100 + $bill->month; // Simple sort YYYYMM
                    });

                    if ($unpaidBills->isNotEmpty()) {
                         $msg = "Assalamu'alaikum.\n";
                         $msg .= "Tagihan Syahriah Ananda: *" . strtoupper($student->name) . "* (NIS: {$student->nis})\n\n";
                         $msg .= "Rincian Belum Lunas:\n";

                         $indonesianMonths = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                         ];

                         foreach ($unpaidBills as $bill) {
                             $monthName = $indonesianMonths[$bill->month] ?? $bill->month;
                             $amount = 'Rp ' . number_format($bill->amount, 0, ',', '.');
                             $msg .= "- {$monthName} {$bill->year}: {$amount}\n";
                         }

                         $totalUnpaid = $unpaidBills->sum('amount');
                         $totalFormatted = 'Rp ' . number_format($totalUnpaid, 0, ',', '.');

                         $msg .= "\nTotal: *{$totalFormatted}*\n";
                         $msg .= "Mohon segera diselesaikan. Terima kasih.";
                         
                         $waLink = "https://wa.me/{$phone}?text=" . urlencode($msg);
                    } else {
                        // All Paid Case
                         $msg = "Assalamu'alaikum.\n";
                         $msg .= "Terima kasih, pembayaran Syahriah Ananda *" . strtoupper($student->name) . "* (NIS: {$student->nis}) sudah LUNAS semua.\n";
                         $waLink = "https://wa.me/{$phone}?text=" . urlencode($msg);
                    }
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
                    'current_due_formatted' => 'Rp ' . number_format($currentDue, 0, ',', '.'),
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
            'academicYears' => $academicYears,
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
