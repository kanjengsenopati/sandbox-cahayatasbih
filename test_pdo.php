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
    PDO::ATTR_TIMEOUT            => 5,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     echo "CONNECTED SUCCESSFULLY!\n";
     
     // Academic Years
     echo "=== ACADEMIC YEARS ===\n";
     $stmt = $pdo->query("SELECT id, name, is_active FROM academic_years");
     $ays = $stmt->fetchAll();
     print_r($ays);
     
     // Bill Types
     echo "=== BILL TYPES ===\n";
     $stmt = $pdo->query("SELECT id, name, academic_year_id, type FROM bill_types WHERE name LIKE '%SYAHR%' OR name LIKE '%APLIKASI%' OR name LIKE '%ZARKAS%'");
     $bts = $stmt->fetchAll();
     print_r($bts);

} catch (\PDOException $e) {
     echo "CONNECTION ERROR: " . $e->getMessage() . "\n";
}
