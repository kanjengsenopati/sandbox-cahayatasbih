<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\School;
use App\Models\Student;
use App\Models\BillType;
use App\Models\AcademicYear;
use App\Models\Transaction;
use App\Traits\CanonicalBillTypeTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleSheetsBackupService
{
    use CanonicalBillTypeTrait;

    /**
     * 12 Sheet Tabs target auto placement di Google Sheet
     */
    public const TARGET_TABS = [
        'SYAHRIAH SMP',
        'SYAHRIAH MA',
        'SYAHRIAH PONDOK',
        'ZARKASI SMP',
        'ZARKASI MA',
        'ZARKASI PONDOK',
        'LKS SMP',
        'LKS MA',
        'PENDAFTARAN',
        'BIAYA APLIKASI SMP',
        'BIAYA APLIKASI MA',
        'BIAYA APLIKASI PONDOK',
    ];

    /**
     * Map bulan akademik (Juli = 0 s/d Juni = 11)
     */
    public const MONTH_MAP = [
        7  => 0, // Juli
        8  => 1, // Agustus
        9  => 2, // September
        10 => 3, // Oktober
        11 => 4, // November
        12 => 5, // Desember
        1  => 6, // Januari
        2  => 7, // Februari
        3  => 8, // Maret
        4  => 9, // April
        5  => 10, // Mei
        6  => 11, // Juni
    ];

    /**
     * Eksekusi pencadangan otomatis seluruh atau sebagian sheet tab ke Google Sheets.
     *
     * @param string|null $specificTab Jika diisi, hanya membackup tab tersebut
     * @return array Hasil eksekusi
     */
    public function runBackup(?string $specificTab = null): array
    {
        $logChannel = 'google_sheets_backup';
        $startTime = microtime(true);

        $this->logInfo("Memulai proses Auto-Backup Google Sheets...", ['tab' => $specificTab]);

        $gasUrl = config('services.google.sheets_webapp_url') ?: env('GOOGLE_SHEETS_WEBAPP_URL');
        $spreadsheetId = config('services.google.backup_spreadsheet_id') ?: env('GOOGLE_SHEETS_BACKUP_SPREADSHEET_ID', '1NPPyqpaArOVW0vAsZ4jjBwOLuK9mutnhqWtgQDeSEY8');

        if (empty($gasUrl)) {
            $msg = "GOOGLE_SHEETS_WEBAPP_URL belum dikonfigurasi di .env atau config/services.php.";
            $this->logError($msg);
            return ['success' => false, 'message' => $msg];
        }

        if (empty($spreadsheetId)) {
            $msg = "GOOGLE_SHEETS_BACKUP_SPREADSHEET_ID belum dikonfigurasi.";
            $this->logError($msg);
            return ['success' => false, 'message' => $msg];
        }

        $activeAy = AcademicYear::where('is_active', 1)->first() ?? AcademicYear::orderBy('id', 'desc')->first();
        if (!$activeAy) {
            $msg = "Tidak ditemukan Tahun Ajaran yang aktif.";
            $this->logError($msg);
            return ['success' => false, 'message' => $msg];
        }

        $tabsToProcess = $specificTab ? [$specificTab] : self::TARGET_TABS;
        $results = [];
        $totalRowsSynced = 0;
        $errors = [];

        foreach ($tabsToProcess as $tabName) {
            try {
                $sheetData = $this->generateDataForTab($tabName, $activeAy);
                if (!$sheetData) {
                    $this->logWarning("Tab {$tabName}: Tidak ada data tagihan atau BillType tidak ditemukan.");
                    $results[$tabName] = ['status' => 'skipped', 'reason' => 'No data'];
                    continue;
                }

                $sendResult = $this->sendSheetToGoogleAppsScript($gasUrl, $spreadsheetId, $sheetData);
                if ($sendResult['success']) {
                    $rowCount = $sheetData['count'];
                    $totalRowsSynced += $rowCount;
                    $results[$tabName] = [
                        'status' => 'success',
                        'rows' => $rowCount,
                    ];
                    $this->logInfo("Tab {$tabName}: Berhasil disinkronkan ({$rowCount} santri).");
                } else {
                    $errors[$tabName] = $sendResult['message'];
                    $results[$tabName] = [
                        'status' => 'failed',
                        'error' => $sendResult['message'],
                    ];
                    $this->logError("Tab {$tabName}: Gagal mengirim ke Google Sheets - " . $sendResult['message']);
                }
            } catch (\Throwable $e) {
                $errors[$tabName] = $e->getMessage();
                $results[$tabName] = [
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
                $this->logError("Tab {$tabName}: Exception saat kompilasi data - " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $duration = round(microtime(true) - $startTime, 2);
        $summary = [
            'success' => empty($errors),
            'duration_seconds' => $duration,
            'spreadsheet_id' => $spreadsheetId,
            'academic_year' => $activeAy->name ?? '-',
            'total_rows_synced' => $totalRowsSynced,
            'results' => $results,
            'errors' => $errors,
        ];

        $this->logInfo("Auto-Backup Google Sheets selesai dalam {$duration} detik.", $summary);
        return $summary;
    }

    /**
     * Mengkompilasi matriks data 2D untuk satu sheet tab spesifik.
     */
    public function generateDataForTab(string $tabName, AcademicYear $academicYear): ?array
    {
        $matchingInfo = $this->getMatchingBillTypeInfo($tabName);
        $matchedBillTypeIds = $matchingInfo['ids'];

        if (empty($matchedBillTypeIds)) {
            return null;
        }

        // Tentukan tahun awal & akhir akademik
        [$startYear, $endYear] = $this->resolveAcademicYears($academicYear);

        // Susun baris Header sesuai format screenshot (Violet Baris 1)
        $headings = [
            'No',
            'Tanggal',
            'Nama',
            'Kelas',
            'UPT',
            'Jenis Transaksi',
            'Item Tagihan',
            'Nominal',
            'Metode Pembayaran',
            'Petugas',
            "Juli {$startYear}",
            "Agustus {$startYear}",
            "September {$startYear}",
            "Oktober {$startYear}",
            "November {$startYear}",
            "Desember {$startYear}",
            "Januari {$endYear}",
            "Februari {$endYear}",
            "Maret {$endYear}",
            "April {$endYear}",
            "Mei {$endYear}",
            "Juni {$endYear}",
        ];

        // Filter UPT berdasarkan nama tab untuk mencegah cross-school leakage
        $schoolTypeFilter = null;
        if (str_contains($tabName, 'SMP')) {
            $schoolTypeFilter = School::TYPE_SMP;
        } elseif (str_contains($tabName, 'MA')) {
            $schoolTypeFilter = School::TYPE_MA;
        } elseif (str_contains($tabName, 'PONDOK')) {
            $schoolTypeFilter = School::TYPE_PONDOK;
        }

        // Query bills santri untuk bill types tersebut
        $billsQuery = Bill::where('academic_year_id', $academicYear->id)
            ->whereIn('bill_type_id', $matchedBillTypeIds)
            ->with([
                'student' => fn($q) => $q->withTrashed(),
                'student.classroom.school',
                'billType',
                'transactions' => function ($q) {
                    $q->where('status', Transaction::STATUS_PAID)
                      ->with(['paymentMethod', 'admin'])
                      ->orderBy('paid_at', 'desc')
                      ->orderBy('created_at', 'desc');
                },
            ]);

        if ($schoolTypeFilter) {
            $billsQuery->whereHas('student.classroom.school', function ($q) use ($schoolTypeFilter) {
                $q->where('type', $schoolTypeFilter);
            });
        }

        $bills = $billsQuery->get();

        if ($bills->isEmpty()) {
            return [
                'sheetName' => $tabName,
                'headerColor' => '#7C3AED',
                'freezeCols' => 3,
                'values' => [$headings],
                'count' => 0,
            ];
        }

        // Kelompokkan per-santri
        $groupedByStudent = $bills->groupBy('student_id');

        // Urutkan santri berdasarkan nama secara alfabetis
        $sortedGroups = $groupedByStudent->sortBy(function ($studentBills) {
            return strtolower($studentBills->first()->student?->name ?? '');
        });

        $rows = [];
        $rows[] = $headings;
        $rowNumber = 0;

        foreach ($sortedGroups as $studentId => $studentBills) {
            $firstBill = $studentBills->first();
            $student = $firstBill->student;
            if (!$student) {
                continue;
            }

            $rowNumber++;

            // Cari transaksi berbayar terbaru santri pada tagihan ini
            $latestTx = null;
            foreach ($studentBills as $b) {
                $tx = $b->transactions->first();
                if ($tx) {
                    if (!$latestTx || ($tx->paid_at && $tx->paid_at > $latestTx->paid_at)) {
                        $latestTx = $tx;
                    }
                }
            }

            // Kolom Tanggal: Tanggal pembayaran terakhir jika sudah bayar, atau waktu update bill terakhir
            $tanggalFormatted = '-';
            $metodePembayaran = '-';
            $petugas = '-';

            if ($latestTx) {
                $dateToFormat = $latestTx->paid_at ?: $latestTx->created_at;
                if ($dateToFormat) {
                    $tanggalFormatted = Carbon::parse($dateToFormat)->locale('id')->translatedFormat('l, d F Y H:i:s');
                }
                $metodePembayaran = $latestTx->paymentMethod?->name ?? 'TUNAI';
                $petugas = $latestTx->admin?->name ?? '-';
            } else {
                $latestBillUpdate = $studentBills->sortByDesc('updated_at')->first();
                if ($latestBillUpdate && $latestBillUpdate->updated_at) {
                    $tanggalFormatted = Carbon::parse($latestBillUpdate->updated_at)->locale('id')->translatedFormat('l, d F Y H:i:s');
                }
            }

            // Hitung alokasi bulan (Juli s/d Juni)
            $monthlyAmounts = array_fill(0, 12, 0);
            $totalAmount = 0;

            foreach ($studentBills as $b) {
                $m = (int) $b->month;
                $amt = (int) $b->amount;
                $totalAmount += $amt;

                if (isset(self::MONTH_MAP[$m])) {
                    $idx = self::MONTH_MAP[$m];
                    $monthlyAmounts[$idx] += $amt;
                }
            }

            $monthlyColumns = array_map(function ($amt) {
                return 'Rp ' . number_format($amt, 0, ',', '.');
            }, $monthlyAmounts);

            $baseRow = [
                $rowNumber,
                $tanggalFormatted,
                $student->name ?? '-',
                $student->classroom?->name ?? '-',
                $student->classroom?->school?->name ?? '-',
                'Tagihan',
                $tabName,
                'Rp ' . number_format($totalAmount, 0, ',', '.'),
                $metodePembayaran,
                $petugas,
            ];

            $rows[] = array_merge($baseRow, $monthlyColumns);
        }

        return [
            'sheetName' => $tabName,
            'headerColor' => '#7C3AED', // Ungu matching screenshot
            'freezeCols' => 3, // Freeze sampai kolom C (No, Tanggal, Nama)
            'values' => $rows,
            'count' => $rowNumber,
        ];
    }

    /**
     * Mengirimkan data sheet ke Google Apps Script Web App.
     */
    protected function sendSheetToGoogleAppsScript(string $gasUrl, string $spreadsheetId, array $sheetData): array
    {
        try {
            $response = Http::timeout(120)
                ->withoutVerifying()
                ->retry(3, 1000)
                ->withOptions([
                    'allow_redirects' => [
                        'max' => 5,
                        'strict' => false,
                        'referer' => true,
                        'protocols' => ['https']
                    ]
                ])
                ->post($gasUrl, [
                    'action' => 'backup_placement',
                    'spreadsheetId' => $spreadsheetId,
                    'sheetName' => $sheetData['sheetName'],
                    'headerColor' => $sheetData['headerColor'] ?? '#7C3AED',
                    'freezeCols' => $sheetData['freezeCols'] ?? 3,
                    'values' => $sheetData['values'],
                ]);

            if ($response->successful()) {
                $json = $response->json();
                if (!empty($json['success'])) {
                    return [
                        'success' => true,
                        'url' => $json['url'] ?? null,
                    ];
                }
                return [
                    'success' => false,
                    'message' => $json['message'] ?? 'Respons GAS tidak bernilai sukses.',
                ];
            }

            return [
                'success' => false,
                'message' => 'HTTP status ' . $response->status() . ': ' . substr($response->body(), 0, 200),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Resolusi startYear dan endYear dari AcademicYear
     */
    protected function resolveAcademicYears(AcademicYear $ay): array
    {
        if (method_exists($ay, 'getStartYearSafe')) {
            $start = $ay->getStartYearSafe();
            if ($start) {
                return [$start, $start + 1];
            }
        }

        if (!empty($ay->start_year) && !empty($ay->end_year)) {
            return [(int)$ay->start_year, (int)$ay->end_year];
        }

        // Parse dari nama (contoh: "2025/2026")
        if (preg_match('/(\d{4})\s*\/\s*(\d{4})/', $ay->name ?? '', $matches)) {
            return [(int)$matches[1], (int)$matches[2]];
        }

        $nowYear = (int) date('Y');
        $nowMonth = (int) date('n');
        $startYear = $nowMonth >= 7 ? $nowYear : $nowYear - 1;
        return [$startYear, $startYear + 1];
    }

    protected function logInfo(string $message, array $context = []): void
    {
        Log::channel('single')->info("[GoogleSheetsBackup] " . $message, $context);
        $this->appendToFileLog("INFO", $message, $context);
    }

    protected function logWarning(string $message, array $context = []): void
    {
        Log::channel('single')->warning("[GoogleSheetsBackup] " . $message, $context);
        $this->appendToFileLog("WARNING", $message, $context);
    }

    protected function logError(string $message, array $context = []): void
    {
        Log::channel('single')->error("[GoogleSheetsBackup] " . $message, $context);
        $this->appendToFileLog("ERROR", $message, $context);
    }

    protected function appendToFileLog(string $level, string $message, array $context = []): void
    {
        try {
            $logPath = storage_path('logs/google_sheets_backup.log');
            $timestamp = date('Y-m-d H:i:s');
            $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES) : '';
            @file_put_contents($logPath, "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {
            // Ignore file logging error
        }
    }
}
