<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$student = App\Models\Student::where('nis', '330224093')->first();
if (!$student) {
    echo "Student not found\n";
    exit;
}

echo "Student: {$student->name} (Saldo: {$student->saldo}, Updated: {$student->updated_at})\n";

$histories = $student->saldoHistories()->latest()->take(5)->get();
foreach ($histories as $h) {
    echo "History ID: {$h->id}, Type: {$h->type}, Amount: {$h->amount}, Before: {$h->balance_before}, After: {$h->balance_after}, Date: {$h->created_at}\n";
}
