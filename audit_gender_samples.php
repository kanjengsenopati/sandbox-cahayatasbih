<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;

$unspecified = Student::where(function($q) {
    $q->whereNull('gender')->orWhere('gender', '');
})->get();

echo "Total unspecified: " . $unspecified->count() . "\n";
echo "Created dates for unspecified:\n";
$dates = [];
foreach ($unspecified as $s) {
    $date = $s->created_at ? substr($s->created_at, 0, 10) : 'NO DATE';
    if (!isset($dates[$date])) {
        $dates[$date] = 0;
    }
    $dates[$date]++;
}
ksort($dates);
foreach ($dates as $date => $count) {
    echo "Date: $date -> Count: $count\n";
}

echo "\nSample names and other columns:\n";
foreach ($unspecified->take(20) as $s) {
    $className = $s->classroom->name ?? 'None';
    $schoolName = $s->classroom->school->name ?? 'None';
    echo "Name: {$s->name} | Classroom: {$className} | School: {$schoolName} | Created: {$s->created_at}\n";
}
