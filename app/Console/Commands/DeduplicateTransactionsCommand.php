<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Services\TransactionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeduplicateTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:deduplicate-transactions {--dry-run : Hanya cek dan tampilkan data tanpa menghapus} {--student= : Filter berdasarkan NIS atau Nama Santri}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deteksi dan bersihkan transaksi kembar (duplikat), detail transaksi ganda, dan sisa detail CANCELLED/INVALID secara otomatis.';

    /**
     * Valid paid statuses.
     */
    protected $paidStatuses = [
        Transaction::STATUS_PAID, 'paid', 'PAID', 'approved', 'APPROVED', 'SUCCESS', 'success', 'LUNAS', 'lunas'
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $studentFilter = $this->option('student');

        $this->info("================================================================================");
        $this->info("   AUDIT & CLEANUP TRANSAKSI KEMBAR / DUPLIKAT TAGIHAN SISWA");
        $this->info("   Mode: " . ($isDryRun ? "<fg=yellow>DRY-RUN (Hanya Simulasi / Audit)</>" : "<fg=green>EKSEKUSI CLEANUP (forceDelete duplikat & sync tagihan)</>"));
        $this->info("================================================================================");

        $affectedStudentIds = collect();

        // ---------------------------------------------------------------------
        // 1. DETEKSI TRANSAKSI KEMBAR (TWIN TRANSACTIONS)
        // ---------------------------------------------------------------------
        $this->newLine();
        $this->info("1. Memeriksa Transaksi Kembar (Dibuat dalam selisih <= 120 detik dengan tagihan yang sama)...");

        $query = DB::table('transactions as t1')
            ->join('transactions as t2', function ($join) {
                $join->on('t1.student_id', '=', 't2.student_id')
                    ->on('t1.pay_amount', '=', 't2.pay_amount')
                    ->whereRaw('t1.id < t2.id')
                    ->whereRaw('abs(TIMESTAMPDIFF(SECOND, t1.created_at, t2.created_at)) <= 120');
            })
            ->where('t1.type', Transaction::TYPE_BILL)
            ->where('t2.type', Transaction::TYPE_BILL)
            ->whereIn('t1.status', $this->paidStatuses)
            ->whereIn('t2.status', $this->paidStatuses)
            ->whereNull('t1.deleted_at')
            ->whereNull('t2.deleted_at')
            ->select(
                't1.id as orig_tx_id',
                't2.id as dup_tx_id',
                't1.student_id',
                't1.pay_amount',
                't1.created_at as orig_time',
                't2.created_at as dup_time'
            );

        if ($studentFilter) {
            $matchedIds = Student::where('nis', 'like', "%{$studentFilter}%")
                ->orWhere('name', 'like', "%{$studentFilter}%")
                ->pluck('id');
            $query->whereIn('t1.student_id', $matchedIds);
        }

        $candidateTwins = $query->get();
        $verifiedDuplicateTxIds = collect();

        $this->info("   Ditemukan " . count($candidateTwins) . " kandidat pasangan transaksi berdekatan.");

        $tableTwins = [];
        foreach ($candidateTwins as $pair) {
            // Verifikasi apakah kedua transaksi memang menargetkan bill_id yang sama
            $detailsT1 = DB::table('transaction_details')
                ->where('transaction_id', $pair->orig_tx_id)
                ->whereNull('deleted_at')
                ->pluck('bill_id')
                ->filter()
                ->values()
                ->toArray();

            $detailsT2 = DB::table('transaction_details')
                ->where('transaction_id', $pair->dup_tx_id)
                ->whereNull('deleted_at')
                ->pluck('bill_id')
                ->filter()
                ->values()
                ->toArray();

            // Cek irisan bill_id: jika ada bill_id yang sama, ini 100% duplikat klik ganda
            $sharedBills = array_intersect($detailsT1, $detailsT2);
            if (!empty($sharedBills)) {
                $student = Student::find($pair->student_id);
                $studentName = $student ? "{$student->name} ({$student->nis})" : "ID: {$pair->student_id}";

                $billNames = DB::table('bills')
                    ->join('bill_types', 'bills.bill_type_id', '=', 'bill_types.id')
                    ->whereIn('bills.id', $sharedBills)
                    ->pluck('bill_types.name')
                    ->implode(', ');

                $tableTwins[] = [
                    'Santri' => $studentName,
                    'Tagihan' => $billNames ?: 'Bill ID: ' . implode(',', $sharedBills),
                    'Nominal' => 'Rp ' . number_format($pair->pay_amount, 0, ',', '.'),
                    'Tx Asli' => substr($pair->orig_tx_id, 0, 8) . "... ({$pair->orig_time})",
                    'Tx Duplikat' => substr($pair->dup_tx_id, 0, 8) . "... ({$pair->dup_time})",
                ];

                $verifiedDuplicateTxIds->push($pair->dup_tx_id);
                $affectedStudentIds->push($pair->student_id);
            }
        }

        if (!empty($tableTwins)) {
            $this->table(['Santri', 'Tagihan', 'Nominal', 'Tx Asli (Simpan)', 'Tx Duplikat (Akan Dihapus)'], $tableTwins);
        } else {
            $this->info("   ✓ Tidak ada transaksi kembar beda ID yang menargetkan tagihan yang sama.");
        }

        // ---------------------------------------------------------------------
        // 2. DETEKSI DUPLIKAT TRANSACTION_DETAILS DALAM 1 TRANSAKSI
        // ---------------------------------------------------------------------
        $this->newLine();
        $this->info("2. Memeriksa Duplicate Detail (Satu Transaksi memiliki >1 baris untuk Bill yang sama)...");

        $dupDetailsQuery = DB::table('transaction_details')
            ->select('transaction_id', 'bill_id', DB::raw('count(*) as count'), DB::raw('group_concat(id) as detail_ids'))
            ->whereNotNull('bill_id')
            ->whereNull('deleted_at')
            ->groupBy('transaction_id', 'bill_id')
            ->having('count', '>', 1);

        $duplicateDetailsGroups = $dupDetailsQuery->get();
        $extraDetailIds = collect();

        foreach ($duplicateDetailsGroups as $group) {
            $ids = explode(',', $group->detail_ids);
            // Simpan yang pertama, hapus sisanya
            $keepId = array_shift($ids);
            foreach ($ids as $extraId) {
                $extraDetailIds->push($extraId);
            }

            $tx = DB::table('transactions')->where('id', $group->transaction_id)->first();
            if ($tx && $tx->student_id) {
                $affectedStudentIds->push($tx->student_id);
            }
        }

        $this->info("   Ditemukan " . $extraDetailIds->count() . " baris detail transaksi ganda di dalam transaksi yang sama.");

        // ---------------------------------------------------------------------
        // 3. DETEKSI DETAIL TRANSAKSI YANG CANCELLED / EXPIRED / ORPHAN TAPI MASIH NEMPEL DI BILL
        // ---------------------------------------------------------------------
        $this->newLine();
        $this->info("3. Memeriksa Detail Transaksi CANCELLED / EXPIRED / REJECTED / ORPHAN yang menempel di Bill...");

        $invalidDetails = DB::table('transaction_details')
            ->leftJoin('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->whereNotNull('transaction_details.bill_id')
            ->where(function ($q) {
                $q->whereNull('transactions.id') // Orphan
                    ->orWhereNotNull('transactions.deleted_at') // Soft deleted tx
                    ->orWhereNotIn('transactions.status', $this->paidStatuses); // CANCELLED/PENDING/EXPIRED
            })
            ->select('transaction_details.id', 'transaction_details.bill_id', 'transaction_details.amount', 'transactions.status as tx_status', 'transactions.student_id')
            ->get();

        $invalidDetailIds = $invalidDetails->pluck('id');
        foreach ($invalidDetails as $inv) {
            if ($inv->student_id) {
                $affectedStudentIds->push($inv->student_id);
            } else {
                $bill = DB::table('bills')->where('id', $inv->bill_id)->first();
                if ($bill && $bill->student_id) {
                    $affectedStudentIds->push($bill->student_id);
                }
            }
        }

        $this->info("   Ditemukan " . $invalidDetailIds->count() . " baris detail yang status transaksinya bukan PAID / sudah dibatalkan.");

        // ---------------------------------------------------------------------
        // EKSEKUSI PEMBERSIHAN (JIKA BUKAN DRY-RUN)
        // ---------------------------------------------------------------------
        $this->newLine();
        $uniqueDuplicateTxIds = $verifiedDuplicateTxIds->unique()->values();
        $allBadDetailIds = $extraDetailIds->merge($invalidDetailIds)->unique()->values();

        $this->info("RINGKASAN YANG AKAN DIBERSIHKAN:");
        $this->line(" - Transaksi Kembar / Duplikat   : " . $uniqueDuplicateTxIds->count() . " transaksi");
        $this->line(" - Detail Duplikat & Invalid     : " . $allBadDetailIds->count() . " baris");
        $this->line(" - Santri yang Terdampak         : " . $affectedStudentIds->unique()->count() . " santri");

        if ($uniqueDuplicateTxIds->isEmpty() && $allBadDetailIds->isEmpty()) {
            $this->info("✓ Database bersih! Tidak ada transaksi kembar atau detail invalid yang ditemukan.");
            return 0;
        }

        if ($isDryRun) {
            $this->warn("\n[DRY-RUN] Tidak ada data yang dihapus. Jalankan perintah tanpa --dry-run untuk mengeksekusi.");
            return 0;
        }

        if (!$this->confirm('Apakah Anda yakin ingin menghapus data duplikat/invalid di atas secara PERMANEN (forceDelete) dan menyinkronkan tagihan?', true)) {
            $this->warn("Operasi dibatalkan oleh pengguna.");
            return 0;
        }

        $this->info("\nMemulai proses pembersihan permanen...");

        DB::transaction(function () use ($uniqueDuplicateTxIds, $allBadDetailIds) {
            // 1. Hapus detail dari transaksi kembar
            if ($uniqueDuplicateTxIds->isNotEmpty()) {
                TransactionDetail::withTrashed()
                    ->whereIn('transaction_id', $uniqueDuplicateTxIds)
                    ->forceDelete();

                // Hapus transaksi kembar itu sendiri
                Transaction::withTrashed()
                    ->whereIn('id', $uniqueDuplicateTxIds)
                    ->forceDelete();
                $this->info("✓ Berhasil menghapus " . $uniqueDuplicateTxIds->count() . " transaksi kembar.");
            }

            // 2. Hapus detail ganda dan invalid
            if ($allBadDetailIds->isNotEmpty()) {
                TransactionDetail::withTrashed()
                    ->whereIn('id', $allBadDetailIds)
                    ->forceDelete();
                $this->info("✓ Berhasil menghapus " . $allBadDetailIds->count() . " baris detail ganda & invalid.");
            }
        });

        // 3. SINKRONISASI ULANG BILL UNTUK SELURUH SANTRI TERDAMPAK
        $uniqueStudentIds = $affectedStudentIds->unique()->filter()->values();
        $this->newLine();
        $this->info("Menyinkronkan tagihan untuk {$uniqueStudentIds->count()} santri terdampak...");

        $bar = $this->output->createProgressBar($uniqueStudentIds->count());
        $bar->start();

        foreach ($uniqueStudentIds as $sid) {
            TransactionService::syncStudentBillsFromPaidTransactions($sid);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("================================================================================");
        $this->info("✓ PEMBERSIHAN & SINKRONISASI SELESAI DENGAN SUKSES!");
        $this->info("================================================================================");

        return 0;
    }
}
