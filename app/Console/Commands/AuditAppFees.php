<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\BillType;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditAppFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:audit-app-fees {--fix : Hapus tagihan ganda secara otomatis}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Melakukan audit tagihan Biaya Aplikasi di seluruh UPT dan menghapus tagihan ganda';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fix = $this->option('fix');
        
        $this->info('==================================================');
        $this->info('   MEMULAI AUDIT & INVESTIGASI BIAYA APLIKASI      ');
        $this->info('==================================================');
        if ($fix) {
            $this->warn('Mode: PERBAIKAN OTOMATIS (Tagihan duplikat akan dihapus)');
        } else {
            $this->info('Mode: SIMULASI / DRY-RUN (Hanya melaporkan, gunakan --fix untuk memperbaiki)');
        }
        $this->info('');

        // 1. Cari tipe tagihan yang mengandung kata 'APLIKASI'
        $billTypes = BillType::withTrashed()
            ->where('name', 'like', '%APLIKASI%')
            ->get();

        if ($billTypes->isEmpty()) {
            $this->error('Tidak ditemukan Tipe Tagihan (BillType) yang berkaitan dengan "APLIKASI".');
            return Command::SUCCESS;
        }

        $this->info('Daftar Tipe Tagihan terkait Aplikasi yang ditemukan:');
        foreach ($billTypes as $bt) {
            $this->line("- [ID: {$bt->id}] {$bt->name} (TA: " . ($bt->academicYear->name ?? 'N/A') . ")" . ($bt->deleted_at ? ' [DIHAPUS]' : ''));
        }
        $this->info('--------------------------------------------------');

        $grandTotalDuplicates = 0;
        $grandTotalFixed = 0;

        // 2. Audit per Tipe Tagihan
        foreach ($billTypes as $bt) {
            $this->info("Mengaudit: {$bt->name}");
            
            // Dapatkan jumlah siswa yang terdaftar pada tagihan ini
            $studentIds = Bill::where('bill_type_id', $bt->id)
                ->distinct()
                ->pluck('student_id')
                ->toArray();

            $this->info("Jumlah siswa yang memiliki tagihan ini: " . count($studentIds));
            
            $duplicatesCount = 0;
            $fixedCount = 0;

            foreach ($studentIds as $studentId) {
                // Ambil semua tagihan tipe ini untuk siswa tertentu, urutkan agar mudah diproses
                $bills = Bill::where('bill_type_id', $bt->id)
                    ->where('student_id', $studentId)
                    ->orderBy('year')
                    ->orderBy('month')
                    ->orderBy('created_at', 'desc')
                    ->get();

                // Group by month-year
                $grouped = [];
                foreach ($bills as $b) {
                    $key = "{$b->month}-{$b->year}";
                    $grouped[$key][] = $b;
                }

                $studentReported = false;

                foreach ($grouped as $key => $monthBills) {
                    $count = count($monthBills);
                    if ($count > 1) {
                        $duplicatesCount += ($count - 1);
                        
                        if (!$studentReported) {
                            $student = Student::withTrashed()->with('classroom.school')->find($studentId);
                            $schoolName = $student->classroom->school->name ?? 'UPT Tidak Diketahui';
                            $className = $student->classroom->name ?? 'Kelas Tidak Diketahui';
                            
                            $this->warn("  Siswa: {$student->name} (NIS: {$student->nis})");
                            $this->line("    UPT: {$schoolName} | Kelas: {$className}");
                            $studentReported = true;
                        }

                        $amounts = array_map(fn($mb) => 'Rp ' . number_format($mb->amount, 0, ',', '.') . " ({$mb->status})", $monthBills);
                        $this->line("    - Periode {$key}: Ditemukan {$count} tagihan. Rincian: " . implode(', ', $amounts));

                        // Tentukan satu tagihan yang akan dipertahankan (PAID diprioritaskan, jika tidak ada pakai yang terbaru)
                        $keepBill = $monthBills[0]; // default terbaru karena order by created_at desc
                        foreach ($monthBills as $mb) {
                            if ($mb->status === Bill::STATUS_PAID) {
                                $keepBill = $mb;
                                break;
                            }
                        }

                        $this->line("      -> Mempertahankan ID: {$keepBill->id} (Status: {$keepBill->status}, Nominal: Rp " . number_format($keepBill->amount, 0, ',', '.') . ")");

                        // Hapus duplikasi
                        foreach ($monthBills as $mb) {
                            if ($mb->id !== $keepBill->id) {
                                if ($fix) {
                                    $mb->forceDelete();
                                    $fixedCount++;
                                }
                            }
                        }
                    }
                }
            }

            $this->info("Hasil Audit untuk {$bt->name}:");
            $this->line("- Jumlah duplikasi ditemukan : {$duplicatesCount} tagihan");
            if ($fix) {
                $this->line("- Jumlah duplikasi diperbaiki : {$fixedCount} tagihan");
            }
            $this->info('--------------------------------------------------');

            $grandTotalDuplicates += $duplicatesCount;
            $grandTotalFixed += $fixedCount;
        }

        $this->info('==================================================');
        $this->info('   AUDIT SELESAI');
        $this->info("   Total Duplikasi Ditemukan di Semua UPT: {$grandTotalDuplicates}");
        if ($fix) {
            $this->info("   Total Duplikasi Berhasil Dihapus     : {$grandTotalFixed}");
        } else {
            $this->warn("   Gunakan opsi --fix untuk menghapus {$grandTotalDuplicates} tagihan ganda.");
        }
        $this->info('==================================================');

        return Command::SUCCESS;
    }
}
