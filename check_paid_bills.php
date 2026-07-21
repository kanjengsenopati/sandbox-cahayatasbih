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

echo "=== ALL PAID OR PARTIALLY PAID BILLS IN SYSTEM ===\n";
$stmt = $pdo->query("
    SELECT b.id, b.student_id, s.name as student_name, b.bill_type_id, bt.name as bill_type_name,
           b.academic_year_id, ay.name as ay_name, b.month, b.year, b.amount, b.paid_amount, b.status
    FROM bills b
    JOIN students s ON b.student_id = s.id
    JOIN bill_types bt ON b.bill_type_id = bt.id
    LEFT JOIN academic_years ay ON b.academic_year_id = ay.id
    WHERE b.deleted_at IS NULL AND (b.status = 'PAID' OR b.paid_amount > 0)
    ORDER BY b.year DESC, b.month DESC
");
$paidBills = $stmt->fetchAll();

echo "Total Paid/Partially Paid Bills Found: " . count($paidBills) . "\n";
foreach ($paidBills as $pb) {
    echo "Student: {$pb['student_name']} | Type: {$pb['bill_type_name']} | Period: {$pb['month']}/{$pb['year']} (AY: {$pb['ay_name']}) | Amount: {$pb['amount']} | Paid: {$pb['paid_amount']} | Status: {$pb['status']}\n";
}
