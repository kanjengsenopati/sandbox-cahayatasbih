<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Menjalankan sinkronisasi massal paid_amount...\n";

// Menggunakan raw SQL update join agar sangat cepat (< 1 detik) untuk jutaan data
$query = "
UPDATE bills b
LEFT JOIN (
    SELECT td.bill_id, SUM(td.amount) as actual_paid
    FROM transaction_details td
    JOIN transactions t ON t.id = td.transaction_id
    WHERE t.status = 'PAID'
      AND td.deleted_at IS NULL
      AND t.deleted_at IS NULL
    GROUP BY td.bill_id
) tx ON b.id = tx.bill_id
SET b.paid_amount = COALESCE(tx.actual_paid, 0),
    b.status = CASE
        WHEN COALESCE(tx.actual_paid, 0) = 0 THEN 'UNPAID'
        WHEN COALESCE(tx.actual_paid, 0) >= b.amount THEN 'PAID'
        ELSE 'PARTIAL'
    END
WHERE b.paid_amount != COALESCE(tx.actual_paid, 0);
";

$affected = DB::update($query);
echo "Berhasil! Terdapat {$affected} tagihan (bills) yang tidak sinkron dan telah diperbaiki (di-revert ke nilai transaksi asli).\n";

