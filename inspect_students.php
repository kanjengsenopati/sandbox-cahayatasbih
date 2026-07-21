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

$stmt = $pdo->query("
    SELECT s.id, s.nis, s.name, s.gender, c.name as classroom_name, sch.name as school_name
    FROM students s
    LEFT JOIN classrooms c ON s.classroom_id = c.id
    LEFT JOIN schools sch ON c.school_id = sch.id
    WHERE s.deleted_at IS NULL
");
$students = $stmt->fetchAll();

echo "=== ACTIVE STUDENTS IN SYSTEM (" . count($students) . ") ===\n";
foreach ($students as $s) {
    echo "ID: {$s['id']} | NIS: {$s['nis']} | Name: {$s['name']} | Class: {$s['classroom_name']} ({$s['school_name']})\n";
}

echo "\n=== ALL BILL TYPES IN SYSTEM ===\n";
$stmt = $pdo->query("SELECT id, name, type, academic_year_id FROM bill_types WHERE deleted_at IS NULL ORDER BY name");
foreach ($stmt->fetchAll() as $bt) {
    echo "ID: {$bt['id']} | Name: {$bt['name']} | AY: {$bt['academic_year_id']}\n";
}
