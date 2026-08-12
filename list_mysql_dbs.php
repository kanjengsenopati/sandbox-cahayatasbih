<?php
$host = '103.193.179.146';
$user = 'cahayatasbih';
$pass = 'DFyKHaf4wkCEmdZt';

try {
    $pdo = new PDO("mysql:host={$host}", $user, $pass);
    $stmt = $pdo->query("SHOW DATABASES");
    echo "=== Databases on {$host} ===\n";
    foreach ($stmt as $row) {
        echo $row[0] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
