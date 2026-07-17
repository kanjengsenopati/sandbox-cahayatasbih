<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== CAHAYA TASBIH SESSION DIAGNOSTIC ===\n\n";

// 1. PHP Process Info
echo "PHP version: " . PHP_VERSION . "\n";
echo "Current User (get_current_user): " . get_current_user() . "\n";
if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
    $processUser = posix_getpwuid(posix_geteuid());
    echo "PHP Process Owner (uid): " . $processUser['name'] . "\n";
} else {
    echo "PHP Process Owner: (posix functions not available, probably Windows or disabled)\n";
}

// 2. Storage Paths Check
$paths = [
    'storage' => __DIR__ . '/../storage',
    'storage/framework' => __DIR__ . '/../storage/framework',
    'storage/framework/sessions' => __DIR__ . '/../storage/framework/sessions',
    'storage/logs' => __DIR__ . '/../storage/logs',
];

foreach ($paths as $name => $path) {
    echo "\nChecking $name ($path):\n";
    if (!file_exists($path)) {
        echo "  - STATUS: NOT FOUND!\n";
        continue;
    }
    
    echo "  - STATUS: Exists\n";
    echo "  - Permissions: " . substr(sprintf('%o', fileperms($path)), -4) . "\n";
    echo "  - Writable: " . (is_writable($path) ? 'YES' : 'NO') . "\n";
    echo "  - Owner UID: " . fileowner($path) . "\n";
    if (function_exists('posix_getpwuid')) {
        $owner = posix_getpwuid(fileowner($path));
        echo "  - Owner Name: " . $owner['name'] . "\n";
    }
}

// 3. Try writing a file to sessions directory
echo "\nTesting file write to storage/framework/sessions:\n";
$sessionDir = $paths['storage/framework/sessions'];
if (is_dir($sessionDir) && is_writable($sessionDir)) {
    $testFile = $sessionDir . '/test_write_' . time() . '.txt';
    $writeResult = @file_put_contents($testFile, 'test');
    if ($writeResult !== false) {
        echo "  - Write test file: SUCCESS\n";
        $read = @file_get_contents($testFile);
        echo "  - Read test file: " . ($read === 'test' ? 'SUCCESS' : 'FAILED') . "\n";
        @unlink($testFile);
    } else {
        echo "  - Write test file: FAILED (Permission Denied or Disk Full)\n";
    }
} else {
    echo "  - Write test file: SKIPPED (Directory not writable or does not exist)\n";
}

// 4. Native PHP Session Check
echo "\nTesting native PHP session:\n";
if (session_status() === PHP_SESSION_NONE) {
    if (@session_start()) {
        echo "  - session_start(): SUCCESS\n";
        $_SESSION['diagnostic_time'] = time();
        echo "  - Session ID: " . session_id() . "\n";
    } else {
        echo "  - session_start(): FAILED\n";
    }
} else {
    echo "  - Native session already active.\n";
}

// 5. Bootstrap Laravel to check config
echo "\nBootstrapping Laravel application:\n";
try {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $request = Illuminate\Http\Request::capture();
    $response = $kernel->handle($request);
    
    echo "  - Laravel Boot: SUCCESS\n";
    echo "  - Session Driver: " . config('session.driver') . "\n";
    echo "  - Session Lifetime: " . config('session.lifetime') . "\n";
    echo "  - Session Cookie: " . config('session.cookie') . "\n";
    echo "  - Session Secure: " . (config('session.secure') ? 'TRUE' : 'FALSE') . "\n";
    echo "  - Session Domain: " . (config('session.domain') ?? 'NULL') . "\n";
    echo "  - CSRF Token: " . csrf_token() . "\n";
    echo "  - Is Request Secure (HTTPS): " . ($request->isSecure() ? 'YES' : 'NO') . "\n";
    
    // Cetak headers penting terkait proxy
    echo "\nRequest Headers:\n";
    echo "  - HTTP_X_FORWARDED_PROTO: " . ($request->server('HTTP_X_FORWARDED_PROTO') ?? 'NOT SET') . "\n";
    echo "  - HTTP_X_FORWARDED_PORT: " . ($request->server('HTTP_X_FORWARDED_PORT') ?? 'NOT SET') . "\n";
    echo "  - HTTP_X_FORWARDED_FOR: " . ($request->server('HTTP_X_FORWARDED_FOR') ?? 'NOT SET') . "\n";
    echo "  - HTTP_HOST: " . ($request->server('HTTP_HOST') ?? 'NOT SET') . "\n";
    
    // Tes persistensi session Laravel
    $session = $request->session();
    if ($session) {
        $counter = $session->get('diagnostic_counter', 0) + 1;
        $session->put('diagnostic_counter', $counter);
        $session->save(); // simpan manual
        echo "\nLaravel Session Persistence Test:\n";
        echo "  - Counter: " . $counter . " (Refresh halaman untuk melihat apakah nilai bertambah!)\n";
        echo "  - Session ID: " . $session->getId() . "\n";
    } else {
        echo "\nLaravel Session: NOT AVAILABLE\n";
    }
    
    $kernel->terminate($request, $response);
    
} catch (Exception $e) {
    echo "  - Laravel Boot: FAILED with error: " . $e->getMessage() . "\n";
}
