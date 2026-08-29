<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Menjalankan sinkronisasi massal paid_amount...\n";

$driver = DB::connection()->getDriverName();
echo "Database Driver: " . strtoupper($driver) . "\n";

if ($driver === 'mysql') {
    // MySQL Optimized Query
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
    echo "MySQL Sync Selesai. {$affected} baris diperbarui.\n";

} elseif ($driver === 'sqlite') {
    // SQLite Optimized Query
    $queryAmount = "
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
    
    $affected = DB::update($queryAmount);
    
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
    echo "SQLite Sync Selesai. {$affected} baris diperbarui.\n";
    
} else {
    echo "Driver database {$driver} belum didukung oleh script ini.\n";
}

echo "Proses sinkronisasi selesai.\n";
