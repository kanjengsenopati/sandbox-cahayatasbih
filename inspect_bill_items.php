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

echo "=== BILL ITEMS ===\n";
$stmt = $pdo->query("SELECT id, name FROM bill_items WHERE deleted_at IS NULL ORDER BY name");
foreach ($stmt->fetchAll() as $bi) {
    echo "ID: {$bi['id']} | Name: {$bi['name']}\n";
}

echo "\n=== EXISTING ZARKASI BILL TYPES ===\n";
$stmt = $pdo->query("SELECT id, name, bill_item_id, academic_year_id FROM bill_types WHERE name LIKE '%ZARKAS%'");
foreach ($stmt->fetchAll() as $bt) {
    echo "ID: {$bt['id']} | Name: {$bt['name']} | BillItem: {$bt['bill_item_id']} | AY: {$bt['academic_year_id']}\n";
}
