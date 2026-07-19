<?php

$host = '103.193.179.146';
$db   = 'devctdb';
$user = 'devctdb';
$pass = '@PK@nkm0811';
$port = 3306;
$charset = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_TIMEOUT            => 5, // 5 seconds timeout
];

$dsn = "mysql:host=$host;dbname=$db;port=$port;charset=$charset";
try {
     echo "Connecting to database...\n";
     $pdo = new PDO($dsn, $user, $pass, $options);
     echo "Connected successfully!\n";
     
     // List all databases
     $stmt = $pdo->query("SHOW DATABASES");
     $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
     echo "Databases on host:\n";
     print_r($databases);
     
     // For each database, check if it has a 'students' table and search for ILLA
     foreach ($databases as $dbName) {
         if (in_array($dbName, ['information_schema', 'performance_schema', 'mysql', 'sys'])) {
             continue;
         }
         try {
             $pdo->exec("USE `$dbName`");
             $stmt = $pdo->query("SHOW TABLES LIKE 'students'");
             if ($stmt->fetch()) {
                 $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE name LIKE ?");
                 $stmt->execute(['%ILLA%']);
                 $count = $stmt->fetchColumn();
                 echo "Database `$dbName` has students table. Students matching ILLA: $count\n";
                 if ($count > 0) {
                     $stmt = $pdo->prepare("SELECT id, name, status FROM students WHERE name LIKE ?");
                     $stmt->execute(['%ILLA%']);
                     $results = $stmt->fetchAll();
                     foreach ($results as $res) {
                         echo "  - Student: {$res['name']} (ID: {$res['id']})\n";
                     }
                 }
             }
         } catch (\Exception $e) {
             echo "Error querying database `$dbName`: " . $e->getMessage() . "\n";
         }
     }
     exit(0);
     
     // Query bills
     $stmt = $pdo->prepare("SELECT id, month, year, amount, status FROM bills WHERE student_id = ?");
     $stmt->execute([$student['id']]);
     $bills = $stmt->fetchAll();
     echo "Total bills found: " . count($bills) . "\n";
     foreach ($bills as $bill) {
         echo "  Bill ID={$bill['id']}, Month={$bill['month']}, Year={$bill['year']}, Amount={$bill['amount']}, Status={$bill['status']}\n";
     }
     
} catch (\PDOException $e) {
     echo "PDO Connection/Query Error: " . $e->getMessage() . " (Code: " . $e->getCode() . ")\n";
}
