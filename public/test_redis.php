<?php
header('Content-Type: text/plain; charset=utf-8');

try {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "Bootstrapped successfully.\n";
    echo "Testing Laravel Redis Facade Connection...\n";
    
    $ping = Illuminate\Support\Facades\Redis::ping();
    echo "Redis Ping Response: " . json_encode($ping) . "\n";
    
    Illuminate\Support\Facades\Redis::set('test_key', 'Hello Redis ' . time());
    echo "Set test_key: SUCCESS\n";
    echo "Get test_key: " . Illuminate\Support\Facades\Redis::get('test_key') . "\n";
    
} catch (Exception $e) {
    echo "Redis Test FAILED with error: " . $e->getMessage() . "\n";
}
