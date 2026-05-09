<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$student = \App\Models\Student::where('name', 'like', '%ALIFFIA MARSYALIMA PUTRI%')->first();
$bills = \App\Models\Bill::with(['billType', 'academicYear'])->where('student_id', $student->id)->get();

foreach ($bills as $bill) {
    if (strpos($bill->billType->name, 'BIAYA APLIKASI') !== false) {
        $ayName = $bill->academicYear ? $bill->academicYear->name : 'N/A';
        echo "Type: {$bill->billType->name} | AY: {$ayName} | Period: {$bill->month}/{$bill->year} | Status: {$bill->status} | Amount: {$bill->amount}\n";
    }
}
