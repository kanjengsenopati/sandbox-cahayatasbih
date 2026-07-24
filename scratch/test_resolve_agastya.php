<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$students = \App\Models\Student::with('classroom')->take(10)->get();
echo "Students in local DB:\n";
foreach ($students as $s) {
    echo "- NIS: {$s->nis} | Name: {$s->name} | Class: " . ($s->classroom?->name ?? 'NULL') . "\n";
}
