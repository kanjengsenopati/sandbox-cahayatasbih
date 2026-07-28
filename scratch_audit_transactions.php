<?php

$host = '103.193.179.146';
$db   = 'devctdb';
$user = 'devctdb';
$pass = '@PK@nkm0811';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_TIMEOUT            => 10,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    echo "=== RECENT TRANSACTIONS IN DB ===\n";
    $stmt = $pdo->query("SELECT t.id, t.student_id, t.status, t.created_at, s.name as student_name FROM transactions t LEFT JOIN students s ON t.student_id = s.id ORDER BY t.created_at DESC LIMIT 10");
    $txs = $stmt->fetchAll();
    print_r($txs);

    foreach ($txs as $tx) {
        $stmt = $pdo->prepare("SELECT td.* FROM transaction_details td WHERE td.transaction_id = ?");
        $stmt->execute([$tx['id']]);
        echo "Details for TX {$tx['id']} ({$tx['student_name']}):\n";
        print_r($stmt->fetchAll());
    }

} catch (\PDOException $e) {
    echo "CONNECTION ERROR: " . $e->getMessage() . "\n";
}
