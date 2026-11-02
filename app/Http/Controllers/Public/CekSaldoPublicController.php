<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PointOfSaleTransaction;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CekSaldoPublicController extends Controller
{
    /**
     * Menampilkan halaman publik Kiosk Cek Saldo Santri (/cek-saldo)
     */
    public function index()
    {
        return view('public.cek-saldo.index');
    }

    /**
     * Memproses pemindaian barcode kartu santri (cara kerja identik dengan POS Kasir)
     */
    public function scan(Request $request)
    {
        try {
            $barcode = trim((string) $request->input('barcode', ''));

            if ($barcode === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan tempelkan atau scan barcode Kartu Santri terlebih dahulu.',
                    'data' => null,
                ]);
            }

            $student = Student::with(['classroom.school', 'school', 'pendingCardReport'])
                ->where(function ($query) use ($barcode) {
                    $query->where('barcode', $barcode)
                        ->orWhere('nis', $barcode)
                        ->orWhere('nisn', $barcode);
                })
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data santri dengan kartu tersebut tidak ditemukan.',
                    'data' => null,
                ]);
            }

            $pendingReport = $student->pendingCardReport;
            $isCardReported = (bool) $pendingReport;
            $cardReport = $pendingReport ? [
                'id' => $pendingReport->id,
                'issue_type' => $pendingReport->issue_type,
                'issue_label' => $pendingReport->issue_label,
                'notes' => $pendingReport->notes,
                'reported_at' => $pendingReport->created_at ? $pendingReport->created_at->format('d/m/Y H:i') : null,
            ] : null;

            $effectiveLimit = (int) ($student->getEffectiveDailyLimit() ?? 0);
            $totalThisDay = 0;
            $remainingLimit = 0;

            if ($effectiveLimit > 0) {
                $todayStart = now()->startOfDay();
                $todayEnd = now()->endOfDay();
                $totalThisDay = (int) PointOfSaleTransaction::where('student_id', $student->id)
                    ->whereBetween('paid_at', [$todayStart, $todayEnd])
                    ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                    ->sum('pay_amount');

                $remainingLimit = max(0, $effectiveLimit - $totalThisDay);
            }

            $saldo = (int) ($student->saldo ?? 0);
            $className = $student->classroom?->name ?? '-';
            $schoolName = $student->classroom?->school?->name
                ?? ($student->school?->name ?? 'PPTQ Cahaya Tasbih');

            return response()->json([
                'success' => true,
                'message' => 'Data santri ditemukan.',
                'data' => [
                    'id' => (string) $student->id,
                    'name' => $student->name,
                    'nis' => $student->nis ?: '-',
                    'class_name' => $className,
                    'school_name' => $schoolName,
                    'status' => $student->status,
                    'translated_status' => $student->translated_status,
                    'avatar_url' => $student->avatar_url,
                    'saldo' => $saldo,
                    'saldo_formatted' => 'Rp ' . number_format($saldo, 0, ',', '.'),
                    'is_blocked' => (bool) $student->is_blocked,
                    'is_card_reported' => $isCardReported,
                    'card_report' => $cardReport,
                    'effective_daily_limit' => $effectiveLimit,
                    'effective_daily_limit_formatted' => 'Rp ' . number_format($effectiveLimit, 0, ',', '.'),
                    'total_this_day' => $totalThisDay,
                    'total_this_day_formatted' => 'Rp ' . number_format($totalThisDay, 0, ',', '.'),
                    'remaining_limit' => $remainingLimit,
                    'remaining_limit_formatted' => 'Rp ' . number_format($remainingLimit, 0, ',', '.'),
                    'scanned_at' => now()->format('d M Y, H:i:s') . ' WIB',
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error in CekSaldoPublicController@scan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memeriksa saldo. Silakan coba scan kembali.',
                'data' => null,
            ], 500);
        }
    }
}
