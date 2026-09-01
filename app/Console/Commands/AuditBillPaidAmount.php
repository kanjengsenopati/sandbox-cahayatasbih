<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditBillPaidAmount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:audit-paid-amount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit komprehensif untuk mencari data tagihan yang tidak konsisten antara tabel bills dan transaksi.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Memulai audit komprehensif sinkronisasi data pembayaran...");

        // Query raw SQL menggunakan Subquery agar kompatibel dengan strict mode (MySQL/SQLite)
        $mismatches = DB::select("
            SELECT * FROM (
                SELECT 
                    b.id as bill_id,
                    s.name as student_name,
                    c.name as classroom_name,
                    bt.name as bill_type_name,
                    b.paid_amount as recorded_paid,
                    COALESCE(
                        (
                            SELECT SUM(td2.amount)
                            FROM transaction_details td2
                            JOIN transactions t2 ON t2.id = td2.transaction_id
                            WHERE td2.bill_id = b.id
                            AND td2.deleted_at IS NULL
                            AND t2.deleted_at IS NULL
                            AND t2.status = 'PAID'
                        ), 0
                    ) as actual_paid
                FROM bills b
                LEFT JOIN students s ON s.id = b.student_id
                LEFT JOIN classrooms c ON c.id = b.classroom_id
                LEFT JOIN bill_types bt ON bt.id = b.bill_type_id
                WHERE b.deleted_at IS NULL
            ) as summary
            WHERE summary.recorded_paid != summary.actual_paid
        ");

        if (empty($mismatches)) {
            $this->info("Kabar Baik! Semua tagihan konsisten. Tidak ditemukan masalah sinkronisasi.");
            return 0;
        }

        $this->warn("Ditemukan " . count($mismatches) . " data tagihan yang tidak konsisten!");

        $tableData = [];
        foreach ($mismatches as $row) {
            $tableData[] = [
                'ID Tagihan' => substr($row->bill_id, 0, 8) . '...', // Potong UUID agar tidak terlalu panjang
                'Nama Siswa' => substr($row->student_name, 0, 20),
                'Kelas' => $row->classroom_name ?? '-',
                'Jenis Tagihan' => substr($row->bill_type_name, 0, 15),
                'Tercatat (Bug)' => 'Rp ' . number_format($row->recorded_paid, 0, ',', '.'),
                'Riwayat Asli' => 'Rp ' . number_format($row->actual_paid, 0, ',', '.'),
                'Selisih (Error)' => 'Rp ' . number_format($row->recorded_paid - $row->actual_paid, 0, ',', '.'),
            ];
        }

        $this->table(
            ['ID Tagihan', 'Nama Siswa', 'Kelas', 'Jenis Tagihan', 'Tercatat (Bug)', 'Riwayat Asli', 'Selisih (Error)'],
            $tableData
        );

        $this->newLine();
        $this->info("Rekomendasi: Jalankan perintah 'php artisan bills:sync-paid-amount' untuk memperbaiki ketidakkonsistenan ini secara otomatis.");
        
        return 0;
    }
}
