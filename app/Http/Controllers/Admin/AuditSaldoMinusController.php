<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AuditSaldoMinusController extends Controller
{
    /**
     * Resolve database connection to use for audit.
     * Checks if current database contains negative saldo; if not, connects to mysql_aplikasi.
     */
    private function getAuditConnection($source = null)
    {
        if ($source === 'local') {
            return DB::connection();
        }

        if ($source === 'aplikasidb') {
            try {
                DB::connection('mysql_aplikasi')->getPdo();
                return DB::connection('mysql_aplikasi');
            } catch (\Throwable $e) {
                return DB::connection();
            }
        }

        // Default auto-detect: check local first
        try {
            if (DB::table('students')->where('saldo', '<', 0)->exists()) {
                return DB::connection();
            }
        } catch (\Throwable $e) {
            // fallback
        }

        // Fallback to mysql_aplikasi where historical minus records are stored
        try {
            DB::connection('mysql_aplikasi')->getPdo();
            return DB::connection('mysql_aplikasi');
        } catch (\Throwable $e) {
            return DB::connection();
        }
    }

    /**
     * Tampilkan halaman utama Audit Saldo Minus
     */
    public function index(Request $request)
    {
        if (!Auth::user()->can('Manage Audit dan Sinkron')) {
            return redirect()->route('dashboard')->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $source = $request->get('source');
        $conn = $this->getAuditConnection($source);
        $connName = $conn->getName();

        // Ambil metrik ringkasan (cache 10 menit agar super cepat)
        $cacheKey = 'audit_saldo_minus_summary_' . $connName;
        if ($request->has('refresh')) {
            Cache::forget($cacheKey);
        }

        $summary = Cache::remember($cacheKey, 600, function () use ($conn) {
            $baseQuery = $conn->table('students')->where('saldo', '<', 0);
            $totalCount = (clone $baseQuery)->count();
            $totalDeficit = abs((clone $baseQuery)->sum('saldo') ?? 0);

            // Statistik per kelompok nominal minus
            $minusRingan = (clone $baseQuery)->where('saldo', '>=', -50000)->count();
            $minusSedang = (clone $baseQuery)->where('saldo', '<', -50000)->where('saldo', '>=', -200000)->count();
            $minusBerat  = (clone $baseQuery)->where('saldo', '<', -200000)->count();

            // Hitung estimasi santri yang saldonya terpotong tagihan SPP
            $studentIds = (clone $baseQuery)->pluck('id')->toArray();
            $sppVictimsCount = 0;
            if (!empty($studentIds)) {
                $sppVictimsCount = $conn->table('saldo_histories')
                    ->whereIn('student_id', $studentIds)
                    ->where('type', 'OUT')
                    ->where(function($q) {
                        $q->where('description', 'like', '%Tagihan%')
                          ->orWhere('description', 'like', '%SPP%')
                          ->orWhere('description', 'like', '%Sms%')
                          ->orWhere('description', 'like', '%Zarkasi%')
                          ->orWhere('description', 'like', '%Pembayaran%')
                          ->orWhere('description', 'like', '%Adjustment%');
                    })
                    ->distinct('student_id')
                    ->count('student_id');
            }

            return [
                'total_count' => $totalCount,
                'total_deficit' => $totalDeficit,
                'minus_ringan' => $minusRingan,
                'minus_sedang' => $minusSedang,
                'minus_berat' => $minusBerat,
                'spp_victims_count' => $sppVictimsCount,
                'pos_only_count' => max(0, $totalCount - $sppVictimsCount),
            ];
        });

        // Ambil daftar sekolah dan kelas untuk filter
        try {
            $schools = $conn->table('schools')->orderBy('name')->get(['id', 'name']);
            $classrooms = $conn->table('classrooms')->orderBy('name')->get(['id', 'name', 'school_id']);
        } catch (\Throwable $e) {
            $schools = collect([]);
            $classrooms = collect([]);
        }

        return view('admins.admin.audit.saldo-minus', compact(
            'summary',
            'schools',
            'classrooms',
            'connName'
        ));
    }

    /**
     * DataTables AJAX Server-side data provider
     */
    public function data(Request $request)
    {
        if (!Auth::user()->can('Manage Audit dan Sinkron')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $source = $request->get('source');
        $conn = $this->getAuditConnection($source);

        $query = $conn->table('students')
            ->leftJoin('classrooms', 'students.classroom_id', '=', 'classrooms.id')
            ->leftJoin('schools', 'classrooms.school_id', '=', 'schools.id')
            ->where('students.saldo', '<', 0);

        // Filter Lembaga
        if ($request->filled('school_id')) {
            $query->where('classrooms.school_id', $request->school_id);
        }

        // Filter Kelas
        if ($request->filled('classroom_id')) {
            $query->where('students.classroom_id', $request->classroom_id);
        }

        // Filter Kategori Minus
        if ($request->filled('minus_range')) {
            switch ($request->minus_range) {
                case 'ringan':
                    $query->where('students.saldo', '>=', -50000);
                    break;
                case 'sedang':
                    $query->where('students.saldo', '<', -50000)->where('students.saldo', '>=', -200000);
                    break;
                case 'berat':
                    $query->where('students.saldo', '<', -200000);
                    break;
            }
        }

        // Filter Pencarian
        if ($request->filled('search_keyword')) {
            $search = trim($request->search_keyword);
            $query->where(function ($q) use ($search) {
                $q->where('students.name', 'like', "%{$search}%")
                  ->orWhere('students.nis', 'like', "%{$search}%")
                  ->orWhere('classrooms.name', 'like', "%{$search}%")
                  ->orWhere('schools.name', 'like', "%{$search}%");
            });
        }

        $totalFiltered = (clone $query)->count();
        $totalRecords = $conn->table('students')->where('saldo', '<', 0)->count();

        // Sorting
        $orderColumn = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'asc');

        $columnsMap = [
            0 => 'students.saldo',
            1 => 'students.name',
            2 => 'schools.name',
            3 => 'classrooms.name',
            4 => 'students.saldo',
            5 => 'students.updated_at',
            6 => 'students.saldo',
            7 => 'students.saldo',
        ];

        $sortColumn = $columnsMap[$orderColumn] ?? 'students.saldo';
        $query->orderBy($sortColumn, $orderDir);

        // Pagination
        $start = (int)$request->input('start', 0);
        $length = (int)$request->input('length', 25);
        if ($length > 100) $length = 100;
        if ($length < 1) $length = 25;

        $students = $query->skip($start)->take($length)->get([
            'students.id',
            'students.nis',
            'students.name',
            'students.saldo',
            'students.status as student_status',
            'students.created_at',
            'students.updated_at',
            'classrooms.name as classroom_name',
            'schools.name as school_name'
        ]);

        $studentIds = $students->pluck('id')->toArray();

        // Batch query histories for root cause & last transaction date
        $histories = collect();
        if (!empty($studentIds)) {
            $histories = $conn->table('saldo_histories')
                ->whereIn('student_id', $studentIds)
                ->select('student_id', 'amount', 'type', 'description', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('student_id');
        }

        // Local check for current saldo
        $localStudents = DB::table('students')
            ->whereIn('id', $studentIds)
            ->get(['id', 'saldo'])
            ->keyBy('id');

        $rows = [];
        $no = $start + 1;

        foreach ($students as $s) {
            $studentHistories = $histories->get($s->id, collect());
            $lastMutation = $studentHistories->first();

            // Hitung total pemotongan tagihan vs jajan vs top up
            $totalIn = $studentHistories->where('type', 'IN')->sum('amount');
            $totalOut = $studentHistories->where('type', 'OUT')->sum('amount');

            $totalBillDeductions = $studentHistories
                ->where('type', 'OUT')
                ->filter(function ($item) {
                    $desc = strtolower($item->description ?? '');
                    return str_contains($desc, 'tagihan') ||
                           str_contains($desc, 'spp') ||
                           str_contains($desc, 'sms') ||
                           str_contains($desc, 'zarkasi') ||
                           str_contains($desc, 'pembayaran') ||
                           str_contains($desc, 'adjustment');
                })
                ->sum('amount');

            $totalPosDeductions = $studentHistories
                ->where('type', 'OUT')
                ->filter(function ($item) {
                    $desc = strtolower($item->description ?? '');
                    return !(str_contains($desc, 'tagihan') ||
                             str_contains($desc, 'spp') ||
                             str_contains($desc, 'sms') ||
                             str_contains($desc, 'zarkasi') ||
                             str_contains($desc, 'pembayaran') ||
                             str_contains($desc, 'adjustment'));
                })
                ->sum('amount');

            // Format tanggal transaksi terakhir
            $lastDate = '-';
            if ($lastMutation && !empty($lastMutation->created_at)) {
                try {
                    $lastDate = Carbon::parse($lastMutation->created_at)->translatedFormat('d M Y H:i');
                } catch (\Throwable $e) {
                    $lastDate = (string)$lastMutation->created_at;
                }
            }

            // Status siswa
            $statusBadge = '<span class="badge badge-light-success fs-8">Aktif</span>';
            if (!empty($s->student_status) && strtolower($s->student_status) !== 'aktif') {
                $statusBadge = '<span class="badge badge-light-secondary text-gray-700 fs-8">' . htmlspecialchars(ucfirst($s->student_status)) . '</span>';
            }

            // Kolom Nama & NIS
            $studentCol = '
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-35px symbol-circle me-3 bg-light-danger d-flex align-items-center justify-content-center text-danger fw-bolder fs-7">
                        ' . strtoupper(substr($s->name, 0, 1)) . '
                    </div>
                    <div class="d-flex flex-column">
                        <span class="text-gray-900 fw-bolder text-hover-primary fs-7 mb-0.5">' . htmlspecialchars($s->name) . '</span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted fs-8 font-mono">NIS: ' . htmlspecialchars($s->nis ?? '-') . '</span>
                            ' . $statusBadge . '
                        </div>
                    </div>
                </div>';

            // Kolom Riwayat Saldo (Total Masuk vs Total Keluar)
            $saldoStatusCol = '
                <div class="d-flex flex-column gap-1">
                    <span class="fs-8 text-success font-mono d-flex align-items-center">
                        <i class="fas fa-arrow-down fs-9 me-1.5 text-success"></i>Masuk: <strong>Rp ' . number_format($totalIn, 0, ',', '.') . '</strong>
                    </span>
                    <span class="fs-8 text-danger font-mono d-flex align-items-center">
                        <i class="fas fa-arrow-up fs-9 me-1.5 text-danger"></i>Keluar: <strong>Rp ' . number_format($totalOut, 0, ',', '.') . '</strong>
                    </span>
                </div>';

            // Kolom Minus Berapa
            $minusVal = abs($s->saldo);
            $minusCol = '
                <div class="d-flex flex-column text-end pe-2">
                    <span class="badge badge-danger fs-6 fw-bolder font-mono py-1.5 px-3 shadow-xs">
                        - Rp ' . number_format($minusVal, 0, ',', '.') . '
                    </span>
                    <span class="text-muted fs-9 mt-1">Defisit Mutasi</span>
                </div>';

            // Kolom Log Kenapa Bisa Minus
            $sppPercent = $totalOut > 0 ? round(($totalBillDeductions / $totalOut) * 100) : 0;
            $posPercent = $totalOut > 0 ? round(($totalPosDeductions / $totalOut) * 100) : 0;

            $rootCauseBadges = '';
            if ($totalBillDeductions > 0) {
                $rootCauseBadges .= '
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="badge badge-light-danger fw-bolder fs-8">
                            <i class="fas fa-file-invoice-dollar text-danger me-1"></i> SPP: Rp ' . number_format($totalBillDeductions, 0, ',', '.') . '
                        </span>
                        <span class="text-muted fs-9 font-mono ms-1">(' . $sppPercent . '%)</span>
                    </div>';
            }
            if ($totalPosDeductions > 0) {
                $rootCauseBadges .= '
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="badge badge-light-warning fw-bolder fs-8">
                            <i class="fas fa-shopping-basket text-warning me-1"></i> Jajan: Rp ' . number_format($totalPosDeductions, 0, ',', '.') . '
                        </span>
                        <span class="text-muted fs-9 font-mono ms-1">(' . $posPercent . '%)</span>
                    </div>';
            }

            if (empty($rootCauseBadges)) {
                $rootCauseBadges = '<span class="text-muted fs-8 fst-italic">Pengeluaran melebihi uang masuk</span>';
            }

            $logCol = '
                <div class="d-flex flex-column py-1">
                    ' . $rootCauseBadges . '
                </div>';

            // Tombol Aksi
            $actionBtn = '
                <button type="button" 
                        class="btn btn-sm btn-primary fw-bolder fs-8 d-flex align-items-center py-2 px-3 btn-view-logs shadow-xs" 
                        data-id="' . htmlspecialchars($s->id) . '" 
                        data-name="' . htmlspecialchars($s->name) . '" 
                        data-nis="' . htmlspecialchars($s->nis ?? '-') . '" 
                        data-school="' . htmlspecialchars($s->school_name ?? '-') . '" 
                        data-class="' . htmlspecialchars($s->classroom_name ?? '-') . '" 
                        data-saldo="' . htmlspecialchars($s->saldo) . '">
                    <i class="fas fa-columns me-1.5 fs-7"></i> Detail Log
                </button>';

            $rows[] = [
                'no' => $no++,
                'student' => $studentCol,
                'school' => htmlspecialchars($s->school_name ?? '-'),
                'classroom' => htmlspecialchars($s->classroom_name ?? '-'),
                'saldo_status' => $saldoStatusCol,
                'last_trans_date' => $lastDate,
                'minus_amount' => $minusCol,
                'root_cause' => $logCol,
                'action' => $actionBtn,
            ];
        }

        return response()->json([
            'draw' => (int)$request->input('draw', 1),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $rows
        ]);
    }

    /**
     * Endpoint untuk memuat detail log kronologis mutasi saldo santri
     */
    public function logs(Request $request, $id)
    {
        if (!Auth::user()->can('Manage Audit dan Sinkron')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $source = $request->get('source');
        $conn = $this->getAuditConnection($source);

        $student = $conn->table('students')
            ->leftJoin('classrooms', 'students.classroom_id', '=', 'classrooms.id')
            ->leftJoin('schools', 'classrooms.school_id', '=', 'schools.id')
            ->where('students.id', $id)
            ->first([
                'students.id',
                'students.nis',
                'students.name',
                'students.saldo',
                'students.status as student_status',
                'classrooms.name as classroom_name',
                'schools.name as school_name'
            ]);

        if (!$student) {
            return response()->json(['error' => 'Data santri tidak ditemukan.'], 404);
        }

        $histories = $conn->table('saldo_histories')
            ->where('student_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        $running = 0;
        $totalIn = 0;
        $totalOut = 0;
        $totalBillDeductions = 0;
        $totalPosDeductions = 0;

        $itemsTopup = [];
        $itemsSpp = [];
        $itemsPos = [];
        $timeline = [];
        $firstNegativeEvent = null;

        foreach ($histories as $idx => $item) {
            $prev = $running;
            $amt = (int)$item->amount;
            $desc = $item->description ?? '-';
            $descLower = strtolower($desc);

            try {
                $dateFormatted = Carbon::parse($item->created_at)->translatedFormat('d M Y H:i:s');
            } catch (\Throwable $e) {
                $dateFormatted = (string)$item->created_at;
            }

            if ($item->type === 'IN') {
                $running += $amt;
                $totalIn += $amt;
                $category = 'topup';

                $itemsTopup[] = [
                    'index' => count($itemsTopup) + 1,
                    'created_at' => $dateFormatted,
                    'raw_date' => $item->created_at,
                    'amount' => $amt,
                    'amount_formatted' => '+ Rp ' . number_format($amt, 0, ',', '.'),
                    'description' => $desc,
                    'balance_after' => $running,
                    'balance_after_formatted' => ($running < 0 ? '- Rp ' : 'Rp ') . number_format(abs($running), 0, ',', '.')
                ];
            } else {
                $running -= $amt;
                $totalOut += $amt;

                if (str_contains($descLower, 'tagihan') || str_contains($descLower, 'spp') || str_contains($descLower, 'sms') || str_contains($descLower, 'zarkasi') || str_contains($descLower, 'pembayaran') || str_contains($descLower, 'adjustment')) {
                    $totalBillDeductions += $amt;
                    $category = 'spp';

                    $itemsSpp[] = [
                        'index' => count($itemsSpp) + 1,
                        'created_at' => $dateFormatted,
                        'raw_date' => $item->created_at,
                        'amount' => $amt,
                        'amount_formatted' => '- Rp ' . number_format($amt, 0, ',', '.'),
                        'description' => $desc,
                        'balance_after' => $running,
                        'balance_after_formatted' => ($running < 0 ? '- Rp ' : 'Rp ') . number_format(abs($running), 0, ',', '.'),
                        'is_negative' => $running < 0
                    ];
                } else {
                    $totalPosDeductions += $amt;
                    $category = 'pos';

                    $itemsPos[] = [
                        'index' => count($itemsPos) + 1,
                        'created_at' => $dateFormatted,
                        'raw_date' => $item->created_at,
                        'amount' => $amt,
                        'amount_formatted' => '- Rp ' . number_format($amt, 0, ',', '.'),
                        'description' => $desc,
                        'balance_after' => $running,
                        'balance_after_formatted' => ($running < 0 ? '- Rp ' : 'Rp ') . number_format(abs($running), 0, ',', '.'),
                        'is_negative' => $running < 0
                    ];
                }
            }

            $isFirstNegative = false;
            if ($running < 0 && $prev >= 0 && !$firstNegativeEvent) {
                $isFirstNegative = true;
                $firstNegativeEvent = [
                    'date' => $dateFormatted,
                    'raw_date' => $item->created_at,
                    'prev' => $prev,
                    'prev_formatted' => 'Rp ' . number_format($prev, 0, ',', '.'),
                    'amount' => $amt,
                    'amount_formatted' => 'Rp ' . number_format($amt, 0, ',', '.'),
                    'desc' => $desc,
                    'after' => $running,
                    'after_formatted' => '- Rp ' . number_format(abs($running), 0, ',', '.'),
                    'category' => $category
                ];
            }

            $timeline[] = [
                'index' => $idx + 1,
                'created_at' => $dateFormatted,
                'raw_date' => $item->created_at,
                'type' => $item->type,
                'category' => $category,
                'amount' => $amt,
                'amount_formatted' => ($item->type === 'IN' ? '+ Rp ' : '- Rp ') . number_format($amt, 0, ',', '.'),
                'description' => $desc,
                'prev_balance' => $prev,
                'prev_balance_formatted' => ($prev < 0 ? '- Rp ' : 'Rp ') . number_format(abs($prev), 0, ',', '.'),
                'balance_after' => $running,
                'balance_after_formatted' => ($running < 0 ? '- Rp ' : 'Rp ') . number_format(abs($running), 0, ',', '.'),
                'is_negative' => $running < 0,
                'is_first_negative' => $isFirstNegative
            ];
        }

        $deficit = $totalOut - $totalIn;
        $sppPercent = $totalOut > 0 ? round(($totalBillDeductions / $totalOut) * 100, 1) : 0;
        $posPercent = $totalOut > 0 ? round(($totalPosDeductions / $totalOut) * 100, 1) : 0;

        // Terbaru di atas untuk tiap kolom
        $itemsTopupDesc = array_reverse($itemsTopup);
        $itemsSppDesc = array_reverse($itemsSpp);
        $itemsPosDesc = array_reverse($itemsPos);
        $timelineDesc = array_reverse($timeline);

        return response()->json([
            'status' => 'success',
            'student' => $student,
            'summary' => [
                'final_saldo' => (int)$student->saldo,
                'final_saldo_formatted' => '- Rp ' . number_format(abs((int)$student->saldo), 0, ',', '.'),
                'calculated_saldo' => $running,
                'calculated_saldo_formatted' => ($running < 0 ? '- Rp ' : 'Rp ') . number_format(abs($running), 0, ',', '.'),
                'total_in' => $totalIn,
                'total_in_formatted' => 'Rp ' . number_format($totalIn, 0, ',', '.'),
                'total_out' => $totalOut,
                'total_out_formatted' => 'Rp ' . number_format($totalOut, 0, ',', '.'),
                'total_bill_deductions' => $totalBillDeductions,
                'total_bill_formatted' => 'Rp ' . number_format($totalBillDeductions, 0, ',', '.'),
                'total_pos_deductions' => $totalPosDeductions,
                'total_pos_formatted' => 'Rp ' . number_format($totalPosDeductions, 0, ',', '.'),
                'deficit' => $deficit,
                'deficit_formatted' => 'Rp ' . number_format($deficit, 0, ',', '.'),
                'spp_percent' => $sppPercent,
                'pos_percent' => $posPercent,
                'topup_count' => count($itemsTopup),
                'spp_count' => count($itemsSpp),
                'pos_count' => count($itemsPos),
                'total_events' => count($histories),
                'first_negative_event' => $firstNegativeEvent,
                'diagnosis' => $totalBillDeductions > $totalPosDeductions
                    ? "Saldo minus terutama akibat pemotongan tagihan SPP bulanan (Rp " . number_format($totalBillDeductions, 0, ',', '.') . " / {$sppPercent}%) yang menyerap dana melebihi top up masuk."
                    : "Saldo minus didominasi transaksi belanja kantin/kasir POS (Rp " . number_format($totalPosDeductions, 0, ',', '.') . " / {$posPercent}%) saat saldo tidak mencukupi."
            ],
            'items_topup' => $itemsTopupDesc,
            'items_spp' => $itemsSppDesc,
            'items_pos' => $itemsPosDesc,
            'timeline' => $timelineDesc
        ]);
    }

    /**
     * Export daftar saldo minus ke CSV / TSV
     */
    public function export(Request $request)
    {
        if (!Auth::user()->can('Manage Audit dan Sinkron')) {
            abort(403);
        }

        $source = $request->get('source');
        $conn = $this->getAuditConnection($source);

        $students = $conn->table('students')
            ->leftJoin('classrooms', 'students.classroom_id', '=', 'classrooms.id')
            ->leftJoin('schools', 'classrooms.school_id', '=', 'schools.id')
            ->where('students.saldo', '<', 0)
            ->orderBy('students.saldo', 'asc')
            ->get([
                'students.id',
                'students.nis',
                'students.name',
                'students.saldo',
                'students.status as student_status',
                'classrooms.name as classroom_name',
                'schools.name as school_name'
            ]);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="audit_saldo_minus_santri_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($students, $conn) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'No',
                'NIS',
                'Nama Siswa',
                'Lembaga',
                'Kelas',
                'Status Siswa',
                'Saldo Minus (Rp)',
                'Total Potong Tagihan SPP (Rp)',
                'Total Jajan Kasir POS (Rp)',
                'Akar Penyebab Utama'
            ]);

            $no = 1;
            foreach ($students as $s) {
                $histories = $conn->table('saldo_histories')
                    ->where('student_id', $s->id)
                    ->where('type', 'OUT')
                    ->get(['amount', 'description']);

                $bills = 0;
                $jajan = 0;
                foreach ($histories as $h) {
                    $desc = strtolower($h->description ?? '');
                    if (str_contains($desc, 'tagihan') || str_contains($desc, 'spp') || str_contains($desc, 'sms') || str_contains($desc, 'zarkasi') || str_contains($desc, 'pembayaran') || str_contains($desc, 'adjustment')) {
                        $bills += (int)$h->amount;
                    } else {
                        $jajan += (int)$h->amount;
                    }
                }

                $mainCause = ($bills > 0)
                    ? 'Autodebit Tagihan SPP saat saldo tidak mencukupi (Rp ' . number_format($bills, 0, ',', '.') . ')'
                    : 'Akumulasi Belanja Jajan Kasir PoS (Rp ' . number_format($jajan, 0, ',', '.') . ')';

                fputcsv($handle, [
                    $no++,
                    $s->nis ?? '-',
                    $s->name,
                    $s->school_name ?? '-',
                    $s->classroom_name ?? '-',
                    ucfirst($s->student_status ?? 'Aktif'),
                    $s->saldo,
                    $bills,
                    $jajan,
                    $mainCause
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
