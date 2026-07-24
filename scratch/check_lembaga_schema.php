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
    PDO::ATTR_TIMEOUT            => 15,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    echo "=== SCHOOLS / LEMBAGA IN DB ===\n";
    $stmt = $pdo->query("SELECT * FROM schools");
    foreach ($stmt->fetchAll() as $s) {
        echo "School ID: {$s['id']} | Name: '{$s['name']}' | Type: '{$s['type']}'\n";
    }

    echo "\n=== CLASSROOMS & STUDENTS PER SCHOOL ===\n";
    $stmt = $pdo->query("
        SELECT c.school_id, sch.name as school_name, count(DISTINCT c.id) as classroom_count, count(st.id) as student_count
        FROM classrooms c
        LEFT JOIN schools sch ON c.school_id = sch.id
        LEFT JOIN students st ON st.classroom_id = c.id
        WHERE c.deleted_at IS NULL
        GROUP BY c.school_id, sch.name
    ");
    foreach ($stmt->fetchAll() as $r) {
        echo "School: {$r['school_name']} (ID: {$r['school_id']}) -> Classrooms: {$r['classroom_count']}, Active Students: {$r['student_count']}\n";
    }

    echo "\n=== ALL CLASSROOMS WITH SCHOOL ===\n";
    $stmt = $pdo->query("
        SELECT c.id, c.name as classroom_name, sch.name as school_name 
        FROM classrooms c 
        LEFT JOIN schools sch ON c.school_id = sch.id 
        WHERE c.deleted_at IS NULL 
        ORDER BY sch.name, c.name
    ");
    foreach ($stmt->fetchAll() as $r) {
        echo "Classroom ID: {$r['id']} | Name: '{$r['classroom_name']}' | School: '{$r['school_name']}'\n";
    }

    $stmt = $pdo->query("SELECT count(*) as total_students FROM students WHERE deleted_at IS NULL");
    echo "\nTotal Active Students in DB: " . $stmt->fetch()['total_students'] . "\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
