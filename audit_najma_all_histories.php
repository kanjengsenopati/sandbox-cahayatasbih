<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$localPdo = new PDO('sqlite:database/local_replica.sqlite');
$localPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$ids = [
    '41b2044f-7871-4e50-a56e-7b7f3ab020a3',
    '67633690-b872-4ae1-a4e0-da672ff4d637',
];

foreach ($ids as $id) {
    echo "=== HISTORIES FOR ID: {$id} ===\n";
    $stmt = $localPdo->prepare("SELECT * FROM saldo_histories WHERE student_id = ?");
    $stmt->execute([$id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        echo "ID: {$r['id']} | At: {$r['created_at']} | Type: {$r['type']} | Amt: {$r['amount']} | Status: {$r['status']} | Desc: {$r['description']}\n";
    }
    if (empty($rows)) echo "NONE FOUND\n";
}
