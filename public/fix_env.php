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

// Bersihkan cache config
echo "\nRunning config:clear...\n";
$phpPath = '/www/server/php/84/bin/php';
$artisanPath = __DIR__ . '/../artisan';

$output = [];
$return_var = 0;
exec("$phpPath $artisanPath config:clear 2>&1", $output, $return_var);
echo implode("\n", $output) . "\nCode: $return_var\n";

$output = [];
$return_var = 0;
exec("$phpPath $artisanPath config:cache 2>&1", $output, $return_var);
echo "\nRunning config:cache...\n";
echo implode("\n", $output) . "\nCode: $return_var\n";
