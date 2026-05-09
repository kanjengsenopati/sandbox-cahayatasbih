<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Carbon\Carbon;
use App\Models\Bill;
use App\Models\School;
use App\Models\Student;
use App\Models\BillType;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\SendNotifWaService;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentBillNotification;
use App\Jobs\SendToWhatsappNotificationJob;
use App\Jobs\SendBillWhatsappNotificationJob;
use App\Jobs\SendUnpaidBillNotificationJob;
use Illuminate\Support\Facades\Log;

class ReportBillStudentController extends Controller
{
    /**
     * Excluded bill type IDs (system/internal types).
     */
    private const EXCLUDED_BILL_TYPE_IDS = [
        '02dae620-fc2c-4bf2-9e13-c5c1950e4d48',
        '615a34af-be2d-45f2-9830-720fea341a0c',
        'f3a25c77-f8c0-4882-8286-571bc57bf87c',
        'ce389861-40ab-4523-9364-3458e9dfda1d',
    ];

    /**
     * Indonesian month names.
     */
    private const MONTH_NAMES = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function index()
    {
        if (!Auth::user()->can('Manage Laporan Tagihan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            return $this->handleAjaxRequest();
        }

        $schools = School::orderBy('name')->get();
        $billTypes = BillType::select('id', 'name')
            ->whereNotIn('id', self::EXCLUDED_BILL_TYPE_IDS)
            ->get();
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();

        return view('admins.report-bill-student.index', compact('schools', 'billTypes', 'academicYears'));
    }

    /**
     * Route AJAX requests to the correct handler.
     */
    private function handleAjaxRequest()
    {
        return match (request()->data) {
            'total'       => $this->getTotal(),
            'table'       => $this->getTable(),
            'rekap_total' => $this->getRekapTotal(),
            'rekap_table' => $this->getRekapTable(),
            'rekap_detail'=> $this->getRekapDetail(),
            default       => response()->json(['error' => 'Invalid data type'], 400),
        };
    }

    // =========================================================================
    // TAB 1: DATA TAGIHAN (Existing - Per-Bill Row)
    // =========================================================================

    /**
     * Build the base Bill query with all shared filters applied.
     */
    private function buildBillQuery()
    {
        return Bill::when(request()->filled('start_date'), function ($query) {
                $startDate = Carbon::parse(request()->start_date);
                $query->where(function ($sub) use ($startDate) {
                    $sub->where('year', '>', $startDate->year)
                        ->orWhere(function ($q) use ($startDate) {
                            $q->where('year', '=', $startDate->year)
                              ->where('month', '>=', $startDate->month);
                        });
                });
            })
            ->when(request()->filled('end_date'), function ($query) {
                $endDate = Carbon::parse(request()->end_date);
                $query->where(function ($sub) use ($endDate) {
                    $sub->where('year', '<', $endDate->year)
                        ->orWhere(function ($q) use ($endDate) {
                            $q->where('year', '=', $endDate->year)
                              ->where('month', '<=', $endDate->month);
                        });
                });
            })
            ->when(request()->filled('academic_year_id'), function ($query) {
                $query->where('academic_year_id', request()->academic_year_id);
            })
            ->schoolFilter('school_id', request()->school_id)
            ->classroomFilter('classroom_id', request()->classroom_id)
            ->when(request()->filled('bill_type_id'), function ($query) {
                $query->whereIn('bill_type_id', request()->bill_type_id);
            })
            ->when(request()->filled('status'), function ($query) {
                $query->where('status', request()->status);
            })
            ->when(
                request()->has('search') && is_array(request()->search) && isset(request()->search['value']),
                function ($query) {
                    $searchTerm = request()->search['value'];
                    $query->whereHas('student', function ($sub) use ($searchTerm) {
                        $sub->where('name', 'like', '%' . $searchTerm . '%');
                    });
                }
            )
            ->latest();
    }

    /**
     * Summary cards for Tab 1 (Target Pemasukan, Lunas, Belum Lunas).
     */
    private function getTotal()
    {
        $totals = $this->buildBillQuery()
            ->selectRaw("
                SUM(CASE WHEN status = '" . Bill::STATUS_PAID . "' THEN amount ELSE 0 END) as total_paid,
                SUM(CASE WHEN status = '" . Bill::STATUS_UNPAID . "' THEN amount ELSE 0 END) as total_unpaid,
                SUM(amount) as total
            ")->first();

        return response()->json([
            'total_paid'     => number_format($totals->total_paid ?? 0, 0, ',', '.'),
            'total_unpaid'   => number_format($totals->total_unpaid ?? 0, 0, ',', '.'),
            'target_revenue' => number_format($totals->total ?? 0, 0, ',', '.'),
        ]);
    }

    /**
     * DataTable for Tab 1 — individual bill rows.
     */
    private function getTable()
    {
        $data = $this->buildBillQuery();

        return DataTables::of($data)
            ->addColumn('amount', fn($row) => 'Rp' . number_format($row->amount, 0, ',', '.'))
            ->addColumn('bill_type', fn($row) => $row->billType?->name ?? '-')
            ->addColumn('status', fn($row) => $row->status == 'UNPAID'
                ? '<span class="badge badge-danger">Belum Lunas</span>'
                : '<span class="badge badge-success">Lunas</span>')
            ->addColumn('student', function ($row) {
                $studentName = $row->student?->name ?? '-';
                $className   = $row->classroom?->name ?? '-';
                $avatarUrl   = $row->student?->avatar ?: asset('assets/media/avatars/default.png');
                return '<div class="student-card" style="display:flex;align-items:center;gap:10px;">
                    <img src="' . $avatarUrl . '" alt="Avatar" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                    <div><div><strong>' . $studentName . '</strong></div><div>' . $className . '</div></div>
                </div>';
            })
            ->addColumn('period', function ($row) {
                $monthName = self::MONTH_NAMES[$row->month] ?? 'Invalid (' . $row->month . ')';
                return $monthName . ' ' . $row->year;
            })
            ->addColumn('notification', function ($row) {
                $notification = $row?->student?->studentBillNotifications?->first();
                return $notification
                    ? '<span class="badge badge-success">Dikirim ' . Carbon::parse($notification->sent_at)->diffForHumans() . '</span>'
                    : '<span class="badge badge-danger">Belum Dikirim</span>';
            })
            ->addColumn('action', function ($row) {
                $actionDelete = route('report-transaction.destroy', $row->id);
                return "<div class='d-flex gap-2 flex-nowrap justify-content-center'>" .
                    view('components.action.delete', ['action' => $actionDelete, 'id' => $row->id, 'name' => 'Laporan Transaksi']) .
                    "</div>";
            })
            ->rawColumns(['amount', 'action', 'student', 'status', 'notification'])
            ->make(true);
    }

    // =========================================================================
    // TAB 2: REKAP PER-SANTRI (New - Grouped by Student)
    // =========================================================================

    /**
     * Build the optimized rekap query using JOIN + GROUP BY at DB level.
     * Groups all bill types across multiple academic years per student.
     */
    private function buildRekapQuery()
    {
        $query = Student::query()
            ->select([
                'students.id',
                'students.name',
                'students.avatar',
                'students.classroom_id',
                'classrooms.name as classroom_name',
                DB::raw('COUNT(bills.id) as bill_count'),
                DB::raw('SUM(bills.amount) as total_bill'),
                DB::raw('SUM(CASE WHEN bills.status = "PAID" THEN bills.amount ELSE 0 END) as total_paid'),
                DB::raw('SUM(CASE WHEN bills.status = "UNPAID" THEN bills.amount ELSE 0 END) as total_unpaid'),
                DB::raw('SUM(CASE
                    WHEN bills.status = "UNPAID" AND (
                        bills.year < ' . date('Y') . ' OR (bills.year = ' . date('Y') . ' AND bills.month <= ' . date('n') . ')
                    ) THEN bills.amount
                    ELSE 0
                END) as current_due_amount'),
            ])
            ->join('bills', 'bills.student_id', '=', 'students.id')
            ->join('classrooms', 'classrooms.id', '=', 'students.classroom_id')
            ->whereNull('bills.deleted_at');

        // Date range filter
        if (request()->filled('start_date')) {
            $startDate = Carbon::parse(request()->start_date);
            $startPeriod = (int)($startDate->year . str_pad($startDate->month, 2, '0', STR_PAD_LEFT));
            $query->whereRaw('(bills.year * 100 + bills.month) >= ?', [$startPeriod]);
        }

        if (request()->filled('end_date')) {
            $endDate = Carbon::parse(request()->end_date);
            $endPeriod = (int)($endDate->year . str_pad($endDate->month, 2, '0', STR_PAD_LEFT));
            $query->whereRaw('(bills.year * 100 + bills.month) <= ?', [$endPeriod]);
        }

        // Academic year filter
        if (request()->filled('academic_year_id')) {
            $query->where('bills.academic_year_id', request()->academic_year_id);
        }

        // School filter via classroom
        if (request()->filled('school_id')) {
            $query->where('classrooms.school_id', request()->school_id);
        }

        // Classroom filter
        if (request()->filled('classroom_id')) {
            $query->where('students.classroom_id', request()->classroom_id);
        }

        // Bill type filter
        if (request()->filled('bill_type_id')) {
            $query->whereIn('bills.bill_type_id', request()->bill_type_id);
        }

        // Status filter using HAVING (runs after GROUP BY)
        if (request()->filled('status')) {
            if (request()->status === 'PAID') {
                $query->havingRaw('total_bill = total_paid AND total_bill > 0');
            } elseif (request()->status === 'UNPAID') {
                $query->havingRaw('total_unpaid > 0');
            }
        }

        // GROUP BY student
        $query->groupBy('students.id', 'students.name', 'students.avatar', 'students.classroom_id', 'classrooms.name');

        return $query;
    }

    /**
     * Summary cards for Tab 2.
     */
    private function getRekapTotal()
    {
        // Use raw DB query for maximum performance (no model overhead)
        $baseQuery = DB::table('bills')
            ->join('students', 'students.id', '=', 'bills.student_id')
            ->join('classrooms', 'classrooms.id', '=', 'students.classroom_id')
            ->whereNull('bills.deleted_at');

        // Apply the same filters
        if (request()->filled('start_date')) {
            $startDate = Carbon::parse(request()->start_date);
            $startPeriod = (int)($startDate->year . str_pad($startDate->month, 2, '0', STR_PAD_LEFT));
            $baseQuery->whereRaw('(bills.year * 100 + bills.month) >= ?', [$startPeriod]);
        }
        if (request()->filled('end_date')) {
            $endDate = Carbon::parse(request()->end_date);
            $endPeriod = (int)($endDate->year . str_pad($endDate->month, 2, '0', STR_PAD_LEFT));
            $baseQuery->whereRaw('(bills.year * 100 + bills.month) <= ?', [$endPeriod]);
        }
        if (request()->filled('academic_year_id')) {
            $baseQuery->where('bills.academic_year_id', request()->academic_year_id);
        }
        if (request()->filled('school_id')) {
            $baseQuery->where('classrooms.school_id', request()->school_id);
        }
        if (request()->filled('classroom_id')) {
            $baseQuery->where('students.classroom_id', request()->classroom_id);
        }
        if (request()->filled('bill_type_id')) {
            $baseQuery->whereIn('bills.bill_type_id', request()->bill_type_id);
        }

        $stats = $baseQuery->selectRaw('
            COUNT(DISTINCT bills.student_id) as total_students,
            SUM(bills.amount) as total_amount,
            SUM(CASE WHEN bills.status = ? THEN bills.amount ELSE 0 END) as total_paid,
            SUM(CASE WHEN bills.status = ? THEN bills.amount ELSE 0 END) as total_unpaid,
            SUM(CASE
                WHEN bills.status = ? AND (
                    bills.year < ? OR (bills.year = ? AND bills.month <= ?)
                ) THEN bills.amount
                ELSE 0
            END) as total_current_due
        ', [
            Bill::STATUS_PAID,
            Bill::STATUS_UNPAID,
            Bill::STATUS_UNPAID,
            date('Y'), date('Y'), date('n'),
        ])->first();

        $totalAmount = $stats->total_amount ?? 0;
        $totalPaid   = $stats->total_paid ?? 0;
        $percentage  = $totalAmount == 0 ? 0 : ($totalPaid / $totalAmount) * 100;

        return response()->json([
            'total_students'    => number_format($stats->total_students ?? 0, 0, ',', '.'),
            'total_amount'      => number_format($totalAmount, 0, ',', '.'),
            'total_paid'        => number_format($totalPaid, 0, ',', '.'),
            'total_unpaid'      => number_format($stats->total_unpaid ?? 0, 0, ',', '.'),
            'total_current_due' => number_format($stats->total_current_due ?? 0, 0, ',', '.'),
            'percentage'        => number_format($percentage, 1, ',', '.'),
        ]);
    }

    /**
     * DataTable for Tab 2 — grouped by student.
     */
    private function getRekapTable()
    {
        $query = $this->buildRekapQuery();

        return DataTables::of($query)
            // Disable global search on calculated columns to prevent SQL errors
            ->filterColumn('total_bill', function () {})
            ->filterColumn('total_paid', function () {})
            ->filterColumn('total_unpaid', function () {})
            ->filterColumn('bill_count', function () {})

            ->addColumn('student', function ($row) {
                $avatarUrl = $row->avatar ?: asset('assets/media/avatars/default.png');
                return '<div class="student-card" style="display:flex;align-items:center;gap:10px;">
                    <img src="' . $avatarUrl . '" alt="Avatar" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                    <div><div><strong>' . e($row->name) . '</strong></div><div>' . e($row->classroom_name) . '</div></div>
                </div>';
            })
            ->addColumn('bill_count_display', fn($row) => number_format($row->bill_count, 0, ',', '.') . ' tagihan')
            ->addColumn('total_bill_display', fn($row) => 'Rp ' . number_format($row->total_bill, 0, ',', '.'))
            ->addColumn('total_paid_display', fn($row) => '<span class="text-success fw-bold">Rp ' . number_format($row->total_paid, 0, ',', '.') . '</span>')
            ->addColumn('total_unpaid_display', function ($row) {
                $amount = $row->total_unpaid;
                if ($amount <= 0) {
                    return '<span class="text-muted">-</span>';
                }
                return '<span class="text-danger fw-bold">Rp ' . number_format($amount, 0, ',', '.') . '</span>';
            })
            ->addColumn('current_due_display', function ($row) {
                if ($row->current_due_amount > 0) {
                    return '<span class="fw-bolder text-danger">Rp ' . number_format($row->current_due_amount, 0, ',', '.') . '</span>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('status', function ($row) {
                if ($row->current_due_amount > 0) {
                    return '<span class="badge badge-light-danger fw-bolder text-danger">Melewati Tempo</span>';
                }
                if ($row->total_unpaid > 0) {
                    return '<span class="badge badge-light-warning fw-bolder text-warning">Belum Lunas</span>';
                }
                return '<span class="badge badge-light-success fw-bolder text-success">Lunas</span>';
            })
            ->addColumn('percentage', function ($row) {
                if ($row->total_bill == 0) return '0%';
                $pct = ($row->total_paid / $row->total_bill) * 100;
                $color = $pct >= 100 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                return '<div class="d-flex align-items-center gap-2">
                    <div class="progress flex-grow-1" style="height:6px;"><div class="progress-bar bg-' . $color . '" style="width:' . min($pct, 100) . '%"></div></div>
                    <span class="fw-bold text-' . $color . '" style="min-width:40px">' . number_format($pct, 0) . '%</span>
                </div>';
            })
            ->rawColumns(['student', 'total_paid_display', 'total_unpaid_display', 'current_due_display', 'status', 'percentage'])
            ->make(true);
    }

    /**
     * Details for Tab 2 — individual bills for a specific student.
     */
    private function getRekapDetail()
    {
        $studentId = request()->student_id;
        if (!$studentId) {
            return response()->json([]);
        }

        $query = $this->buildBillQuery()->where('student_id', $studentId);
        $bills = $query->with('billType')->get();

        $details = $bills->map(function ($bill) {
            $monthName = self::MONTH_NAMES[$bill->month] ?? 'Invalid';
            $statusBadge = $bill->status == 'UNPAID'
                ? '<span class="badge badge-light-danger fs-8">Belum Lunas</span>'
                : '<span class="badge badge-light-success fs-8">Lunas</span>';

            return [
                'bill_type' => $bill->billType?->name ?? '-',
                'period'    => $monthName . ' ' . $bill->year,
                'amount'    => 'Rp ' . number_format($bill->amount, 0, ',', '.'),
                'status'    => $statusBadge,
            ];
        });

        return response()->json($details);
    }

    // =========================================================================
    // WA BLAST
    // =========================================================================

    public function sendBillWhatsappNotification()
    {
        try {
            dispatch(new SendUnpaidBillNotificationJob());

            return response()->json([
                'success' => true,
                'message' => 'WA Blast sedang diproses di queue.'
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengirim WA Blast: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim WA Blast: ' . $e->getMessage(),
            ], 500);
        }
    }
}
