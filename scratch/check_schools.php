<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== SCHOOLS (UPT) ===\n";
$schools = \App\Models\School::all();
foreach ($schools as $s) {
    echo "ID: {$s->id} | Name: {$s->name}\n";
}

echo "\n=== SAMPLE BILL TYPES & THEIR SCHOOL / CLASSROOM RELATIONS ===\n";
$bts = \App\Models\BillType::with(['academicYear'])->get();
foreach ($bts as $bt) {
    echo "BT ID: {$bt->id} | Name: {$bt->name} | Type: {$bt->type}\n";
}
