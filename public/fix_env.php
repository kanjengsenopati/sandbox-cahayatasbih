<?php
header('Content-Type: text/plain; charset=utf-8');

if (!isset($_GET['secret']) || $_GET['secret'] !== 'ct_fix_2026') {
    die("Unauthorized.");
}

$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    die(".env file not found.");
}

$content = file_get_contents($envPath);
echo "=== CURRENT .env RELEVANT CONFIG ===\n";
preg_match_all('/^(SESSION_DRIVER|REDIS_PASSWORD|REDIS_HOST|REDIS_PORT)=.*/m', $content, $matches);
foreach ($matches[0] as $match) {
    echo $match . "\n";
}

// Ubah SESSION_DRIVER menjadi file
$newContent = preg_replace('/^SESSION_DRIVER=.*/m', 'SESSION_DRIVER=file', $content);

if ($newContent !== $content) {
    if (file_put_contents($envPath, $newContent) !== false) {
        echo "\nSESSION_DRIVER berhasil diubah menjadi 'file' di .env!\n";
    } else {
        echo "\nGagal menulis ke .env (masalah hak akses).\n";
    }
} else {
    echo "\nSESSION_DRIVER sudah bernilai 'file' atau tidak ada perubahan.\n";
}

// Bersihkan cache config secara programmatis
echo "\nRunning Artisan config commands programmatically...\n";
try {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    // Jalankan config:clear
    $exitCode = Illuminate\Support\Facades\Artisan::call('config:clear');
    echo "config:clear exit code: $exitCode\n";
    echo "Output:\n" . Illuminate\Support\Facades\Artisan::output() . "\n";
    
    // Jalankan config:cache
    $exitCode = Illuminate\Support\Facades\Artisan::call('config:cache');
    echo "config:cache exit code: $exitCode\n";
    echo "Output:\n" . Illuminate\Support\Facades\Artisan::output() . "\n";
    
} catch (Exception $e) {
    echo "Artisan call failed: " . $e->getMessage() . "\n";
}
