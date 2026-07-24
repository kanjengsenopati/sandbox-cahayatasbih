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
    
    echo "=== SEARCH BY NIS 330200127 ===\n";
    $stmt = $pdo->prepare("SELECT * FROM students WHERE nis LIKE '%330200127%' OR name LIKE '%ABDUL%' OR name LIKE '%WICAKSONO%'");
    $stmt->execute();
    $rows = $stmt->fetchAll();
    echo "Found " . count($rows) . " matches:\n";
    foreach ($rows as $st) {
        echo "ID: {$st['id']} | NIS: {$st['nis']} | Name: '{$st['name']}' | ClassroomID: {$st['classroom_id']}\n";
        
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
        foreach ($stmtBills->fetchAll() as $bg) {
            $del = $bg['deleted_at'] ? " (SOFT DELETED: {$bg['deleted_at']})" : " (ACTIVE)";
            echo "  - BillType ID: {$bg['bill_type_id']} | Name: '{$bg['bill_type_name']}' | AY: {$bg['ay_name']} | Bills: {$bg['total_bills']} | Sum Amt: Rp " . number_format($bg['sum_amount'], 0, ',', '.') . "{$del}\n";
        }
    }

    echo "\n=== ALL BILL TYPES IN DB ===\n";
    $stmtBt = $pdo->query("SELECT bt.id, bt.name, bt.bill_item_id, bi.name as item_name, ay.name as ay_name, count(b.id) as bills_count FROM bill_types bt LEFT JOIN bill_items bi ON bt.bill_item_id = bi.id LEFT JOIN academic_years ay ON bt.academic_year_id = ay.id LEFT JOIN bills b ON b.bill_type_id = bt.id WHERE bt.deleted_at IS NULL GROUP BY bt.id, bt.name, bt.bill_item_id, bi.name, ay.name");
    foreach ($stmtBt->fetchAll() as $bt) {
        echo "BillType ID: {$bt['id']} | Name: '{$bt['name']}' | Item: '{$bt['item_name']}' | AY: {$bt['ay_name']} | Bills Count: {$bt['bills_count']}\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
