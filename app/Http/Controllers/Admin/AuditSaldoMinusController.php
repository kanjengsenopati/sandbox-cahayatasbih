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
     * Klasifikasikan jenis mutasi transaksi:
     * - 'topup': Pemasukan (type == 'IN')
     * - 'spp': Pemotongan Tagihan SPP / Bulanan Pesantren murni
     * - 'pos': Transaksi Belanja Jajan Kasir PoS / Keranjang Belanja
     * - 'adjustment': Penyesuaian saldo sistem lama
     */
    public function categorizeMutation($type, $description)
    {
        if ($type === 'IN') {
            return 'topup';
        }

        $descLower = strtolower($description ?? '');

        // 1. Kasir (PoS) Jajan / Keranjang Belanja
        if (str_contains($descLower, 'pembelian barang') ||
            str_contains($descLower, 'barang') ||
            str_contains($descLower, 'kantin') ||
            str_contains($descLower, 'pos') ||
            str_contains($descLower, 'jajan') ||
            str_contains($descLower, 'tarik cash') ||
            str_contains($descLower, 'tarik tunai')) {
            return 'pos';
        }

        // 2. Tagihan SPP Bulanan
        if (str_contains($descLower, 'tagihan') ||
            str_contains($descLower, 'spp') ||
            str_contains($descLower, 'sms') ||
            str_contains($descLower, 'zarkasi')) {
            return 'spp';
        }

        // 3. Adjustment Sistem
        if (str_contains($descLower, 'adjustment') ||
            str_contains($descLower, 'sinkronisasi master') ||
            str_contains($descLower, 'selisih saldo')) {
            return 'adjustment';
        }

        // Default pengeluaran kasir belanja jika tidak termasuk tagihan/adjustment
        return 'pos';
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

            // Hitung santri yang saldonya terpotong tagihan SPP bulanan (HANYA tagihan/spp murni, BUKAN pembelian barang kasir)
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
                          ->orWhere('description', 'like', '%Zarkasi%');
                    })
                    ->where('description', 'not like', '%Pembelian Barang%')
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

        // Batch query histories for root cause & first negative date (ascending chronological)
        $histories = collect();
        if (!empty($studentIds)) {
            $histories = $conn->table('saldo_histories')
                ->whereIn('student_id', $studentIds)
                ->select('id', 'student_id', 'amount', 'type', 'description', 'created_at')
                ->orderBy('created_at', 'asc')
                ->get()
                ->groupBy('student_id');
        }

        // Local check for current saldo
        $localStudents = DB::table('students')
            ->whereIn('id', $studentIds)
            ->get(['id', 'saldo'])
            ->keyBy('id');

        // First pass: identify origin event and collect origin history IDs for batch officer & detail lookup
        $studentOrigins = [];
        $originHistoryIds = [];
        $studentCalculations = [];

        foreach ($students as $s) {
            $studentHistories = $histories->get($s->id, collect());
            $lastMutation = $studentHistories->last();

            $running = 0;
            $totalIn = 0;
            $totalOut = 0;
            $firstEverMinus = null;
            $streakMinus = null;

            foreach ($studentHistories as $item) {
                $prev = $running;
                $amt = (int)$item->amount;
                $cat = $this->categorizeMutation($item->type, $item->description ?? '');

                if ($item->type === 'IN') {
                    $running += $amt;
                    $totalIn += $amt;
                } else {
                    $running -= $amt;
                    $totalOut += $amt;
                }

                if ($running < 0 && $prev >= 0) {
                    $ev = [
                        'id' => $item->id,
                        'date' => Carbon::parse($item->created_at)->translatedFormat('d M Y H:i'),
                        'raw_date' => $item->created_at,
                        'amount' => $amt,
                        'amount_formatted' => 'Rp ' . number_format($amt, 0, ',', '.'),
                        'description' => $item->description ?? '-',
                        'category' => $cat,
                        'prev' => $prev,
                        'prev_formatted' => ($prev < 0 ? '- Rp ' : 'Rp ') . number_format(abs($prev), 0, ',', '.'),
                        'after' => $running,
                        'after_formatted' => '- Rp ' . number_format(abs($running), 0, ',', '.')
                    ];
                    $streakMinus = $ev;
                    if (!$firstEverMinus) {
                        $firstEverMinus = $ev;
                    }
                } elseif ($running >= 0) {
                    $streakMinus = null;
                }
            }

            $originEvent = $firstEverMinus ?? $streakMinus;
            $studentOrigins[$s->id] = $originEvent;
            $studentCalculations[$s->id] = [
                'total_in' => $totalIn,
                'total_out' => $totalOut,
                'last_mutation' => $lastMutation
            ];

            if ($originEvent && !empty($originEvent['id'])) {
                $originHistoryIds[] = $originEvent['id'];
            }
        }

        // Batch preload petugas & nama spesifik tagihan untuk origin events
        $billOriginMap = [];
        $posOriginMap = [];
        if (!empty($originHistoryIds)) {
            try {
                $billRows = $conn->table('transaction_details')
                    ->leftJoin('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                    ->leftJoin('admins', 'transactions.admin_id', '=', 'admins.id')
                    ->leftJoin('bills', 'transaction_details.bill_id', '=', 'bills.id')
                    ->leftJoin('bill_types', 'bills.bill_type_id', '=', 'bill_types.id')
                    ->whereIn('transaction_details.saldo_history_id', $originHistoryIds)
                    ->select([
                        'transaction_details.saldo_history_id',
                        'transactions.payment_code',
                        'admins.name as admin_name',
                        'bills.month',
                        'bills.year',
                        'bill_types.name as bill_type_name'
                    ])
                    ->get()
                    ->groupBy('saldo_history_id');

                foreach ($billRows as $shId => $bGroup) {
                    $firstB = $bGroup->first();
                    $adminName = $firstB->admin_name ?: 'Petugas Keuangan';
                    $names = [];
                    foreach ($bGroup as $bg) {
                        if ($bg->bill_type_name) {
                            $period = ($bg->month && $bg->year) ? " (Bulan {$bg->month}/{$bg->year})" : '';
                            $names[] = $bg->bill_type_name . $period;
                        }
                    }
                    $billName = !empty($names) ? implode(', ', array_unique($names)) : 'SPP Bulanan Pesantren';

                    $billOriginMap[$shId] = [
                        'admin_name' => $adminName,
                        'payment_code' => $firstB->payment_code,
                        'bill_name' => $billName
                    ];
                }
            } catch (\Throwable $e) {}

            try {
                $posRows = $conn->table('point_of_sale_transactions')
                    ->leftJoin('admins', 'point_of_sale_transactions.admin_id', '=', 'admins.id')
                    ->whereIn('point_of_sale_transactions.saldo_history_id', $originHistoryIds)
                    ->select([
                        'point_of_sale_transactions.saldo_history_id',
                        'point_of_sale_transactions.payment_code',
                        'admins.name as cashier_name'
                    ])
                    ->get()
                    ->keyBy('saldo_history_id');

                foreach ($posRows as $shId => $pd) {
                    $posOriginMap[$shId] = [
                        'cashier_name' => $pd->cashier_name ?: 'Petugas Kasir',
                        'payment_code' => $pd->payment_code
                    ];
                }
            } catch (\Throwable $e) {}
        }

        $rows = [];
        $no = $start + 1;

        foreach ($students as $s) {
            $originEvent = $studentOrigins[$s->id] ?? null;
            $calc = $studentCalculations[$s->id] ?? ['total_in' => 0, 'total_out' => 0, 'last_mutation' => null];
            $totalIn = $calc['total_in'];
            $totalOut = $calc['total_out'];
            $lastMutation = $calc['last_mutation'];

            // Format tanggal titik awal mulai minus (Timestamp)
            $originDateCol = '-';
            if ($originEvent) {
                $rawDt = !empty($originEvent['raw_date']) ? Carbon::parse($originEvent['raw_date']) : Carbon::parse($originEvent['date']);
                $datePart = $rawDt->translatedFormat('d M Y');
                $timePart = $rawDt->format('H:i');

                $originDateCol = '
                    <div class="d-flex flex-column text-nowrap">
                        <div class="text-gray-900 fw-bold fs-8 mb-0.5">
                            <i class="far fa-calendar-alt text-danger me-1 fs-9"></i>' . $datePart . '
                            <span class="text-muted font-mono fs-9 ms-1">' . $timePart . '</span>
                        </div>
                        <div class="text-danger font-mono fs-9 fw-semibold">
                            ' . $originEvent['prev_formatted'] . ' &rarr; <span class="fw-bolder">' . $originEvent['after_formatted'] . '</span>
                        </div>
                    </div>';
            } elseif ($lastMutation) {
                $originDateCol = '<span class="text-muted font-mono fs-8 text-nowrap">' . Carbon::parse($lastMutation->created_at)->translatedFormat('d M Y H:i') . '</span>';
            }

            // Status siswa
            $statusBadge = '<span class="badge badge-light-success text-success fs-9 py-0.5 px-1.5 fw-bold">Aktif</span>';
            if (!empty($s->student_status) && strtolower($s->student_status) !== 'aktif') {
                $statusBadge = '<span class="badge badge-light-secondary text-gray-700 fs-9 py-0.5 px-1.5 fw-bold">' . htmlspecialchars(ucfirst($s->student_status)) . '</span>';
            }

            // Kolom Nama & NIS
            $studentCol = '
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-30px symbol-circle me-2.5 bg-light-danger text-danger fw-bolder fs-8 d-flex align-items-center justify-content-center flex-shrink-0">
                        ' . strtoupper(substr($s->name, 0, 1)) . '
                    </div>
                    <div class="d-flex flex-column" style="min-width: 0;">
                        <span class="text-gray-900 fw-bold fs-7 mb-0.5 text-hover-primary text-truncate" title="' . htmlspecialchars($s->name) . '">' . htmlspecialchars($s->name) . '</span>
                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                            <span class="text-muted fs-8 font-mono">NIS: ' . htmlspecialchars($s->nis ?? '-') . '</span>
                            ' . $statusBadge . '
                        </div>
                    </div>
                </div>';

            // Kolom Riwayat Saldo (Total Masuk vs Total Keluar)
            $saldoStatusCol = '
                <div class="d-flex flex-column gap-1 text-nowrap">
                    <div class="d-flex align-items-center justify-content-between text-success fs-8">
                        <span class="fs-9"><i class="fas fa-arrow-down me-1 text-success fs-9"></i>Masuk</span>
                        <span class="font-mono fw-bold ms-2">Rp ' . number_format($totalIn, 0, ',', '.') . '</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between text-danger fs-8">
                        <span class="fs-9"><i class="fas fa-arrow-up me-1 text-danger fs-9"></i>Keluar</span>
                        <span class="font-mono fw-bold ms-2">Rp ' . number_format($totalOut, 0, ',', '.') . '</span>
                    </div>
                </div>';

            // Kolom Minus Berapa
            $minusVal = abs($s->saldo);
            $minusCol = '
                <div class="d-flex flex-column align-items-end text-nowrap">
                    <span class="badge badge-light-danger text-danger fs-7 fw-bolder font-mono py-1 px-2.5">
                        - Rp ' . number_format($minusVal, 0, ',', '.') . '
                    </span>
                    <span class="text-muted fs-9 mt-0.5">Defisit Saldo</span>
                </div>';

            // Kolom Disebabkan Oleh Apa & Berapa Nominalnya
            $logCol = '';
            if ($originEvent) {
                $officerName = null;
                $friendlyTitle = $originEvent['description'];

                if ($originEvent['category'] === 'spp') {
                    $badgeClass = 'badge-light-danger text-danger border border-danger border-opacity-30';
                    $badgeIcon = '<i class="fas fa-file-invoice-dollar text-danger me-1"></i> Potong Tagihan';
                    if (isset($billOriginMap[$originEvent['id']])) {
                        $friendlyTitle = $billOriginMap[$originEvent['id']]['bill_name'];
                        $officerName = $billOriginMap[$originEvent['id']]['admin_name'];
                    } else {
                        $friendlyTitle = 'Potong Tagihan';
                    }
                } elseif ($originEvent['category'] === 'adjustment') {
                    $badgeClass = 'badge-light-secondary text-gray-700 border border-gray-300';
                    $badgeIcon = '<i class="fas fa-tools text-gray-500 me-1"></i> Penyesuaian';
                    $friendlyTitle = 'Penyesuaian Saldo Sistem';
                } else {
                    $badgeClass = 'badge-light-warning text-dark border border-warning border-opacity-30';
                    $badgeIcon = '<i class="fas fa-shopping-basket text-warning me-1"></i> Kasir PoS';
                    if (stripos($originEvent['description'], 'pembelian barang') !== false) {
                        $friendlyTitle = 'Belanja Kasir (PoS Keranjang)';
                    } elseif (stripos($originEvent['description'], 'tarik cash') !== false) {
                        $friendlyTitle = 'Tarik Kasbon Tunai Kasir';
                    }
                    if (isset($posOriginMap[$originEvent['id']])) {
                        $officerName = $posOriginMap[$originEvent['id']]['cashier_name'];
                    } elseif (isset($billOriginMap[$originEvent['id']])) {
                        $officerName = $billOriginMap[$originEvent['id']]['admin_name'];
                    }
                }

                $rawDesc = ($friendlyTitle !== $originEvent['description']) 
                    ? '<span class="text-muted fs-9 text-truncate mt-0.5" style="max-width: 220px;" title="' . htmlspecialchars($originEvent['description']) . '"><i class="fas fa-receipt me-1 text-gray-400"></i>' . htmlspecialchars($originEvent['description']) . '</span>' 
                    : '';

                $officerHtml = '';
                if (!empty($officerName)) {
                    $officerHtml = '<div class="text-primary fs-9 fw-semibold mt-0.5 text-truncate" style="max-width: 220px;" title="' . htmlspecialchars($officerName) . '"><i class="fas fa-user-check me-1"></i>Petugas: ' . htmlspecialchars($officerName) . '</div>';
                }

                $logCol = '
                    <div class="d-flex flex-column py-0.5" style="max-width: 230px;">
                        <div class="d-flex align-items-center gap-1 mb-0.5 flex-wrap">
                            <span class="badge ' . $badgeClass . ' fw-bold fs-9 py-0.5 px-1.5">' . $badgeIcon . '</span>
                            <span class="badge badge-light-danger text-danger fw-bold font-mono fs-9 py-0.5 px-1.5">' . $originEvent['amount_formatted'] . '</span>
                        </div>
                        <span class="text-gray-900 fs-8 fw-bold text-truncate" style="max-width: 220px;" title="' . htmlspecialchars($friendlyTitle) . '">
                            ' . htmlspecialchars($friendlyTitle) . '
                        </span>
                        ' . $officerHtml . '
                        ' . $rawDesc . '
                    </div>';
            } else {
                $logCol = '<span class="text-muted fs-8 fst-italic">Pengeluaran kasir melebihi saldo</span>';
            }

            // Tombol Aksi
            $actionBtn = '
                <button type="button" 
                        class="btn btn-sm btn-primary fw-bolder fs-8 py-1.5 px-2.5 btn-view-logs text-nowrap" 
                        data-id="' . htmlspecialchars($s->id) . '" 
                        data-name="' . htmlspecialchars($s->name) . '" 
                        data-nis="' . htmlspecialchars($s->nis ?? '-') . '" 
                        data-school="' . htmlspecialchars($s->school_name ?? '-') . '" 
                        data-class="' . htmlspecialchars($s->classroom_name ?? '-') . '" 
                        data-saldo="' . htmlspecialchars($s->saldo) . '">
                    <i class="fas fa-search me-1 fs-9"></i> Detail Log
                </button>';

            $rows[] = [
                'no' => $no++,
                'student' => $studentCol,
                'school' => '<span class="fw-bold text-gray-800 fs-7 text-nowrap">' . htmlspecialchars($s->school_name ?? '-') . '</span>',
                'classroom' => '<span class="badge badge-light-primary text-primary fw-bolder fs-8 px-2 py-0.5 text-nowrap">' . htmlspecialchars($s->classroom_name ?? '-') . '</span>',
                'saldo_status' => $saldoStatusCol,
                'last_trans_date' => $originDateCol,
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

        $historyIds = $histories->pluck('id')->toArray();

        // 1. Preload rincian nama tagihan & admin/petugas pemroses dari tabel transaction_details & bills
        $billDetailsMap = [];
        if (!empty($historyIds)) {
            try {
                $billRows = $conn->table('transaction_details')
                    ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                    ->leftJoin('admins', 'transactions.admin_id', '=', 'admins.id')
                    ->leftJoin('bills', 'transaction_details.bill_id', '=', 'bills.id')
                    ->leftJoin('bill_types', 'bills.bill_type_id', '=', 'bill_types.id')
                    ->whereIn('transaction_details.saldo_history_id', $historyIds)
                    ->select([
                        'transaction_details.saldo_history_id',
                        'transactions.payment_code',
                        'transactions.admin_id',
                        'admins.name as admin_name',
                        'bills.month',
                        'bills.year',
                        'bill_types.name as bill_type_name'
                    ])
                    ->get()
                    ->groupBy('saldo_history_id');

                foreach ($billRows as $shId => $bGroup) {
                    $firstB = $bGroup->first();
                    $adminName = $firstB->admin_name ?: 'Petugas Keuangan';
                    $names = [];
                    foreach ($bGroup as $bg) {
                        if ($bg->bill_type_name) {
                            $period = ($bg->month && $bg->year) ? " (Bulan {$bg->month}/{$bg->year})" : '';
                            $names[] = $bg->bill_type_name . $period;
                        }
                    }
                    $billName = !empty($names) ? implode(', ', array_unique($names)) : 'SPP Bulanan Pesantren';

                    $billDetailsMap[$shId] = [
                        'admin_name' => $adminName,
                        'payment_code' => $firstB->payment_code,
                        'bill_name' => $billName
                    ];
                }
            } catch (\Throwable $e) {
                // fallback
            }
        }

        // 2. Preload rincian kasir & nama petugas kasir dari tabel point_of_sale_transactions
        $posDetailsMap = [];
        if (!empty($historyIds)) {
            try {
                $posRows = $conn->table('point_of_sale_transactions')
                    ->leftJoin('admins', 'point_of_sale_transactions.admin_id', '=', 'admins.id')
                    ->whereIn('point_of_sale_transactions.saldo_history_id', $historyIds)
                    ->select([
                        'point_of_sale_transactions.saldo_history_id',
                        'point_of_sale_transactions.payment_code',
                        'admins.name as cashier_name'
                    ])
                    ->get()
                    ->keyBy('saldo_history_id');

                foreach ($posRows as $shId => $pd) {
                    $posDetailsMap[$shId] = [
                        'cashier_name' => $pd->cashier_name ?: 'Petugas Kasir',
                        'payment_code' => $pd->payment_code
                    ];
                }
            } catch (\Throwable $e) {
                // fallback
            }
        }

        $running = 0;
        $totalIn = 0;
        $totalOut = 0;
        $totalBillDeductions = 0;
        $totalPosDeductions = 0;
        $totalAdjustments = 0;

        $itemsTopup = [];
        $itemsSpp = [];
        $itemsPos = [];
        $timeline = [];
        $firstNegativeEvent = null;

        foreach ($histories as $idx => $item) {
            $prev = $running;
            $amt = (int)$item->amount;
            $desc = $item->description ?? '-';

            try {
                $dateFormatted = Carbon::parse($item->created_at)->translatedFormat('d M Y H:i:s');
            } catch (\Throwable $e) {
                $dateFormatted = (string)$item->created_at;
            }

            $category = $this->categorizeMutation($item->type, $desc);
            $officerName = null;
            $paymentCode = null;

            // Friendly description & officer mapping
            $friendlyDesc = $desc;
            if ($category === 'spp') {
                if (isset($billDetailsMap[$item->id])) {
                    $friendlyDesc = $billDetailsMap[$item->id]['bill_name'];
                    $officerName = $billDetailsMap[$item->id]['admin_name'];
                    $paymentCode = $billDetailsMap[$item->id]['payment_code'];
                } else {
                    $friendlyDesc = 'SPP Bulanan Pesantren';
                }
            } elseif ($category === 'pos') {
                if (stripos($desc, 'pembelian barang') !== false) {
                    $friendlyDesc = 'Belanja Kasir (PoS Keranjang)';
                } elseif (stripos($desc, 'tarik cash') !== false) {
                    $friendlyDesc = 'Tarik Kasbon Tunai Kasir';
                }

                if (isset($posDetailsMap[$item->id])) {
                    $officerName = $posDetailsMap[$item->id]['cashier_name'];
                    $paymentCode = $posDetailsMap[$item->id]['payment_code'];
                }
            } elseif ($category === 'adjustment') {
                $friendlyDesc = 'Penyesuaian Saldo Sistem Lama';
            }

            if ($item->type === 'IN') {
                $running += $amt;
                $totalIn += $amt;

                $itemsTopup[] = [
                    'index' => count($itemsTopup) + 1,
                    'created_at' => $dateFormatted,
                    'raw_date' => $item->created_at,
                    'amount' => $amt,
                    'amount_formatted' => '+ Rp ' . number_format($amt, 0, ',', '.'),
                    'description' => $desc,
                    'friendly_desc' => $friendlyDesc,
                    'officer_name' => $officerName,
                    'payment_code' => $paymentCode,
                    'balance_after' => $running,
                    'balance_after_formatted' => ($running < 0 ? '- Rp ' : 'Rp ') . number_format(abs($running), 0, ',', '.')
                ];
            } else {
                $running -= $amt;
                $totalOut += $amt;

                if ($category === 'spp') {
                    $totalBillDeductions += $amt;

                    $itemsSpp[] = [
                        'index' => count($itemsSpp) + 1,
                        'created_at' => $dateFormatted,
                        'raw_date' => $item->created_at,
                        'amount' => $amt,
                        'amount_formatted' => '- Rp ' . number_format($amt, 0, ',', '.'),
                        'description' => $desc,
                        'friendly_desc' => $friendlyDesc,
                        'officer_name' => $officerName,
                        'payment_code' => $paymentCode,
                        'balance_after' => $running,
                        'balance_after_formatted' => ($running < 0 ? '- Rp ' : 'Rp ') . number_format(abs($running), 0, ',', '.'),
                        'is_negative' => $running < 0
                    ];
                } elseif ($category === 'adjustment') {
                    $totalAdjustments += $amt;

                    $itemsPos[] = [
                        'index' => count($itemsPos) + 1,
                        'created_at' => $dateFormatted,
                        'raw_date' => $item->created_at,
                        'amount' => $amt,
                        'amount_formatted' => '- Rp ' . number_format($amt, 0, ',', '.'),
                        'description' => $desc,
                        'friendly_desc' => $friendlyDesc,
                        'officer_name' => $officerName,
                        'payment_code' => $paymentCode,
                        'balance_after' => $running,
                        'balance_after_formatted' => ($running < 0 ? '- Rp ' : 'Rp ') . number_format(abs($running), 0, ',', '.'),
                        'is_negative' => $running < 0,
                        'is_adjustment' => true
                    ];
                } else {
                    $totalPosDeductions += $amt;

                    $itemsPos[] = [
                        'index' => count($itemsPos) + 1,
                        'created_at' => $dateFormatted,
                        'raw_date' => $item->created_at,
                        'amount' => $amt,
                        'amount_formatted' => '- Rp ' . number_format($amt, 0, ',', '.'),
                        'description' => $desc,
                        'friendly_desc' => $friendlyDesc,
                        'officer_name' => $officerName,
                        'payment_code' => $paymentCode,
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
                    'prev_formatted' => ($prev < 0 ? '- Rp ' : 'Rp ') . number_format(abs($prev), 0, ',', '.'),
                    'amount' => $amt,
                    'amount_formatted' => 'Rp ' . number_format($amt, 0, ',', '.'),
                    'desc' => $desc,
                    'friendly_desc' => $friendlyDesc,
                    'officer_name' => $officerName,
                    'payment_code' => $paymentCode,
                    'after' => $running,
                    'after_formatted' => '- Rp ' . number_format(abs($running), 0, ',', '.'),
                    'category' => $category,
                    'timeline_index' => $idx + 1
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
                'friendly_desc' => $friendlyDesc,
                'officer_name' => $officerName,
                'payment_code' => $paymentCode,
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
        $adjPercent = $totalOut > 0 ? round(($totalAdjustments / $totalOut) * 100, 1) : 0;

        // Diagnosis yang akurat
        if ($totalBillDeductions > $totalPosDeductions) {
            $diagnosis = "Saldo minus terutama akibat pemotongan SPP bulanan (Rp " . number_format($totalBillDeductions, 0, ',', '.') . " / {$sppPercent}%) yang menyerap dana melebihi top up masuk.";
        } elseif ($totalBillDeductions > 0 && $totalPosDeductions > 0) {
            $diagnosis = "Saldo minus akibat kombinasi belanja Kasir (PoS) keranjang belanja (Rp " . number_format($totalPosDeductions, 0, ',', '.') . " / {$posPercent}%) dan pemotongan SPP (Rp " . number_format($totalBillDeductions, 0, ',', '.') . " / {$sppPercent}%).";
        } else {
            $diagnosis = "Saldo minus murni akibat transaksi jajan lewat fitur Kasir (PoS keranjang belanja) sebesar Rp " . number_format($totalPosDeductions, 0, ',', '.') . " tanpa batasan kasbon saat saldo tidak mencukupi.";
        }

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
                'total_adjustments' => $totalAdjustments,
                'total_adjust_formatted' => 'Rp ' . number_format($totalAdjustments, 0, ',', '.'),
                'deficit' => $deficit,
                'deficit_formatted' => 'Rp ' . number_format($deficit, 0, ',', '.'),
                'spp_percent' => $sppPercent,
                'pos_percent' => $posPercent,
                'adj_percent' => $adjPercent,
                'topup_count' => count($itemsTopup),
                'spp_count' => count($itemsSpp),
                'pos_count' => count($itemsPos),
                'total_events' => count($histories),
                'first_negative_event' => $firstNegativeEvent,
                'diagnosis' => $diagnosis
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
                'Total Potong Tagihan (Rp)',
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
                $adjustment = 0;
                foreach ($histories as $h) {
                    $cat = $this->categorizeMutation('OUT', $h->description ?? '');
                    if ($cat === 'spp') {
                        $bills += (int)$h->amount;
                    } elseif ($cat === 'adjustment') {
                        $adjustment += (int)$h->amount;
                    } else {
                        $jajan += (int)$h->amount;
                    }
                }

                $mainCause = ($bills > 0)
                    ? 'Pemotongan tagihan saat saldo tidak mencukupi (Rp ' . number_format($bills, 0, ',', '.') . ')'
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
