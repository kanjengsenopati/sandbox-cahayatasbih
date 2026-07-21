<?php

$host = '103.193.179.146';
$db   = 'devctdb';
$user = 'devctdb';
$pass = '@PK@nkm0811';
$charset = 'utf8mb4';

$pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$queenaId = 'cd940c78-ea4b-4e76-b51b-f3821d3a453f';

// Find bills for Queena for 2026-07
$stmt = $pdo->prepare("SELECT id, bill_type_id, month, year, amount, paid_amount, status, created_at FROM bills WHERE student_id = ? AND month = 7 AND year = 2026 AND deleted_at IS NULL");
$stmt->execute([$queenaId]);
$bills = $stmt->fetchAll();

echo "Bills for Queena 2026-07:\n";
foreach ($bills as $b) {
    echo "ID: {$b['id']} | BillType: {$b['bill_type_id']} | Amount: {$b['amount']} | Paid: {$b['paid_amount']} | Status: {$b['status']} | Created: {$b['created_at']}\n";
    
    // If status == 'UNPAID', delete it to keep the PAID bill
    if ($b['status'] === 'UNPAID') {
        $pdo->prepare("UPDATE bills SET deleted_at = NOW() WHERE id = ?")->execute([$b['id']]);
        echo "   -> Soft-deleted duplicate UNPAID bill (ID: {$b['id']})\n";
    }
}
