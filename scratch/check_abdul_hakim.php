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
    echo "=== ABDUL HAKIM WICAKSONO DB AUDIT ===\n\n";

    $stmt = $pdo->prepare("
        SELECT s.id, s.name, s.nis, s.gender, s.status, s.classroom_id, c.name as classroom_name, sch.name as school_name 
        FROM students s
        LEFT JOIN classrooms c ON s.classroom_id = c.id
        LEFT JOIN schools sch ON c.school_id = sch.id
        WHERE LOWER(s.name) LIKE '%abdul hakim%'
    ");
    $stmt->execute();
    $students = $stmt->fetchAll();
    foreach ($students as $st) {
        echo "Student ID: {$st['id']} | NIS: {$st['nis']} | Name: {$st['name']} | Class: {$st['classroom_name']} | School: {$st['school_name']}\n";

        // Query all bills for this student grouped by bill_type_id
        $stmtBills = $pdo->prepare("
            SELECT b.bill_type_id, bt.name as bill_type_name, bt.academic_year_id, ay.name as ay_name, 
                   count(b.id) as total_bills, sum(b.amount) as sum_amount, sum(b.paid_amount) as sum_paid,
                   b.deleted_at
            FROM bills b
            JOIN bill_types bt ON b.bill_type_id = bt.id
            LEFT JOIN academic_years ay ON bt.academic_year_id = ay.id
            WHERE b.student_id = ?
            GROUP BY b.bill_type_id, bt.name, bt.academic_year_id, ay.name, b.deleted_at
        ");
        $stmtBills->execute([$st['id']]);
        $billsGroup = $stmtBills->fetchAll();
        echo "Bills Grouped by BillType for {$st['name']}:\n";
        foreach ($billsGroup as $bg) {
            $del = $bg['deleted_at'] ? " (SOFT DELETED: {$bg['deleted_at']})" : " (ACTIVE)";
            echo "  - BillType ID: {$bg['bill_type_id']} | Name: '{$bg['bill_type_name']}' | AY: {$bg['ay_name']} | Bills: {$bg['total_bills']} | Sum Amt: Rp " . number_format($bg['sum_amount'], 0, ',', '.') . " | Sum Paid: Rp " . number_format($bg['sum_paid'], 0, ',', '.') . "{$del}\n";
        }
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
