<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$conn = 'mysql_aplikasi';

// Count total CAT2 bills
$cat2 = DB::connection($conn)->select("
    SELECT COUNT(DISTINCT b.id) as total_bills, COUNT(DISTINCT s.id) as total_students
    FROM bills b
    JOIN students s ON s.id = b.student_id
    JOIN transaction_details td ON td.bill_id = b.id
    JOIN transactions t ON t.id = td.transaction_id
    JOIN academic_years ay ON ay.id = b.academic_year_id
    WHERE b.deleted_at IS NULL AND b.status = 'UNPAID' AND ay.name = '2026/2027'
    AND td.deleted_at IS NULL AND t.deleted_at IS NULL AND t.status = 'PAID'
");
echo "CAT2 Stats:\n";
echo "  Total tagihan anomali: " . $cat2[0]->total_bills . "\n";
echo "  Total siswa terdampak: " . $cat2[0]->total_students . "\n";

// Check average amount
$avg = DB::connection($conn)->select("
    SELECT AVG(b.amount) as avg_amount, SUM(b.amount) as total_amount
    FROM bills b
    JOIN transaction_details td ON td.bill_id = b.id
    JOIN transactions t ON t.id = td.transaction_id
    JOIN academic_years ay ON ay.id = b.academic_year_id
    WHERE b.deleted_at IS NULL AND b.status = 'UNPAID' AND ay.name = '2026/2027'
    AND td.deleted_at IS NULL AND t.deleted_at IS NULL AND t.status = 'PAID'
");
echo "  Rata-rata nominal: Rp " . number_format($avg[0]->avg_amount, 0, ',', '.') . "\n";
echo "  Total nominal terdampak: Rp " . number_format($avg[0]->total_amount, 0, ',', '.') . "\n";
