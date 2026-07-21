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

$stmt = $pdo->query("SELECT id, name, bill_item_id FROM bill_types WHERE academic_year_id = '52f11471-6fb0-46ec-91d9-2a3998038232'");
foreach ($stmt->fetchAll() as $row) {
    echo "ID: {$row['id']} | Name: {$row['name']} | BillItem: {$row['bill_item_id']}\n";
}
