<?php
header('Content-Type: text/plain; charset=utf-8');

$logFile = __DIR__ . '/../storage/logs/laravel.log';

if (!file_exists($logFile)) {
    die("Log file not found at: $logFile");
}

$lines = 100;
if (isset($_GET['lines'])) {
    $lines = intval($_GET['lines']);
}

// Read last N lines
$file = new SplFileObject($logFile, 'r');
$file->seek(PHP_INT_MAX);
$totalLines = $file->key();

$start = max(0, $totalLines - $lines);
$file->seek($start);

echo "=== LAST $lines LINES OF LARAVEL LOG ===\n\n";
while (!$file->eof()) {
    echo $file->current();
    $file->next();
}
