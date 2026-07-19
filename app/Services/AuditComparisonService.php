<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Services\GeminiVisionService;

class AuditComparisonService
{
    /**
     * Compare local database with master database.
     */
    public function getComparisonData(): array
    {
        // 1. Get summary counts
        $localStudentsCount = DB::connection('mysql')->table('students')->whereNull('deleted_at')->count();
        $masterStudentsCount = DB::connection('mysql_master')->table('students')->whereNull('deleted_at')->count();

        $localBillsCount = DB::connection('mysql')->table('bills')->whereNull('deleted_at')->count();
        $masterBillsCount = DB::connection('mysql_master')->table('bills')->whereNull('deleted_at')->count();

        $localTxsCount = DB::connection('mysql')->table('transactions')->whereNull('deleted_at')->count();
        $masterTxsCount = DB::connection('mysql_master')->table('transactions')->whereNull('deleted_at')->count();

        $localTotalSaldo = DB::connection('mysql')->table('students')->whereNull('deleted_at')->sum('saldo');
        $masterTotalSaldo = DB::connection('mysql_master')->table('students')->whereNull('deleted_at')->sum('saldo');

        $localTotalSaving = DB::connection('mysql')->table('students')->whereNull('deleted_at')->sum('saving');
        $masterTotalSaving = DB::connection('mysql_master')->table('students')->whereNull('deleted_at')->sum('saving');

        $summaries = [
            'students' => ['local' => $localStudentsCount, 'master' => $masterStudentsCount, 'diff' => $localStudentsCount - $masterStudentsCount],
            'bills' => ['local' => $localBillsCount, 'master' => $masterBillsCount, 'diff' => $localBillsCount - $masterBillsCount],
            'transactions' => ['local' => $localTxsCount, 'master' => $masterTxsCount, 'diff' => $localTxsCount - $masterTxsCount],
            'saldo' => ['local' => $localTotalSaldo, 'master' => $masterTotalSaldo, 'diff' => $localTotalSaldo - $masterTotalSaldo],
            'saving' => ['local' => $localTotalSaving, 'master' => $masterTotalSaving, 'diff' => $localTotalSaving - $masterTotalSaving],
        ];

        // 2. Fetch all local and master students keyed by ID (UUID)
        $localStudents = DB::connection('mysql')->table('students')->whereNull('deleted_at')->get()->keyBy('id');
        $masterStudents = DB::connection('mysql_master')->table('students')->whereNull('deleted_at')->get()->keyBy('id');

        // Reference tables local
        $localClassrooms = DB::connection('mysql')->table('classrooms')->get()->keyBy('id');
        $localSchools = DB::connection('mysql')->table('schools')->get()->keyBy('id');
        $localAcademicYears = DB::connection('mysql')->table('academic_years')->get()->keyBy('id');

        // Reference tables master
        $masterClassrooms = DB::connection('mysql_master')->table('classrooms')->get()->keyBy('id');
        $masterSchools = DB::connection('mysql_master')->table('schools')->get()->keyBy('id');
        $masterAcademicYears = DB::connection('mysql_master')->table('academic_years')->get()->keyBy('id');

        $discrepancies = [];

        foreach ($masterStudents as $id => $ms) {
            $ls = $localStudents->get($id);
            
            // Resolve local student details
            $localClass = '-';
            $localSchool = '-';
            $localAY = '-';
            $localBillsTotal = 0;
            $localBillsCount = 0;
            $localSaldo = 0;
            $localSaving = 0;
            $localTxCount = 0;

            if ($ls) {
                $localSaldo = $ls->saldo;
                $localSaving = $ls->saving;
                if ($ls->classroom_id && isset($localClassrooms[$ls->classroom_id])) {
                    $classroom = $localClassrooms[$ls->classroom_id];
                    $localClass = $classroom->name;
                    if ($classroom->school_id && isset($localSchools[$classroom->school_id])) {
                        $localSchool = $localSchools[$classroom->school_id]->name;
                    }
                }
                
                // Get latest academic year from histories
                $latestHistory = DB::connection('mysql')->table('student_classroom_histories')
                    ->where('student_id', $ls->id)
                    ->whereNull('deleted_at')
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($latestHistory && isset($localAcademicYears[$latestHistory->academic_year_id])) {
                    $localAY = $localAcademicYears[$latestHistory->academic_year_id]->name;
                }

                // Count bills
                $localBillsCount = DB::connection('mysql')->table('bills')
                    ->where('student_id', $ls->id)
                    ->whereNull('deleted_at')
                    ->count();
                $localBillsTotal = DB::connection('mysql')->table('bills')
                    ->where('student_id', $ls->id)
                    ->whereNull('deleted_at')
                    ->sum('amount');

                // Count transactions / saldo histories
                $localTxCount = DB::connection('mysql')->table('saldo_histories')
                    ->where('student_id', $ls->id)
                    ->count();
            }

            // Resolve master student details
            $masterClass = '-';
            $masterSchool = '-';
            $masterAY = '-';
            $masterBillsTotal = 0;
            $masterBillsCount = 0;
            $masterSaldo = $ms->saldo;
            $masterSaving = $ms->saving;
            $masterTxCount = 0;

            if ($ms->classroom_id && isset($masterClassrooms[$ms->classroom_id])) {
                $classroom = $masterClassrooms[$ms->classroom_id];
                $masterClass = $classroom->name;
                if ($classroom->school_id && isset($masterSchools[$classroom->school_id])) {
                    $masterSchool = $masterSchools[$classroom->school_id]->name;
                }
            }

            // Get latest academic year from master histories
            $latestMasterHistory = DB::connection('mysql_master')->table('student_classroom_histories')
                ->where('student_id', $ms->id)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->first();
            if ($latestMasterHistory && isset($masterAcademicYears[$latestMasterHistory->academic_year_id])) {
                $masterAY = $masterAcademicYears[$latestMasterHistory->academic_year_id]->name;
            }

            // Count master bills
            $masterBillsCount = DB::connection('mysql_master')->table('bills')
                ->where('student_id', $ms->id)
                ->whereNull('deleted_at')
                ->count();
            $masterBillsTotal = DB::connection('mysql_master')->table('bills')
                ->where('student_id', $ms->id)
                ->whereNull('deleted_at')
                ->sum('amount');

            // Count master transactions / saldo histories
            $masterTxCount = DB::connection('mysql_master')->table('saldo_histories')
                ->where('student_id', $ms->id)
                ->count();

            // Compare details
            $hasDiff = !$ls || 
                       $ls->name !== $ms->name ||
                       $localClass !== $masterClass ||
                       $localSchool !== $masterSchool ||
                       $localAY !== $masterAY ||
                       $localSaldo != $masterSaldo ||
                       $localSaving != $masterSaving ||
                       $localBillsCount != $masterBillsCount ||
                       $localTxCount != $masterTxCount;

            if ($hasDiff) {
                $discrepancies[] = [
                    'nis' => $ms->nis ?: ($ls ? $ls->nis : '-'),
                    'name' => $ms->name,
                    'local' => [
                        'exists' => (bool) $ls,
                        'name' => $ls ? $ls->name : '-',
                        'school' => $localSchool,
                        'class' => $localClass,
                        'academic_year' => $localAY,
                        'saldo' => $localSaldo,
                        'saving' => $localSaving,
                        'bills_count' => $localBillsCount,
                        'bills_total' => $localBillsTotal,
                        'tx_count' => $localTxCount,
                    ],
                    'master' => [
                        'school' => $masterSchool,
                        'class' => $masterClass,
                        'academic_year' => $masterAY,
                        'saldo' => $masterSaldo,
                        'saving' => $masterSaving,
                        'bills_count' => $masterBillsCount,
                        'bills_total' => $masterBillsTotal,
                        'tx_count' => $masterTxCount,
                    ],
                    'sync_result' => [
                        'school' => $masterSchool,
                        'class' => $masterClass,
                        'academic_year' => $masterAY,
                        'saldo' => $masterSaldo,
                        'saving' => $masterSaving,
                        'bills_count' => $masterBillsCount,
                        'bills_total' => $masterBillsTotal,
                        'tx_count' => $masterTxCount,
                    ]
                ];
            }
        }

        // Add students that are in local but not in master
        foreach ($localStudents as $id => $ls) {
            if (!$masterStudents->has($id)) {
                $localClass = '-';
                $localSchool = '-';
                $localAY = '-';
                $localBillsTotal = 0;
                $localBillsCount = 0;
                $localTxCount = 0;
                
                if ($ls->classroom_id && isset($localClassrooms[$ls->classroom_id])) {
                    $classroom = $localClassrooms[$ls->classroom_id];
                    $localClass = $classroom->name;
                    if ($classroom->school_id && isset($localSchools[$classroom->school_id])) {
                        $localSchool = $localSchools[$classroom->school_id]->name;
                    }
                }
                
                $latestHistory = DB::connection('mysql')->table('student_classroom_histories')
                    ->where('student_id', $ls->id)
                    ->whereNull('deleted_at')
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($latestHistory && isset($localAcademicYears[$latestHistory->academic_year_id])) {
                    $localAY = $localAcademicYears[$latestHistory->academic_year_id]->name;
                }

                $localBillsCount = DB::connection('mysql')->table('bills')
                    ->where('student_id', $ls->id)
                    ->whereNull('deleted_at')
                    ->count();
                $localBillsTotal = DB::connection('mysql')->table('bills')
                    ->where('student_id', $ls->id)
                    ->whereNull('deleted_at')
                    ->sum('amount');

                $localTxCount = DB::connection('mysql')->table('saldo_histories')
                    ->where('student_id', $ls->id)
                    ->count();

                $discrepancies[] = [
                    'nis' => $ls->nis ?: '-',
                    'name' => $ls->name,
                    'local' => [
                        'exists' => true,
                        'name' => $ls->name,
                        'school' => $localSchool,
                        'class' => $localClass,
                        'academic_year' => $localAY,
                        'saldo' => $ls->saldo,
                        'saving' => $ls->saving,
                        'bills_count' => $localBillsCount,
                        'bills_total' => $localBillsTotal,
                        'tx_count' => $localTxCount,
                    ],
                    'master' => [
                        'school' => '-',
                        'class' => '-',
                        'academic_year' => '-',
                        'saldo' => 0,
                        'saving' => 0,
                        'bills_count' => 0,
                        'bills_total' => 0,
                        'tx_count' => 0,
                    ],
                    'sync_result' => [
                        'school' => 'DELETED',
                        'class' => '-',
                        'academic_year' => '-',
                        'saldo' => 0,
                        'saving' => 0,
                        'bills_count' => 0,
                        'bills_total' => 0,
                        'tx_count' => 0,
                    ]
                ];
            }
        }

        return [
            'summaries' => $summaries,
            'discrepancies' => $discrepancies,
        ];
    }

