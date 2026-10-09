<?php

namespace App\Http\Controllers\Admin;

use App\Models\School;
use App\Models\Student;
use App\Models\StudentCardReport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class StudentCardReportController extends Controller
{
    /**
     * Display a listing of card problem reports (Monitoring by Super Admin).
     */
    public function index()
    {
        if (request()->ajax()) {
            $query = StudentCardReport::with(['student.classroom.school', 'reportedBy', 'processedBy'])
                ->when(request('status'), function ($q) {
                    $q->where('status', request('status'));
                })
                ->when(request('issue_type'), function ($q) {
                    $q->where('issue_type', request('issue_type'));
                })
                ->when(request('school_id'), function ($q) {
                    $q->whereHas('student.classroom', function ($sub) {
                        $sub->where('school_id', request('school_id'));
                    });
                })
                ->when(request('search_name'), function ($q) {
                    $term = request('search_name');
                    $q->whereHas('student', function ($sub) use ($term) {
                        $sub->where('name', 'like', "%{$term}%")
                            ->orWhere('nis', 'like', "%{$term}%");
                    });
                })
                ->orderBy('created_at', 'desc');

            return DataTables::of($query)
                ->addColumn('checkbox', function ($data) {
                    return "<div class='form-check form-check-sm form-check-custom form-check-solid justify-content-center'>" .
                        "<input class='form-check-input report-checkbox' type='checkbox' value='{$data->id}' " . ($data->status === 'completed' ? 'disabled' : '') . " />" .
                        "</div>";
                })
                ->editColumn('created_at', function ($data) {
                    return "<span class='text-gray-700 fw-bold'>" . $data->created_at->format('d/m/Y') . "</span>" .
                        "<br><small class='text-muted fs-8'>" . $data->created_at->format('H:i') . " WIB</small>";
                })
                ->addColumn('student_info', function ($data) {
                    $student = $data->student;
                    if (!$student) {
                        return '<span class="text-danger fst-italic">Data santri tidak ditemukan</span>';
                    }
                    $nis = e($student->nis ?: '-');
                    $name = e($student->name);
                    $class = e($student->classroom->name ?? '-');
                    $school = e($student->classroom->school->name ?? '-');

                    return "<div><strong class='text-gray-900 fs-7'>{$name}</strong></div>" .
                        "<div class='text-muted fs-8'>NIS: {$nis} | {$class} ({$school})</div>";
                })
                ->addColumn('barcode', function ($data) {
                    $barcode = $data->student?->barcode;
                    if ($barcode) {
                        return "<span class='font-monospace fw-bold text-gray-800 fs-7'>" . e($barcode) . "</span>";
                    }
                    return "<span class='badge badge-light-danger fs-8'>Belum ada barcode</span>";
                })
                ->editColumn('issue_type', function ($data) {
                    $badgeClass = match($data->issue_type) {
                        'rusak' => 'badge-light-danger text-danger',
                        'tidak_bisa_transaksi' => 'badge-light-warning text-warning',
                        'hilang' => 'badge-light-primary text-primary',
                        default => 'badge-light-secondary text-secondary',
                    };
                    $icon = match($data->issue_type) {
                        'rusak' => 'fa-heart-broken',
                        'tidak_bisa_transaksi' => 'fa-times-circle',
                        'hilang' => 'fa-search',
                        default => 'fa-exclamation-circle',
                    };
                    return "<span class='badge {$badgeClass} fw-bolder px-2 py-1'><i class='fa {$icon} me-1'></i>" . e($data->issue_label) . "</span>";
                })
                ->addColumn('reporter_info', function ($data) {
                    $reporterName = e($data->reportedBy?->name ?? 'Sistem / Tidak Diketahui');
                    $notes = $data->notes ? "<br><small class='text-gray-600 fst-italic'>\"" . e($data->notes) . "\"</small>" : "";
                    return "<span class='text-gray-800 fw-semibold'>" . $reporterName . "</span>" . $notes;
                })
                ->editColumn('status', function ($data) {
                    if ($data->status === 'completed') {
                        $processedBy = e($data->processedBy?->name ?? 'Admin');
                        $processedTime = $data->processed_at ? $data->processed_at->format('d/m/Y H:i') : '';
                        return "<span class='badge badge-light-success fw-bolder px-2 py-1'><i class='fa fa-check-circle text-success me-1'></i>Selesai Dicetak</span>" .
                            "<br><small class='text-muted fs-8'>Oleh: {$processedBy} ({$processedTime})</small>";
                    }
                    return "<span class='badge badge-light-danger fw-bolder px-2 py-1 pulse pulse-danger'><span class='pulse-ring'></span><i class='fa fa-clock text-danger me-1'></i>Menunggu Pembuatan</span>";
                })
                ->addColumn('action', function ($data) {
                    $studentId = $data->student_id;
                    $studentName = e($data->student?->name ?? '');
                    $studentNis = e($data->student?->nis ?? '-');
                    $studentBarcode = e($data->student?->barcode ?? '');
                    $studentPrevBarcode = e($data->student?->previous_barcode ?? '');

                    $printUrl = route('student.generate-student-card', $studentId);
                    $completeUrl = route('student-card-reports.complete', $data->id);

                    // 1. Tombol Cetak Kartu
                    $btnPrint = "<a href='{$printUrl}' target='_blank' class='btn btn-icon btn-light-primary btn-sm me-1 w-30px h-30px' title='Cetak Ulang Kartu Santri'>" .
                        "<i class='fa fa-print fs-7'></i></a>";

                    // 2. Tombol Edit Barcode Modal
                    $btnEditBarcode = "<button type='button' class='btn btn-icon btn-light-warning btn-sm me-1 w-30px h-30px btn-edit-barcode' " .
                        "data-id='{$studentId}' data-name='{$studentName}' data-nis='{$studentNis}' data-barcode='{$studentBarcode}' data-previous='{$studentPrevBarcode}' " .
                        "title='Edit / Generate Barcode Santri'>" .
                        "<i class='fa fa-barcode fs-7'></i></button>";

                    // 3. Tombol Tandai Selesai
                    if ($data->status !== 'completed') {
                        $btnComplete = "<button type='button' class='btn btn-icon btn-light-success btn-sm me-1 w-30px h-30px btn-complete-report' " .
                            "data-id='{$data->id}' data-url='{$completeUrl}' data-name='{$studentName}' title='Tandai Selesai Dicetak'>" .
                            "<i class='fa fa-check fs-7'></i></button>";
                    } else {
                        $btnComplete = "";
                    }

                    // 4. Tombol Hapus Laporan
                    $deleteUrl = route('student-card-reports.destroy', $data->id);
                    $btnDelete = "<button type='button' class='btn btn-icon btn-light-danger btn-sm w-30px h-30px btn-delete-report' " .
                        "data-id='{$data->id}' data-url='{$deleteUrl}' title='Hapus Laporan'>" .
                        "<i class='fa fa-trash fs-8'></i></button>";

                    return "<div class='d-flex align-items-center justify-content-center flex-nowrap'>" .
                        $btnPrint . $btnEditBarcode . $btnComplete . $btnDelete .
                        "</div>";
                })
                ->rawColumns(['checkbox', 'created_at', 'student_info', 'barcode', 'issue_type', 'reporter_info', 'status', 'action'])
                ->make(true);
        }

        // Summary counts
        $counts = [
            'total_pending' => StudentCardReport::pending()->count(),
            'rusak' => StudentCardReport::pending()->where('issue_type', 'rusak')->count(),
            'tidak_bisa_transaksi' => StudentCardReport::pending()->where('issue_type', 'tidak_bisa_transaksi')->count(),
            'hilang' => StudentCardReport::pending()->where('issue_type', 'hilang')->count(),
            'completed' => StudentCardReport::completed()->count(),
        ];

        $schools = School::hasSchool()->orderBy('name')->get();

        return view('admins.student-card-reports.index', compact('counts', 'schools'));
    }

    /**
     * Store bulk reports submitted by Koordinator Pondok Mart.
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|exists:students,id',
            'issue_type' => 'required|in:rusak,tidak_bisa_transaksi,hilang',
            'notes' => 'nullable|string|max:500',
        ], [
            'student_ids.required' => 'Pilih minimal satu santri yang ingin dilaporkan.',
            'issue_type.required' => 'Pilih jenis kendala kartu.',
            'issue_type.in' => 'Jenis kendala tidak valid.',
        ]);

        $studentIds = $request->input('student_ids');
        $issueType = $request->input('issue_type');
        $notes = $request->input('notes');
        $adminId = Auth::id();

        $savedCount = 0;
        foreach ($studentIds as $sId) {
            // Update existing pending report or create new
            StudentCardReport::create([
                'student_id' => $sId,
                'reported_by' => $adminId,
                'issue_type' => $issueType,
                'notes' => $notes,
                'status' => StudentCardReport::STATUS_PENDING,
            ]);
            $savedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil mengirim laporan kendala kartu untuk {$savedCount} santri ke Super Admin.",
            'count' => $savedCount,
        ]);
    }

    /**
     * Mark a single report as completed.
     */
    public function complete($id)
    {
        $report = StudentCardReport::findOrFail($id);
        $report->update([
            'status' => StudentCardReport::STATUS_COMPLETED,
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Laporan kartu untuk santri {$report->student?->name} ditandai selesai dicetak.",
        ]);
    }

    /**
     * Mark multiple reports as completed in bulk.
     */
    public function bulkComplete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|exists:student_card_reports,id',
        ]);

        $count = StudentCardReport::whereIn('id', $request->ids)
            ->where('status', '!=', StudentCardReport::STATUS_COMPLETED)
            ->update([
                'status' => StudentCardReport::STATUS_COMPLETED,
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => "{$count} laporan kartu berhasil ditandai selesai dicetak ulang.",
        ]);
    }

    /**
     * Remove the specified report.
     */
    public function destroy($id)
    {
        $report = StudentCardReport::findOrFail($id);
        $report->delete();

        return response()->json([
            'success' => true,
            'message' => 'Laporan kartu berhasil dihapus.',
        ]);
    }
}
