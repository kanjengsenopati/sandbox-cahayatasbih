<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;

$unspecified = Student::where(function($q) {
    $q->whereNull('gender')->orWhere('gender', '');
})->get();

$names = $unspecified->pluck('name')->toArray();
sort($names);

foreach ($names as $i => $name) {
    echo ($i+1) . ". " . $name . "\n";
}