    /**
     * Generate AI Insight using Gemini API based on comparison results.
     */
    public function generateAiInsight(array $comparison): string
    {
        $summaries = $comparison['summaries'];
        $discrepancies = $comparison['discrepancies'];

        $prompt = "Kamu adalah AI Auditor handal untuk aplikasi keuangan sekolah Cahaya Tasbih.
Berikut adalah data perbandingan integritas data antara Database Lokal saat ini (Data Awal) dan Database Master Lama (Data dari DB Lama):

Ringkasan Perbedaan:
- Jumlah Siswa: Lokal ({$summaries['students']['local']}) vs Master ({$summaries['students']['master']}) [Selisih: {$summaries['students']['diff']}]
- Jumlah Tagihan: Lokal ({$summaries['bills']['local']}) vs Master ({$summaries['bills']['master']}) [Selisih: {$summaries['bills']['diff']}]
- Jumlah Transaksi: Lokal ({$summaries['transactions']['local']}) vs Master ({$summaries['transactions']['master']}) [Selisih: {$summaries['transactions']['diff']}]
- Total Saldo: Lokal (Rp " . number_format($summaries['saldo']['local'], 0, ',', '.') . ") vs Master (Rp " . number_format($summaries['saldo']['master'], 0, ',', '.') . ") [Selisih: Rp " . number_format($summaries['saldo']['diff'], 0, ',', '.') . "]
- Total Tabungan: Lokal (Rp " . number_format($summaries['saving']['local'], 0, ',', '.') . ") vs Master (Rp " . number_format($summaries['saving']['master'], 0, ',', '.') . ") [Selisih: Rp " . number_format($summaries['saving']['diff'], 0, ',', '.') . "]

Jumlah Siswa dengan Perbedaan Detail (UPT, Kelas, Saldo, Tagihan): " . count($discrepancies) . " siswa.

Tugas kamu:
Berikan analisis AI Insight yang ringkas, profesional, dan taktis dalam Bahasa Indonesia. Kembalikan HANYA teks HTML bersih (gunakan tag <p>, <ul>, <li>, <strong>, <span> dengan class-class Bootstrap) yang siap dirender di dalam halaman web. Jangan gunakan karakter markdown seperti bintang ganda (**) untuk cetak tebal, gunakan tag <strong> saja.

Format analisis harus mencakup:
1. Potensi Risiko: Jelaskan konsekuensi dan bahaya terhadap konsistensi data jika langsung melakukan sinkronisasi tanpa verifikasi (seperti hilangnya riwayat saldo atau anomali tagihan aktif santri).
2. Rekomendasi Langkah Konfirmasi: Berikan rekomendasi langkah demi langkah sebelum melakukan aksi konfirmasi lanjutan final update (seperti backup database, melakukan sinkronisasi bertahap, atau menginvestigasi siswa dengan selisih saldo/tabungan yang signifikan).
3. Pentingnya Integritas Relasi: Jelaskan pentingnya memastikan keselarasan relasi siswa dengan UPT, kelas, tahun ajaran, dan transaksi keuangan mereka agar tidak memicu kebingungan wali santri di aplikasi mobile.";

        $insight = GeminiVisionService::generateTextInsight($prompt);

        if (empty($insight)) {
            $insight = "
            <p class='text-warning'><i class='fas fa-exclamation-triangle me-2'></i> <strong>Gagal memuat AI Insight otomatis.</strong> Kunci API Gemini tidak dikonfigurasi atau layanan sedang sibuk.</p>
            <p><strong>Rekomendasi Manual Auditor:</strong></p>
            <ul>
                <li>Lakukan backup basis data lokal sebelum melakukan pembaruan/sinkronisasi massal.</li>
                <li>Verifikasi ketidakcocokan kelas dan sekolah pada daftar siswa di bawah ini untuk menghindari ketidaksesuaian laporan di portal wali santri.</li>
                <li>Periksa perbedaan saldo dan riwayat transaksi dengan teliti.</li>
            </ul>
            ";
        }

        return $insight;
    }
}
