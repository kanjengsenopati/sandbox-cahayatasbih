<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Student;

echo "=== DB CHECK ===\n";
echo "Default Host: " . config('database.connections.mysql.host') . "\n";
echo "Default DB: " . config('database.connections.mysql.database') . "\n";

try {
    $count = Student::count();
    echo "Student count in current DB: " . $count . "\n";
} catch (\Throwable $e) {
    echo "Error connecting to current DB: " . $e->getMessage() . "\n";
}

echo "\n=== LOCALHOST MYSQL CHECK ===\n";
try {
    $localPdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    echo "Local MySQL (127.0.0.1:3306) is AVAILABLE!\n";
    $stmt = $localPdo->query("SHOW DATABASES");
    $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Databases in local MySQL: " . implode(', ', $dbs) . "\n";
} catch (\Throwable $e) {
    echo "Local MySQL (127.0.0.1:3306) connection failed: " . $e->getMessage() . "\n";
}
