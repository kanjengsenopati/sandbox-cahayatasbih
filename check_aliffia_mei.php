<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$student = \App\Models\Student::where('name', 'like', '%ALIFFIA MARSYALIMA PUTRI%')->first();

$bills = \App\Models\Bill::with(['billType', 'academicYear'])
    ->where('student_id', $student->id)
    ->where('month', 5)
    ->where('year', 2026)
    ->get();

foreach ($bills as $bill) {
    echo "ID: {$bill->id} | TypeID: {$bill->bill_type_id} | TypeName: {$bill->billType->name} | AY: {$bill->academicYear->name} | Period: {$bill->month}/{$bill->year} | Status: {$bill->status} | Amount: {$bill->amount}\n";
}
