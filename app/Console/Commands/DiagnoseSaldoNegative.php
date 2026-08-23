<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DiagnoseSaldoNegative extends Command
{
    protected $signature = 'saldo:diagnose {--top=5 : Jumlah santri teratas untuk didiagnosa}';
    protected $description = 'Diagnosa mendalam data saldo_histories untuk santri dengan saldo negatif terdalam';

    public function handle()
    {
        $top = (int) $this->option('top');

        $this->info("=========================================================================================");
        $this->info("     DIAGNOSA MENDALAM SALDO NEGATIF SANTRI (RAW DATA DUMP)                              ");
        $this->info("=========================================================================================\n");

        // Get top N most negative students
        $students = DB::table('students')
            ->join('classrooms', 'students.classroom_id', '=', 'classrooms.id')
            ->where('students.saldo', '<', 0)
            ->orderBy('students.saldo', 'asc')
            ->limit($top)
            ->select('students.id', 'students.nis', 'students.name', 'students.saldo', 'classrooms.name as classroom')
            ->get();

        $this->info("Ditemukan " . DB::table('students')->where('saldo', '<', 0)->count() . " santri dengan saldo negatif.");
        $this->info("Mendiagnosa {$top} santri dengan saldo paling negatif:\n");

        foreach ($students as $idx => $student) {
            $this->info("=========================================================================================");
            $this->info("[" . ($idx + 1) . "] {$student->name} (NIS: {$student->nis}, Kelas: {$student->classroom})");
            $this->info("    Saldo DB Saat Ini: Rp " . number_format($student->saldo, 0, ',', '.'));

            // Count all histories by type and status
            $summary = DB::table('saldo_histories')
                ->where('student_id', $student->id)
                ->select(
                    'type',
                    'status',
                    DB::raw('count(*) as cnt'),
                    DB::raw('sum(amount) as total')
                )
                ->groupBy('type', 'status')
                ->get();

            $this->info("\n    RINGKASAN MUTASI (per type & status):");
            foreach ($summary as $s) {
                $this->line("      Type: {$s->type} | Status: {$s->status} | Count: {$s->cnt} | Total: Rp " . number_format($s->total, 0, ',', '.'));
            }

            // Check for Alokasi records
            $alokasiCount = DB::table('saldo_histories')
                ->where('student_id', $student->id)
                ->where('description', 'like', '%Alokasi Kelebihan Bayar%')
                ->count();
            $this->info("\n    Record Alokasi Kelebihan Bayar: {$alokasiCount}");

            // List ALL distinct descriptions with totals
            $descs = DB::table('saldo_histories')
                ->where('student_id', $student->id)
                ->where('status', 'SUCCESS')
                ->select(
                    'type',
                    'description',
                    DB::raw('count(*) as cnt'),
                    DB::raw('sum(amount) as total')
                )
                ->groupBy('type', 'description')
                ->orderBy('type')
                ->orderBy('total', 'desc')
                ->get();

            $this->info("\n    DETAIL SEMUA DESKRIPSI (SUCCESS only):");
            $totalIn = 0;
            $totalOut = 0;
            foreach ($descs as $d) {
                $prefix = $d->type === 'IN' ? '  [+]' : '  [-]';
                $this->line("    {$prefix} {$d->type}: {$d->description} (×{$d->cnt}) = Rp " . number_format($d->total, 0, ',', '.'));
                if ($d->type === 'IN') $totalIn += $d->total;
                else $totalOut += $d->total;
            }

            $calculatedBalance = $totalIn - $totalOut;
            $this->info("\n    KALKULASI:");
            $this->line("      Total IN (SUCCESS)  : Rp " . number_format($totalIn, 0, ',', '.'));
            $this->line("      Total OUT (SUCCESS) : Rp " . number_format($totalOut, 0, ',', '.'));
            $this->line("      IN - OUT            : Rp " . number_format($calculatedBalance, 0, ',', '.'));
            $this->line("      Saldo DB            : Rp " . number_format($student->saldo, 0, ',', '.'));
            $this->line("      Selisih             : Rp " . number_format($calculatedBalance - $student->saldo, 0, ',', '.'));

            // Show first 3 and last 3 records chronologically
            $firstRecords = DB::table('saldo_histories')
                ->where('student_id', $student->id)
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->limit(3)
                ->get();

            $lastRecords = DB::table('saldo_histories')
                ->where('student_id', $student->id)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->limit(3)
                ->get()
                ->reverse();

            $this->info("\n    3 RECORD PERTAMA:");
            foreach ($firstRecords as $r) {
                $this->line("      [{$r->created_at}] {$r->type} Rp " . number_format($r->amount, 0, ',', '.') 
                    . " | Status:{$r->status} | Before:Rp " . number_format($r->balance_before, 0, ',', '.')
                    . " After:Rp " . number_format($r->balance_after, 0, ',', '.')
                    . " | {$r->description}");
            }

            $this->info("    3 RECORD TERAKHIR:");
            foreach ($lastRecords as $r) {
                $this->line("      [{$r->created_at}] {$r->type} Rp " . number_format($r->amount, 0, ',', '.') 
                    . " | Status:{$r->status} | Before:Rp " . number_format($r->balance_before, 0, ',', '.')
                    . " After:Rp " . number_format($r->balance_after, 0, ',', '.')
                    . " | {$r->description}");
            }

            $this->line('');
        }

        return 0;
    }
}
