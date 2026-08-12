<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$localPdo = new PDO('sqlite:database/local_replica.sqlite');
$localPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Local Transactions for NAJMA on 2026-08-03 and 2026-08-04 ===\n";
$stmt = $localPdo->query("
    SELECT sh.id, sh.created_at, sh.type, sh.amount, sh.balance_before, sh.balance_after, s.name, s.id as student_id
    FROM saldo_histories sh
    JOIN students s ON s.id = sh.student_id
    WHERE sh.created_at BETWEEN '2026-08-03 00:00:00' AND '2026-08-04 23:59:59'
      AND s.name LIKE '%NAJMA%'
    ORDER BY sh.created_at DESC
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $r) {
    echo "At: {$r['created_at']} | Student: {$r['name']} ({$r['student_id']}) | Type: {$r['type']} | Amt: {$r['amount']} | BEF: {$r['balance_before']} | AFT: {$r['balance_after']}\n";
}
if (empty($rows)) echo "NONE FOUND\n";
