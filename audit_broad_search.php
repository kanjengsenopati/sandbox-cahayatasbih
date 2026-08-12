<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$localPdo = new PDO('sqlite:database/local_replica.sqlite');
$localPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 1. Search for ANY record with amount = 500000 in local DB
$stmt1 = $localPdo->query("
    SELECT sh.id, sh.student_id, sh.amount, sh.balance_before, sh.balance_after, sh.created_at, sh.status, s.name
    FROM saldo_histories sh
    JOIN students s ON s.id = sh.student_id
    WHERE sh.amount = 500000
    LIMIT 20
");
$res1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
echo "=== LOCAL: 500k transactions ===\n";
foreach ($res1 as $r) {
    echo "Student: {$r['name']} | ID: {$r['student_id']} | Status: {$r['status']} | At: {$r['created_at']} | BEF: {$r['balance_before']} | AFT: {$r['balance_after']}\n";
}
if (empty($res1)) echo "NONE FOUND\n";

// 2. Search for MUHAMAD NAJMA NAWALHUDAYA in local DB
$stmt2 = $localPdo->prepare("SELECT id, name, saldo FROM students WHERE name LIKE '%NAJMA%'");
$stmt2->execute();
$res2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
echo "\n=== LOCAL: Students with name LIKE 'NAJMA' ===\n";
foreach ($res2 as $s) {
    echo "ID: {$s['id']} | Name: {$s['name']} | Saldo: {$s['saldo']}\n";
}

// 3. Search VPS for ANY 500k transaction
$vpsPdo = new PDO(
    'mysql:host=' . env('DB_MASTER_HOST') . ';port=' . env('DB_MASTER_PORT') . ';dbname=' . env('DB_MASTER_DATABASE'),
    env('DB_MASTER_USERNAME'),
    env('DB_MASTER_PASSWORD')
);
$stmt3 = $vpsPdo->query("
    SELECT sh.id, sh.student_id, sh.amount, sh.balance_before, sh.balance_after, sh.created_at, sh.status, s.name
    FROM saldo_histories sh
    JOIN students s ON s.id = sh.student_id
    WHERE sh.amount = 500000
    LIMIT 20
");
$res3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
echo "\n=== VPS: 500k transactions ===\n";
foreach ($res3 as $r) {
    echo "Student: {$r['name']} | ID: {$r['student_id']} | Status: {$r['status']} | At: {$r['created_at']} | BEF: {$r['balance_before']} | AFT: {$r['balance_after']}\n";
}
if (empty($res3)) echo "NONE FOUND\n";
