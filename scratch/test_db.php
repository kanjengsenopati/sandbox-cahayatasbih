<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Total Students: " . \App\Models\Student::count() . "\n";
echo "Sample Students:\n";
foreach (\App\Models\Student::take(20)->get() as $s) {
    echo "ID: {$s->id} | Name: {$s->name} | NIS: {$s->nis}\n";
}

echo "\nSearching for FAQIH / KHARY / 7G:\n";
$students = \App\Models\Student::where('name', 'like', '%FAQIH%')
    ->orWhere('name', 'like', '%KHARY%')
    ->orWhere('nis', 'like', '%332126%')
    ->get();

foreach ($students as $s) {
    echo "Found: ID {$s->id} | Name: {$s->name} | NIS: {$s->nis}\n";
}
