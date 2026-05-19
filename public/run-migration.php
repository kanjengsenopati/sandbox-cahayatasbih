<?php
// Temporary script to alter student_permits status enum using active Laravel configuration
header('Content-Type: text/plain');

use Illuminate\Support\Facades\DB;

try {
    echo "Bootstrapping Laravel...\n";
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    echo "Laravel bootstrapped successfully!\n\n";

    $dbConfig = config('database.connections.mysql');
    echo "Active Database Config:\n";
    echo "Host: " . ($dbConfig['host'] ?? 'N/A') . "\n";
    echo "Database: " . ($dbConfig['database'] ?? 'N/A') . "\n";
    echo "Username: " . ($dbConfig['username'] ?? 'N/A') . "\n\n";

    // Check status column
    echo "Checking current column definition:\n";
    $result = DB::select("DESCRIBE student_permits status");
    print_r($result);
    echo "\n";

    // Alter table
    echo "Altering student_permits table to add 'pending_return' status...\n";
    DB::statement("ALTER TABLE student_permits MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'out', 'pending_return', 'returned', 'expired') NOT NULL DEFAULT 'pending'");
    echo "Alter query executed successfully!\n\n";

    // Verify change
    echo "Verifying new column definition:\n";
    $result = DB::select("DESCRIBE student_permits status");
    print_r($result);
    echo "\n";

    // Mark migration as done
    echo "Marking migration in migrations table...\n";
    $migrationName = '2026_05_19_190000_add_pending_return_to_student_permits_status';
    
    $exists = DB::table('migrations')->where('migration', $migrationName)->exists();

    if ($exists) {
        echo "Migration record already exists.\n";
    } else {
        $batch = DB::table('migrations')->max('batch') ?: 0;
        $batch++;
        
        DB::table('migrations')->insert([
            'migration' => $migrationName,
            'batch' => $batch
        ]);
        echo "Migration record inserted with batch $batch.\n";
    }

    echo "\nAll operations completed successfully!";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
