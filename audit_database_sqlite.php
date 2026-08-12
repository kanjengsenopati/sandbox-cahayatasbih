<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dbFile = 'database/database.sqlite';
if (!file_exists($dbFile)) {
    echo "database.sqlite does not exist\n";
    exit;
}

echo "Size of database.sqlite: " . filesize($dbFile) . " bytes\n\n";

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Search for NAJMA in students table
    $stmt = $pdo->query("SELECT id, name, saldo FROM students WHERE name LIKE '%NAJMA%'");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "=== database.sqlite: Students matching NAJMA ===\n";
    foreach ($students as $s) {
        echo "ID: {$s['id']} | Name: {$s['name']} | Saldo: {$s['saldo']}\n";
        
        // Fetch histories
        $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM saldo_histories WHERE student_id = ?");
        $stmt2->execute([$s['id']]);
        $count = $stmt2->fetchColumn();
        echo "  History records: {$count}\n";
    }
    if (empty($students)) echo "NONE FOUND\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
