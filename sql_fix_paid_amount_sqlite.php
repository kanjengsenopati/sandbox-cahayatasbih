<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Menjalankan sinkronisasi massal paid_amount untuk SQLite...\n";

// Bikin view temporary atau pakai subquery langsung.
$query = "
UPDATE bills
SET paid_amount = COALESCE((
    SELECT SUM(td.amount)
    FROM transaction_details td
    JOIN transactions t ON t.id = td.transaction_id
    WHERE td.bill_id = bills.id
      AND t.status = 'PAID'
      AND td.deleted_at IS NULL
      AND t.deleted_at IS NULL
), 0)
WHERE paid_amount != COALESCE((
    SELECT SUM(td.amount)
    FROM transaction_details td
    JOIN transactions t ON t.id = td.transaction_id
    WHERE td.bill_id = bills.id
      AND t.status = 'PAID'
      AND td.deleted_at IS NULL
      AND t.deleted_at IS NULL
), 0);
";

$affected = DB::update($query);

// Update status
$queryStatus = "
UPDATE bills
SET status = CASE
    WHEN paid_amount = 0 THEN 'UNPAID'
    WHEN paid_amount >= amount THEN 'PAID'
    ELSE 'PARTIAL'
END
WHERE status != CASE
    WHEN paid_amount = 0 THEN 'UNPAID'
    WHEN paid_amount >= amount THEN 'PAID'
    ELSE 'PARTIAL'
END;
";

DB::update($queryStatus);

echo "Berhasil! Terdapat {$affected} tagihan (bills) yang tidak sinkron dan telah diperbaiki (di-revert ke nilai transaksi asli).\n";

