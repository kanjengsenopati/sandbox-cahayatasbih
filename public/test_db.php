<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== DATABASE DIAGNOSTIC ===\n";
try {
    require __DIR__.'/../vendor/autoload.php';
    echo "Autoload loaded.\n";
    
    $app = require_once __DIR__.'/../bootstrap/app.php';
    echo "Application instance obtained.\n";
    
    // Boot the application manually
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    echo "Laravel Bootstrapped successfully.\n";
    
    // Check DB config
    echo "DB Host: " . config('database.connections.mysql.host') . "\n";
    echo "DB Database: " . config('database.connections.mysql.database') . "\n";
    echo "DB Username: " . config('database.connections.mysql.username') . "\n";
    
    // Query directly using Laravel DB facade
    echo "\nExecuting query...\n";
    $result = Illuminate\Support\Facades\DB::select('SELECT 1 + 1 AS test');
    echo "Query Result: " . json_encode($result) . "\n";
    
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
}
