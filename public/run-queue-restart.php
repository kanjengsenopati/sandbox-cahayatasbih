<?php
// Temporary script to restart Laravel queue and clear config cache
header('Content-Type: text/plain');

use Illuminate\Support\Facades\Artisan;

try {
    echo "Bootstrapping Laravel...\n";
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    echo "Laravel bootstrapped successfully!\n\n";

    $dbConfig = config('database.connections.mysql');
    echo "Active Database Host: " . ($dbConfig['host'] ?? 'N/A') . "\n";
    echo "Active Database Name: " . ($dbConfig['database'] ?? 'N/A') . "\n\n";

    echo "Running config:clear...\n";
    Artisan::call('config:clear');
    echo Artisan::output() . "\n";

    echo "Running cache:clear...\n";
    Artisan::call('cache:clear');
    echo Artisan::output() . "\n";

    echo "Running queue:restart...\n";
    Artisan::call('queue:restart');
    echo Artisan::output() . "\n";

    echo "All operations completed successfully!";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
