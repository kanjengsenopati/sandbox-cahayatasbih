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

function generate_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

$queenaId = 'cd940c78-ea4b-4e76-b51b-f3821d3a453f';
$ay2026Id = '52f11471-6fb0-46ec-91d9-2a3998038232';

// Queena's classroom
$stmtSt = $pdo->prepare("SELECT classroom_id FROM students WHERE id = ?");
$stmtSt->execute([$queenaId]);
$cId = $stmtSt->fetchColumn();

// 1. Biaya Aplikasi 2026-07
$aplikasiBtId = '92201c2f-d5e2-44cb-b28c-4f17be5c3616';
$stmtItem = $pdo->prepare("SELECT pri.id FROM payment_rate_items pri JOIN payment_rates pr ON pri.payment_rate_id = pr.id WHERE pr.bill_type_id = ? AND month = 7 AND year = 2026 AND pri.deleted_at IS NULL AND pr.deleted_at IS NULL");
$stmtItem->execute([$aplikasiBtId]);
$appItemId = $stmtItem->fetchColumn();

$stmtCheck = $pdo->prepare("SELECT id FROM bills WHERE student_id = ? AND bill_type_id = ? AND month = 7 AND year = 2026 AND deleted_at IS NULL");
$stmtCheck->execute([$queenaId, $aplikasiBtId]);
if (!$stmtCheck->fetchColumn()) {
    $pdo->prepare("INSERT INTO bills (id, bill_type_id, student_id, classroom_id, academic_year_id, month, year, amount, paid_amount, status, payment_rate_item_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 7, 2026, 10000, 0, 'UNPAID', ?, NOW(), NOW())")->execute([generate_uuid(), $aplikasiBtId, $queenaId, $cId, $ay2026Id, $appItemId]);
    echo "Restored Queena Biaya Aplikasi 2026-07 (Rp 10.000)\n";
}

// 2. Zarkasi 2026-07
$zarkasiBtId = '4da85847-77ab-42f7-9cdb-6d185c29c81b';
$stmtItem->execute([$zarkasiBtId]);
$zarItemId = $stmtItem->fetchColumn();

$stmtCheck->execute([$queenaId, $zarkasiBtId]);
if (!$stmtCheck->fetchColumn()) {
    $pdo->prepare("INSERT INTO bills (id, bill_type_id, student_id, classroom_id, academic_year_id, month, year, amount, paid_amount, status, payment_rate_item_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 7, 2026, 100000, 0, 'UNPAID', ?, NOW(), NOW())")->execute([generate_uuid(), $zarkasiBtId, $queenaId, $cId, $ay2026Id, $zarItemId]);
    echo "Restored Queena Zarkasi 2026-07 (Rp 100.000)\n";
}
