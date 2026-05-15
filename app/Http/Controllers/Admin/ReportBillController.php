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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportBillDetailExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
                    'realisasion_percentage' => $total == 0 ? 0 : ($totalPaid / $total) * 100, // Return float for calculations
                    'realisasion_percentage_text' => $total == 0 ? '0%' : number_format(($totalPaid / $total) * 100, 2, ',', '.') . '%',
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
     * Menampilkan Halaman Detail Laporan Tagihan
     */
    public function show(string $id)
    {
        $this->authorize('Manage Laporan Tagihan');

        // Jika Request AJAX (DataTables / Summary Cards)
        if (request()->ajax()) {
            return $this->handleAjaxRequest($id);
        }

        // Load halaman awal
        $billType = BillType::findOrFail($id);
        
        // Optimasi: Select hanya kolom yang dibutuhkan agar ringan
        $schools = School::hasSchool()
            ->select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();

        return view('admins.report-bill.show', compact('billType', 'schools'));
    }

    /**
     * Mengatur logic request AJAX
     */
    private function handleAjaxRequest(string $id)
    {
        // Request untuk tabel utama
        if (request()->type === 'bill') {
            return $this->getBillData($id);
        }

        // Request untuk kartu summary (Total, Realisasi, Sisa)
        if (request()->type === 'total') {
            return $this->getTotalData($id);
        }
        
        return abort(404);
    }

    /**
     * CORE OPTIMIZATION: Query DataTables
     * Menggunakan JOIN + GROUP BY + SUM di level Database.
     */
    private function getBillData(string $id)
    {
        // 1. Siapkan Parameter Filter Tanggal (Integer YYYYMM)
        // Teknik ini 50x lebih cepat daripada string date comparison
        $startDate = request()->start_date;
        $endDate = request()->end_date;
        
        $startPeriod = $startDate ? (int)(date('Y', strtotime($startDate)) . date('m', strtotime($startDate))) : null;
        $endPeriod   = $endDate   ? (int)(date('Y', strtotime($endDate))   . date('m', strtotime($endDate)))   : null;

        // 2. Build Query (Query Builder)
        $query = Student::query()
            ->select([
                'students.id',
                'students.name',
                'students.nis',
                'bills.classroom_id', // Dibutuhkan untuk relasi dan grouping (HISTORICAL)
                'classrooms.name as classroom_name',
                // AGGREGATE FUNCTION: Database yang menghitung total
                DB::raw('SUM(bills.amount) as total_bill_amount'),
                DB::raw('SUM(CASE WHEN bills.status = "PAID" THEN bills.amount ELSE 0 END) as total_paid_amount'),
                DB::raw('SUM(CASE 
                    WHEN bills.status = "UNPAID" AND (
                        bills.year < ' . date('Y') . ' OR (bills.year = ' . date('Y') . ' AND bills.month <= ' . date('n') . ')
                    ) THEN bills.amount 
                    ELSE 0 
                END) as current_due_amount')
            ])
            // JOIN Tables (Inner Join filter otomatis data yg kosong)
            ->join('bills', 'bills.student_id', '=', 'students.id')
            ->join('classrooms', 'classrooms.id', '=', 'bills.classroom_id')
            ->where('bills.bill_type_id', $id);

        // 3. Apply Filters
        if (request()->school_id && request()->school_id != 'null') {
            $query->where('classrooms.school_id', request()->school_id);
        }

        if (request()->classroom_id && request()->classroom_id != 'null') {
            $query->where('bills.classroom_id', request()->classroom_id);
        }

        // Filter Tanggal Teroptimasi
        if ($startPeriod && $endPeriod) {
            $query->whereRaw('(bills.year * 100 + bills.month) >= ?', [$startPeriod])
                  ->whereRaw('(bills.year * 100 + bills.month) <= ?', [$endPeriod]);
        }

        // 4. GROUP BY (Wajib karena ada SUM)
        // Grouping berdasarkan ID Siswa dan Kelas Waktu Tagihan
        $query->groupBy('students.id', 'students.name', 'students.nis', 'bills.classroom_id', 'classrooms.name');

        // 5. Filter Status Lunas/Belum (Menggunakan HAVING)
        // "HAVING" berjalan setelah "GROUP BY" selesai menghitung
        if (request()->status === 'PAID') {
            // Lunas jika Total Tagihan > 0 DAN Total Tagihan == Total Bayar
            $query->havingRaw('total_bill_amount = total_paid_amount AND total_bill_amount > 0');
        } elseif (request()->status === 'UNPAID') {
            // Belum Lunas jika Total Tagihan > Total Bayar
            $query->havingRaw('total_bill_amount > total_paid_amount');
        }

        // 6. Return DataTables
        return DataTables::of($query)
            // Matikan pencarian global di kolom kalkulasi untuk mencegah error SQL
            ->filterColumn('total', function(){}) 
            ->filterColumn('total_paid', function(){})
            ->filterColumn('total_unpaid', function(){})
            
            // Format Kolom
            // Format Kolom
            ->addColumn('status', function ($row) {
                if ($row->current_due_amount > 0) return '<span class="badge badge-light-danger fw-bolder text-danger">Melewati Tempo</span>';
             
                $unpaid = $row->total_bill_amount - $row->total_paid_amount;
                if ($unpaid > 0) return '<span class="badge badge-light-warning fw-bolder text-warning">Belum Lunas</span>';
                
                return '<span class="badge badge-light-success fw-bolder text-success">Lunas</span>';
            })
            ->addColumn('total', fn($row) => 'Rp ' . number_format($row->total_bill_amount, 0, ',', '.'))
            ->addColumn('total_paid', fn($row) => 'Rp ' . number_format($row->total_paid_amount, 0, ',', '.'))
            
            // Kolom Tunggakan (Current Due)
            ->addColumn('current_due', function($row) {
                 return $row->current_due_amount > 0 
                    ? '<span class="fw-bolder text-danger">Rp ' . number_format($row->current_due_amount, 0, ',', '.') . '</span>'
                    : '<span class="text-muted">-</span>';
            })
            
            // Kolom Sisa Kontrak (Future Bill)
            ->addColumn('future_bill', function($row) {
                 $unpaidTotal = $row->total_bill_amount - $row->total_paid_amount;
                 $futureBill = $unpaidTotal - $row->current_due_amount;
                 
                 return $futureBill > 0 
                    ? '<span class="text-muted fw-bold">Rp ' . number_format($futureBill, 0, ',', '.') . '</span>'
                    : '<span class="text-muted">-</span>';
            })
            
            ->addColumn('action', fn($row) => $this->renderActions($row, $id))
            ->rawColumns(['status', 'action', 'current_due', 'future_bill', 'type'])
            ->make(true);
    }

    /**
     * Logic Summary Cards (Total Data di Header)
     */

    /**
     * Logic Summary Cards (Total Data di Header) - SUPER OPTIMIZED
     */
    private function getTotalData(string $id)
    {
        // Ganti Eloquent 'Bill::where...' dengan DB Query Builder + JOIN
        // Ini jauh lebih ringan daripada 'whereHas' karena menghindari subquery
        $query = DB::table('bills')
            ->join('students', 'students.id', '=', 'bills.student_id')
            ->join('classrooms', 'classrooms.id', '=', 'bills.classroom_id')
            ->where('bills.bill_type_id', $id);

        // 1. Filter Sekolah (Sekarang pakai alias 'classrooms')
        if (request()->school_id && request()->school_id != 'null') {
             $query->where('classrooms.school_id', request()->school_id);
        }

        // 2. Filter Kelas (Sekarang pakai historical bills.classroom_id)
        if (request()->classroom_id && request()->classroom_id != 'null') {
             $query->where('bills.classroom_id', request()->classroom_id);
        }

        // 3. Filter Tanggal (Logic Integer Index)
        if (request()->start_date && request()->end_date) {
            $startPeriod = (int)(date('Y', strtotime(request()->start_date)) . date('m', strtotime(request()->start_date)));
            $endPeriod   = (int)(date('Y', strtotime(request()->end_date))   . date('m', strtotime(request()->end_date)));
            
            // Pastikan kolom spesifik tabel bills
            $query->whereRaw('(bills.year * 100 + bills.month) >= ?', [$startPeriod])
                  ->whereRaw('(bills.year * 100 + bills.month) <= ?', [$endPeriod]);
        }

        // 4. Filter Status
        if (request()->status === 'UNPAID') $query->where('bills.status', Bill::STATUS_UNPAID);
        if (request()->status === 'PAID') $query->where('bills.status', Bill::STATUS_PAID);

        // 5. Aggregate Calculation
        // Langsung hitung tanpa load model sama sekali. Tambahkan logic Current Due
        $stats = $query->selectRaw('
            SUM(bills.amount) as total_amount,
            SUM(CASE WHEN bills.status = ? THEN bills.amount ELSE 0 END) as total_paid,
            SUM(CASE 
                WHEN bills.status = ? AND (
                    bills.year < ? OR (bills.year = ? AND bills.month <= ?)
                ) THEN bills.amount 
                ELSE 0 
            END) as total_current_due
        ', [
            Bill::STATUS_PAID, 
            Bill::STATUS_UNPAID, 
            date('Y'), 
            date('Y'), 
            date('n')
        ])->first();

        // 6. Formatting Result
        $total = $stats->total_amount ?? 0;
        $totalPaid = $stats->total_paid ?? 0;
        $totalUnpaid = $total - $totalPaid;
        $percentage = $total == 0 ? 0 : ($totalPaid / $total) * 100;

        return response()->json([
            'total' => number_format($total, 0, ',', '.'),
            'total_paid' => number_format($totalPaid, 0, ',', '.'),
            'realisasion_percentage' => $percentage, // Biarkan float utk progress bar width
            'realisasion_percentage_text' => number_format($percentage, 2, ',', '.') . '%',
            'total_unpaid' => number_format($totalUnpaid, 0, ',', '.'),
            'total_current_due' => number_format($stats->total_current_due ?? 0, 0, ',', '.')
        ]);
    }

    /**
     * Helper Render Action Buttons
     */
    private function renderActions($row, $billTypeId)
    {
        // URL Generator manual untuk performa
        $actionDetail = url("/bill/summary-bill?student_id={$row->id}&bill_type_id={$billTypeId}");
        
        // Button HTML
        return "
        <div class='d-flex gap-2 flex-nowrap justify-content-center'>
            <a href='{$actionDetail}' class='btn btn-primary btn-sm' target='_blank'>Detail</a>
            <button onclick='sendNotification({$row->id}, {$billTypeId})' class='btn btn-icon btn-success btn-sm' data-bs-toggle='tooltip' title='Kirim Tagihan'>
                <i class='fab fa-whatsapp fs-4'></i>
            </button>
        </div>";
    }

    // public function show(string $id)
    // {
    //     $this->authorize('Manage Laporan Tagihan');

    //     if (request()->ajax()) {
    //         return $this->handleAjaxRequest($id);
    //     }

    //     $billType = BillType::findOrFail($id);
    //     $schools = School::hasSchool()->orderBy('name', 'asc')->get();

    //     return view('admins.report-bill.show', compact('billType', 'schools'));
    // }

    // public function export(string $id)
    // {
    //     $this->authorize('Manage Laporan Tagihan');

    //     // 1. Determine Years based on Academic Year Filter or Current Year
    //     $startYear = date('Y');
    //     if (request()->academic_year_id) {
    //         $academicYear = AcademicYear::find(request()->academic_year_id);
    //         if ($academicYear) {
    //             // Assumes format "2024/2025"
    //             $parts = explode('/', $academicYear->name);
    //             if (count($parts) >= 1) {
    //                 $startYear = (int) trim($parts[0]);
    //             }
    //         }
    //     }
    //     $endYear = $startYear + 1;

    //     // 2. Generate Periods (July Y to June Y+1)
    //     $periods = [];
    //     $currentDate = Carbon::create($startYear, 7, 1); // 1 July StartYear
    //     $endDate = Carbon::create($endYear, 6, 30);      // 30 June EndYear
        
    //     $months = [
    //         1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    //         5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    //         9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    //     ];

    //     while ($currentDate->lte($endDate)) {
    //         $periods[] = [
    //             'month' => $currentDate->month,
    //             'year' => $currentDate->year,
    //             'label' => ($months[$currentDate->month] ?? $currentDate->month) . ' ' . $currentDate->year,
    //         ];
    //         $currentDate->addMonth();
    //     }

    //     // 3. Query Students with Bills filtered by this range
    //     $query = Student::select('students.*', 'classrooms.name as classroom_name')
    //         ->join('classrooms', 'classrooms.id', '=', 'students.classroom_id')
    //         ->join('bills', 'bills.student_id', '=', 'students.id')
    //         ->where('bills.bill_type_id', $id)
    //         ->when(request()->school_id && request()->school_id !== 'null', function ($q) {
    //             $q->where('classrooms.school_id', request()->school_id);
    //         })
    //         ->when(request()->classroom_id && request()->classroom_id !== 'null', function ($q) {
    //             $q->where('students.classroom_id', request()->classroom_id);
    //         })
    //         ->when(request()->academic_year_id && request()->academic_year_id !== 'null', function ($q) {
    //             // Optional: Filter students who strictly have bills linked to this academic_year_id
    //             // But usually the date range filter on bills is enough and safer for "report" purposes across boundaries
    //              $q->where('bills.academic_year_id', request()->academic_year_id);
    //         })
    //         ->when(request()->status, fn($q) => $this->filterByStatus($q, request()->status, $id))
    //         ->with(['classroom', 'bills' => function ($q) use ($id, $startYear, $endYear) {
    //             $q->where('bill_type_id', $id)
    //               ->where(function ($sub) use ($startYear, $endYear) {
    //                   // July of Start Year onwards (July - Dec)
    //                   $sub->where(function ($k) use ($startYear) {
    //                       $k->where('year', $startYear)->where('month', '>=', 7);
    //                   })
    //                   // Up to June of End Year (Jan - June)
    //                   ->orWhere(function ($k) use ($endYear) {
    //                       $k->where('year', $endYear)->where('month', '<=', 6);
    //                   });
    //               });
    //         }])
    //         ->orderBy('classrooms.name', 'asc')
    //         ->orderBy('students.name', 'asc')
    //         ->distinct();

    //     $students = $query->get();

    //     // 4. Calculate Totals based on the filtered bills
    //     $this->calculateStudentTotals($students, $id);

    //     // 5. Dynamic Filename
    //     $billTypeName = BillType::where('id', $id)->value('name') ?? 'Tagihan';
    //     $safeName = preg_replace('/[^A-Za-z0-9 _-]/', '', $billTypeName); // Sanitize filename
    //     $fileName = "Laporan Tagihan {$safeName}.xlsx";

    //     return Excel::download(new ReportBillDetailExport($students, $periods, $id), $fileName);
    // }

    // private function handleAjaxRequest(string $id)
    // {
    //     $dateRange = $this->getDateRange();

    //     if (request()->type === 'bill') {
    //         return $this->getBillData($id, $dateRange);
    //     }

    //     if (request()->type === 'total') {
    //         return $this->getTotalData($id, $dateRange);
    //     }
    // }

    // private function getDateRange()
    // {
    //     return [
    //         'startMonth' => request()->start_date ? date('n', strtotime(request()->start_date)) : null,
    //         'endMonth' => request()->end_date ? date('n', strtotime(request()->end_date)) : null,
    //         'startYear' => request()->start_date ? date('Y', strtotime(request()->start_date)) : null,
    //         'endYear' => request()->end_date ? date('Y', strtotime(request()->end_date)) : null,
    //     ];
    // }

    // private function getBillData(string $id, array $dateRange)
    // {
    //     $students = $this->getFilteredStudents($id, $dateRange);
    //     $this->calculateStudentTotals($students, $id);

    //     return DataTables::of($students)
    //         ->addColumn('total_unpaid', fn($student) => $this->formatCurrency($student->total_unpaid))
    //         ->addColumn('total_paid', fn($student) => $this->formatCurrency($student->total_paid))
    //         ->addColumn('total', fn($student) => $this->formatCurrency($student->total))
    //         ->addColumn('status', fn($student) => $student->status)
    //         ->addColumn('action', fn($data) => $this->renderActions($data, $id))
    //         ->rawColumns(['status', 'action'])
    //         ->make(true);
    // }

    // private function getFilteredStudents(string $id, array $dateRange)
    // {
    //     return Student::select('students.*')
    //         ->join('bills', 'bills.student_id', '=', 'students.id')
    //         ->where('bills.bill_type_id', $id)
    //         // ->whereBetween('bills.month', [$dateRange['startMonth'], $dateRange['endMonth']])
    //         // ->whereBetween('bills.year', [$dateRange['startYear'], $dateRange['endYear']])
    //         ->when(request()->school_id && request()->school_id !== 'null', $this->schoolFilter())
    //         ->when(request()->classroom_id && request()->classroom_id !== 'null', $this->classroomFilter())
    //         ->when(request()->status, fn($query) => $this->filterByStatus($query, request()->status, $id))
    //         // ->when($dateRange['startYear'] && $dateRange['endYear'], $this->dateRangeFilter($dateRange))
    //         ->hasSchool()
    //         ->orderBy('students.name', 'asc')
    //         ->distinct()
    //         ->get();
    // }

    // private function schoolFilter()
    // {
    //     return function ($query) {
    //         return $query->join('classrooms', 'classrooms.id', '=', 'students.classroom_id')
    //             ->where('classrooms.school_id', request()->school_id);
    //     };
    // }

    // private function classroomFilter()
    // {
    //     return fn($query) => $query->where('students.classroom_id', request()->classroom_id);
    // }

    // private function dateRangeFilter(array $dateRange)
    // {
    //     return function ($query) use ($dateRange) {
    //         $query->whereBetween('bills.year', [$dateRange['startYear'], $dateRange['endYear']])
    //             ->whereBetween('bills.month', [$dateRange['startMonth'], $dateRange['endMonth']]);
    //     };
    // }

    // private function calculateStudentTotals(&$students, string $id)
    // {
    //     $totalPaidSum = 0;
    //     $totalUnpaidSum = 0;

    //     $students->map(function ($student) use (&$totalPaidSum, &$totalUnpaidSum, $id) {
    //         // Use the already eager loaded 'bills' collection.
    //         // In the 'export' method, this collection is already filtered by date range and bill_type_id.
    //         // In 'getBillData' (ajax), it might be all bills or lazy loaded.
            
    //         // We filter by bill_type_id again just to be safe if this method is used elsewhere 
    //         // where the relation wasn't pre-filtered by type.
    //         // The collection filter operation is fast and in-memory.
    //         $bills = $student->bills->where('bill_type_id', $id);

    //         $total = $bills->sum('amount');
    //         $studentTotalPaid = $bills->where('status', Bill::STATUS_PAID)->sum('amount');
            
    //         $studentTotalUnpaid = $total - $studentTotalPaid;

    //         // Hitung Tagihan Berjalan (Current Due)
    //         // Sisa tagihan yg bulan & tahunnya <= Saat ini
    //         $nowYear = date('Y');
    //         $nowMonth = date('n');

    //         $currentDue = $bills->filter(function ($bill) use ($nowYear, $nowMonth) {
    //             // Hanya hitung yang BELUM LUNAS
    //             if ($bill->status == Bill::STATUS_PAID) return false;

    //             // Cek apakah periode bill <= Saat ini
    //             if ($bill->year < $nowYear) return true;
    //             if ($bill->year == $nowYear && $bill->month <= $nowMonth) return true;
                
    //             return false;
    //         })->sum('amount');

    //         $totalPaidSum += $studentTotalPaid;
    //         $totalUnpaidSum += $studentTotalUnpaid;

    //         $student->total_paid = $studentTotalPaid;
    //         $student->total_unpaid = $studentTotalUnpaid;
    //         $student->current_due = $currentDue; // New Field 
    //         $student->total = $total;
    //         $student->status = $this->getPaymentStatus($studentTotalPaid, $total);

    //         return $student;
    //     });
    // }

    // private function getPaymentStatus($paid, $total)
    // {
    //     if ($paid === 0) {
    //         return '<span class="badge bg-danger">Belum Bayar</span>';
    //     } elseif ($paid === $total) {
    //         return '<span class="badge bg-success">Lunas</span>';
    //     } else {
    //         return '<span class="badge bg-warning">Belum Lunas</span>';
    //     }
    // }

    // private function getTotalData(string $id, array $dateRange)
    // {
    //     $billQuery = Bill::where('bill_type_id', $id)
    //         ->when(request()->school_id && request()->school_id !== 'null', $this->schoolBillFilter())
    //         ->when(request()->classroom_id && request()->classroom_id !== 'null', $this->classroomBillFilter())
    //         ->when(request()->status === 'UNPAID', fn($query) => $query->where('status', Bill::STATUS_UNPAID))
    //         ->when(request()->status === 'PAID', fn($query) => $query->where('status', Bill::STATUS_PAID))
    //         ->when($dateRange['startYear'] && $dateRange['endYear'], $this->dateRangeFilter($dateRange));

    //     $total = $billQuery->sum('amount');
    //     $totalPaid = $billQuery->where('status', Bill::STATUS_PAID)->sum('amount');
    //     $totalUnpaid = $total - $totalPaid;

    //     return response()->json([
    //         'total' => number_format($total, 0, ',', '.'),
    //         'total_paid' => number_format($totalPaid, 0, ',', '.'),
    //         'realisasion_percentage' => $total == 0 ? 0 : ($totalPaid / $total) * 100, // Return float
    //         'realisasion_percentage_text' => $total == 0 ? '0%' : number_format(($totalPaid / $total) * 100, 2, ',', '.') . '%',
    //         'total_unpaid' => number_format($totalUnpaid, 0, ',', '.')
    //     ]);
    // }

    // private function schoolBillFilter()
    // {
    //     return fn($query) => $query->whereHas('student.classroom', fn($q) => $q->where('school_id', request()->school_id));
    // }

    // private function classroomBillFilter()
    // {
    //     return fn($query) => $query->whereHas('student', fn($q) => $q->where('classroom_id', request()->classroom_id));
    // }

    // private function getBillTotal($studentId, $billTypeId, $status = null)
    // {
    //     $query = Bill::where('student_id', $studentId)
    //         ->where('bill_type_id', $billTypeId);

    //     if ($status) {
    //         $query->where('status', $status);
    //     }

    //     return $query->sum('amount');
    // }

    // private function formatCurrency($amount)
    // {
    //     return 'Rp. ' . number_format($amount, 0, ',', '.');
    // }

    // private function filterByStatus($query, $status, $billTypeId)
    // {
    //     if ($status === 'UNPAID') {
    //         return $query->whereHas('bills', fn($q) => $q->where('status', Bill::STATUS_UNPAID)->where('bill_type_id', $billTypeId));
    //     } elseif ($status === 'PAID') {
    //         return $query->whereDoesntHave('bills', fn($q) => $q->where('status', Bill::STATUS_UNPAID)->where('bill_type_id', $billTypeId));
    //     }
    //     return $query;
    // }

    // private function renderActions($data, $billTypeId)
    // {
    //     $actionDetail = route('bill.summary-bill', ['student_id' => $data->id, 'bill_type_id' => $billTypeId]);
    //     $notificationRoute = route('report-bill.send-bill-notification');

    //     return "
    //     <div class='d-flex gap-2 flex-nowrap justify-content-center'>
    //         <a href='{$actionDetail}' class='btn btn-primary btn-sm'>Detail</a>
    //         <form action='{$notificationRoute}' method='POST'>
    //             " . csrf_field() . "
    //             <input type='hidden' name='bill_type_id' value='{$billTypeId}'>
    //             <input type='hidden' name='student_id[]' value='{$data->id}'>
    //             <button type='submit' class='btn btn-success btn-sm'>Kirim Notifikasi</button>
    //         </form>
    //     </div>";
    // }

     public function export(string $id)
    {
        $this->authorize('Manage Laporan Tagihan');

        // 1. Determine Years based on Academic Year Filter or Current Year
        $startYear = date('Y');
        if (request()->academic_year_id) {
            $academicYear = AcademicYear::find(request()->academic_year_id);
            if ($academicYear) {
                // Assumes format "2024/2025"
                $parts = explode('/', $academicYear->name);
                if (count($parts) >= 1) {
                    $startYear = (int) trim($parts[0]);
                }
            }
        }
        $endYear = $startYear + 1;

        // 2. Generate Periods (July Y to June Y+1)
        $periods = [];
        $currentDate = Carbon::create($startYear, 7, 1); // 1 July StartYear
        $endDate = Carbon::create($endYear, 6, 30);      // 30 June EndYear
        
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        while ($currentDate->lte($endDate)) {
            $periods[] = [
                'month' => $currentDate->month,
                'year' => $currentDate->year,
                'label' => ($months[$currentDate->month] ?? $currentDate->month) . ' ' . $currentDate->year,
            ];
            $currentDate->addMonth();
        }

        // 3. Query Students with Bills filtered by this range
        $query = Student::select('students.*', 'classrooms.name as classroom_name')
            ->join('bills', 'bills.student_id', '=', 'students.id')
            ->join('classrooms', 'classrooms.id', '=', 'bills.classroom_id')
            ->where('bills.bill_type_id', $id)
            ->when(request()->school_id && request()->school_id !== 'null', function ($q) {
                $q->where('classrooms.school_id', request()->school_id);
            })
            ->when(request()->classroom_id && request()->classroom_id !== 'null', function ($q) {
                $q->where('bills.classroom_id', request()->classroom_id);
            })
            ->when(request()->academic_year_id && request()->academic_year_id !== 'null', function ($q) {
                // Optional: Filter students who strictly have bills linked to this academic_year_id
                // But usually the date range filter on bills is enough and safer for "report" purposes across boundaries
                 $q->where('bills.academic_year_id', request()->academic_year_id);
            })
            ->when(request()->status, fn($q) => $this->filterByStatus($q, request()->status, $id))
            ->with(['classroom', 'bills' => function ($q) use ($id, $startYear, $endYear) {
                $q->where('bill_type_id', $id)
                  ->where(function ($sub) use ($startYear, $endYear) {
                      // July of Start Year onwards (July - Dec)
                      $sub->where(function ($k) use ($startYear) {
                          $k->where('year', $startYear)->where('month', '>=', 7);
                      })
                      // Up to June of End Year (Jan - June)
                      ->orWhere(function ($k) use ($endYear) {
                          $k->where('year', $endYear)->where('month', '<=', 6);
                      });
                  });
            }])
            ->orderBy('classrooms.name', 'asc')
            ->orderBy('students.name', 'asc')
            ->distinct();

        $students = $query->get();

        // 4. Calculate Totals based on the filtered bills
        $this->calculateStudentTotals($students, $id);

        // 5. Dynamic Filename
        $billTypeName = BillType::where('id', $id)->value('name') ?? 'Tagihan';
        $safeName = preg_replace('/[^A-Za-z0-9 _-]/', '', $billTypeName); // Sanitize filename
        $fileName = "Laporan Tagihan {$safeName}.xlsx";

        return Excel::download(new ReportBillDetailExport($students, $periods, $id), $fileName);
    }

      private function calculateStudentTotals(&$students, string $id)
    {
        $totalPaidSum = 0;
        $totalUnpaidSum = 0;

        $students->map(function ($student) use (&$totalPaidSum, &$totalUnpaidSum, $id) {
            // Use the already eager loaded 'bills' collection.
            // In the 'export' method, this collection is already filtered by date range and bill_type_id.
            // In 'getBillData' (ajax), it might be all bills or lazy loaded.
            
            // We filter by bill_type_id again just to be safe if this method is used elsewhere 
            // where the relation wasn't pre-filtered by type.
            // The collection filter operation is fast and in-memory.
            $bills = $student->bills->where('bill_type_id', $id);

            $total = $bills->sum('amount');
            $studentTotalPaid = $bills->where('status', Bill::STATUS_PAID)->sum('amount');
            
            $studentTotalUnpaid = $total - $studentTotalPaid;

            // Hitung Tagihan Berjalan (Current Due)
            // Sisa tagihan yg bulan & tahunnya <= Saat ini
            $nowYear = date('Y');
            $nowMonth = date('n');

            $currentDue = $bills->filter(function ($bill) use ($nowYear, $nowMonth) {
                // Hanya hitung yang BELUM LUNAS
                if ($bill->status == Bill::STATUS_PAID) return false;

                // Cek apakah periode bill <= Saat ini
                if ($bill->year < $nowYear) return true;
                if ($bill->year == $nowYear && $bill->month <= $nowMonth) return true;
                
                return false;
            })->sum('amount');

            $totalPaidSum += $studentTotalPaid;
            $totalUnpaidSum += $studentTotalUnpaid;

            $student->total_paid = $studentTotalPaid;
            $student->total_unpaid = $studentTotalUnpaid;
            $student->current_due = $currentDue; // New Field 
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
