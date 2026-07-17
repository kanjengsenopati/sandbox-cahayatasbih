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

// Fast tail implementation using fseek
$handle = fopen($logFile, 'r');
if (!$handle) {
    die("Failed to open log file.");
}

$linecounter = $lines;
$pos = -2;
$beginning = false;
$text = [];

while ($linecounter > 0) {
    $t = " ";
    while ($t != "\n") {
        if (fseek($handle, $pos, SEEK_END) == -1) {
            $beginning = true;
            break;
        }
        $t = fgetc($handle);
        $pos--;
    }
    $linecounter--;
    if ($beginning) {
        rewind($handle);
    }
    $line = fgets($handle);
    if ($line !== false) {
        $text[] = $line;
    }
    if ($beginning) break;
}
fclose($handle);

echo "=== LAST $lines LINES OF LARAVEL LOG (FAST TAIL) ===\n\n";
echo implode("", array_reverse($text));
