<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$localPdo = new PDO('sqlite:database/local_replica.sqlite');
$localPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$studentId = '5d51813e-05b1-49ac-8806-41ab4dfa6a72'; // AINUN NAJMA

echo "=== HISTORIES FOR AINUN NAJMA ({$studentId}) ===\n";
$stmt = $localPdo->prepare("
    SELECT id, created_at, type, amount, balance_before, balance_after, status, description 
    FROM saldo_histories 
    WHERE student_id = ? AND status = 'SUCCESS' AND deleted_at IS NULL
    ORDER BY created_at DESC, id DESC
    LIMIT 20
");
$stmt->execute([$studentId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $r) {
    echo "At: {$r['created_at']} | Type: {$r['type']} | Amt: {$r['amount']} | BEF: {$r['balance_before']} | AFT: {$r['balance_after']} | Desc: {$r['description']}\n";
}
