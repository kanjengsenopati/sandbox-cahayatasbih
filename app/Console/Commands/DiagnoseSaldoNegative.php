<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DiagnoseSaldoNegative extends Command
{
    protected $signature = 'saldo:diagnose 
                            {--top=10 : Jumlah santri teratas untuk didiagnosa}
                            {--student= : Filter berdasarkan NIS atau Nama}';

    protected $description = 'Diagnosa mendalam data saldo_histories untuk santri dengan saldo negatif';

    public function handle()
    {
        $top = (int) $this->option('top');
        $filterStudent = $this->option('student');

        $this->info("=========================================================================================");
        $this->info("     DIAGNOSA MENDALAM SALDO NEGATIF SANTRI (RAW DATA DUMP)                              ");
        $this->info("=========================================================================================\n");

        $query = DB::table('students')
            ->leftJoin('classrooms', 'students.classroom_id', '=', 'classrooms.id');

        if ($filterStudent) {
            $query->where(function ($q) use ($filterStudent) {
                $q->where('students.nis', $filterStudent)
                  ->orWhere('students.id', $filterStudent)
                  ->orWhere('students.name', 'like', "%{$filterStudent}%");
            });
        } else {
            $query->where('students.saldo', '<', 0)
                  ->orderBy('students.saldo', 'asc')
                  ->limit($top);
        }

        $students = $query->select('students.id', 'students.nis', 'students.name', 'students.saldo', 'classrooms.name as classroom')
            ->get();

        $totalNegative = DB::table('students')->where('saldo', '<', 0)->count();
        $this->info("Total santri dengan saldo negatif di sistem: {$totalNegative}");
        $this->info("Menampilkan " . $students->count() . " santri:\n");

        foreach ($students as $idx => $student) {
            $this->info("=========================================================================================");
            $this->info("[" . ($idx + 1) . "] {$student->name} (NIS: {$student->nis}, Kelas: {$student->classroom})");
            $this->info("    Saldo DB Saat Ini: Rp " . number_format($student->saldo, 0, ',', '.'));

            // All mutations for this student
            $histories = DB::table('saldo_histories')
                ->where('student_id', $student->id)
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $this->info("    Total Mutasi: " . $histories->count());

            $this->info("\n    DAFTAR MUTASI BESAR (>= Rp 500.000) ATAU DENGAN KATA KUNCI PENYESUAIAN/TARIK:");
            $flagged = $histories->filter(function ($h) {
                $desc = strtolower($h->description ?? '');
                return $h->amount >= 500000 
                    || str_contains($desc, 'penyesuai')
                    || str_contains($desc, 'koreksi')
                    || str_contains($desc, 'alokasi')
                    || str_contains($desc, 'kelebihan')
                    || (str_contains($desc, 'tarik') && $h->amount >= 100000);
            });

            if ($flagged->count() > 0) {
                foreach ($flagged as $f) {
                    $this->warn("      [{$f->created_at}] ID: {$f->id} | {$f->type} Rp " . number_format($f->amount, 0, ',', '.') 
                        . " | Status: {$f->status} | Before: Rp " . number_format($f->balance_before, 0, ',', '.') 
                        . " After: Rp " . number_format($f->balance_after, 0, ',', '.') 
                        . " | Desc: {$f->description}");
                }
            } else {
                $this->line("      (Tidak ada mutasi >= 500.000 atau flagged)");
            }

            // Summary
            $inSuccess = $histories->where('type', 'IN')->where('status', 'SUCCESS')->sum('amount');
            $outSuccess = $histories->whereIn('type', ['OUT', 'WITHDRAW'])->where('status', 'SUCCESS')->sum('amount');

            $this->info("\n    KALKULASI MURNI:");
            $this->line("      Total IN (SUCCESS)  : Rp " . number_format($inSuccess, 0, ',', '.'));
            $this->line("      Total OUT (SUCCESS) : Rp " . number_format($outSuccess, 0, ',', '.'));
            $this->line("      IN - OUT            : Rp " . number_format($inSuccess - $outSuccess, 0, ',', '.'));

            $this->line('');
        }

        return 0;
    }
}
