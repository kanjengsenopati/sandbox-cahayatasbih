<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check what DB the running app is actually using
echo "=== APP CONFIG ===\n";
echo "DB_CONNECTION: " . config('database.default') . "\n";
echo "DB_DATABASE: " . config('database.connections.' . config('database.default') . '.database') . "\n\n";

// Check all DB connections defined
$connections = array_keys(config('database.connections'));
echo "Available connections: " . implode(', ', $connections) . "\n\n";

// Search in LOCAL SQLITE first
$localPdo = new PDO('sqlite:' . config('database.connections.sqlite.database'));

// Find ANY saldo_history with amount 500000 created on 2026-08-03
$stmt = $localPdo->query("
    SELECT sh.student_id, sh.amount, sh.balance_before, sh.balance_after, sh.created_at, s.name, s.saldo
    FROM saldo_histories sh
    JOIN students s ON s.id = sh.student_id
    WHERE sh.amount = 500000 AND sh.created_at LIKE '2026-08-03%' AND sh.type = 'IN' AND sh.status = 'SUCCESS'
    LIMIT 10
");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "=== LOCAL: Saldo histories with 500k topup on 2026-08-03 ===\n";
foreach ($results as $r) {
    echo "Student: {$r['name']} | ID: {$r['student_id']} | BEF: {$r['balance_before']} | AFT: {$r['balance_after']} | Saldo: {$r['saldo']} | At: {$r['created_at']}\n";
}
if (empty($results)) echo "NONE FOUND\n";

// Now search VPS
echo "\n=== VPS: Same search ===\n";
$vpsPdo = new PDO(
    'mysql:host=' . env('DB_MASTER_HOST') . ';port=' . env('DB_MASTER_PORT') . ';dbname=' . env('DB_MASTER_DATABASE'),
    env('DB_MASTER_USERNAME'),
    env('DB_MASTER_PASSWORD')
);

$stmt2 = $vpsPdo->query("
    SELECT sh.student_id, sh.amount, sh.balance_before, sh.balance_after, sh.created_at, s.name, s.saldo
    FROM saldo_histories sh
    JOIN students s ON s.id = sh.student_id
    WHERE sh.amount = 500000 AND sh.created_at LIKE '2026-08-03%' AND sh.type = 'IN' AND sh.status = 'SUCCESS'
    LIMIT 10
");
$results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
foreach ($results2 as $r) {
    echo "Student: {$r['name']} | ID: {$r['student_id']} | BEF: {$r['balance_before']} | AFT: {$r['balance_after']} | Saldo: {$r['saldo']} | At: {$r['created_at']}\n";
}
if (empty($results2)) echo "NONE FOUND\n";

// Also search for balance_after = 578547 on VPS
echo "\n=== VPS: balance_after = 578547 ===\n";
$stmt3 = $vpsPdo->query("SELECT sh.student_id, s.name, sh.created_at, sh.amount, sh.balance_before, sh.balance_after FROM saldo_histories sh JOIN students s ON s.id = sh.student_id WHERE sh.balance_after = 578547 LIMIT 5");
$results3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
foreach ($results3 as $r) {
    echo "Student: {$r['name']} | ID: {$r['student_id']} | At: {$r['created_at']} | AMT: {$r['amount']} | {$r['balance_before']}->{$r['balance_after']}\n";
}
if (empty($results3)) echo "NONE FOUND\n";

// Search local for balance_after = 578547
echo "\n=== LOCAL: balance_after = 578547 ===\n";
$stmt4 = $localPdo->query("SELECT sh.student_id, s.name, sh.created_at, sh.amount, sh.balance_before, sh.balance_after FROM saldo_histories sh JOIN students s ON s.id = sh.student_id WHERE sh.balance_after = 578547 LIMIT 5");
$results4 = $stmt4->fetchAll(PDO::FETCH_ASSOC);
foreach ($results4 as $r) {
    echo "Student: {$r['name']} | ID: {$r['student_id']} | At: {$r['created_at']} | AMT: {$r['amount']} | {$r['balance_before']}->{$r['balance_after']}\n";
}
if (empty($results4)) echo "NONE FOUND\n";

// Total students count
$localCount = $localPdo->query("SELECT COUNT(*) FROM students WHERE deleted_at IS NULL")->fetchColumn();
$vpsCount = $vpsPdo->query("SELECT COUNT(*) FROM students WHERE deleted_at IS NULL")->fetchColumn();
echo "\n=== TOTALS ===\n";
echo "Local students: {$localCount}\n";
echo "VPS students: {$vpsCount}\n";
