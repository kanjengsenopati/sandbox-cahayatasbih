<?php
// Temporary script to alter student_permits status enum to add pending_return
header('Content-Type: text/plain');

try {
    $host = "103.193.179.146";
    $port = 3306;
    $db   = "devctdb";
    $user = "devctdb";
    $pass = "@PK@nkm0811";
    $charset = "utf8mb4";

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    echo "Connecting to database...\n";
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Connected successfully!\n\n";

    // Check status column
    echo "Checking current column definition:\n";
    $stmt = $pdo->query("DESCRIBE student_permits status");
    $row = $stmt->fetch();
    print_r($row);
    echo "\n";

    // Alter table
    echo "Altering student_permits table to add 'pending_return' status...\n";
    $pdo->exec("ALTER TABLE student_permits MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'out', 'pending_return', 'returned', 'expired') NOT NULL DEFAULT 'pending'");
    echo "Alter query executed successfully!\n\n";

    // Verify change
    echo "Verifying new column definition:\n";
    $stmt = $pdo->query("DESCRIBE student_permits status");
    $row = $stmt->fetch();
    print_r($row);
    echo "\n";

    // Also run laravel migration table insert to mark the migration as done
    echo "Marking migration 2026_05_19_190000_add_pending_return_to_student_permits_status as completed in migrations table...\n";
    $migrationName = '2026_05_19_190000_add_pending_return_to_student_permits_status';
    
    // Check if migration is already in migrations table
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM migrations WHERE migration = ?");
    $stmt->execute([$migrationName]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        echo "Migration record already exists.\n";
    } else {
        // Find next batch number
        $batch = $pdo->query("SELECT MAX(batch) FROM migrations")->fetchColumn() ?: 0;
        $batch++;
        
        $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute([$migrationName, $batch]);
        echo "Migration record inserted with batch $batch.\n";
    }

    echo "\nAll operations completed successfully!";
} catch (\PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
} catch (\Exception $e) {
    echo "General Error: " . $e->getMessage() . "\n";
}
