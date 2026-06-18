<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $provinceCount = \App\Models\Province::count();
    $regencyCount = \App\Models\Regency::count();
    echo "Provinces count: " . $provinceCount . "\n";
    echo "Regencies count: " . $regencyCount . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
