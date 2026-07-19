<?php

require __DIR__ . '/../vendor/autoload.php';

use Carbon\Carbon;

Carbon::setLocale('id');
$now = Carbon::now();

echo "Standard format: " . $now->format('d-M-Y , H : i') . "\n";
echo "Translated format: " . $now->translatedFormat('d-M-Y , H : i') . "\n";
echo "Uppercase Translated format: " . strtoupper($now->translatedFormat('d-M-Y , H : i')) . "\n";
