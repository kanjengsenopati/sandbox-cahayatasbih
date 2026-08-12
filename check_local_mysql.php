<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306", 'root', '');
    $stmt = $pdo->query("SHOW DATABASES");
    echo "=== Local MySQL Databases ===\n";
    foreach ($stmt as $row) {
        echo $row[0] . "\n";
    }
} catch (Exception $e) {
    echo "Local MySQL is not running or credentials invalid: " . $e->getMessage() . "\n";
}
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306", 'cahayatasbih', 'DFyKHaf4wkCEmdZt');
    $stmt = $pdo->query("SHOW DATABASES");
    echo "=== Local MySQL Databases (with env credentials) ===\n";
    foreach ($stmt as $row) {
        echo $row[0] . "\n";
    }
} catch (Exception $e) {
    // Ignore
}
