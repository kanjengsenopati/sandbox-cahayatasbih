<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$student = \App\Models\Student::with('user')->where('nis', 'like', '%332126001%')->orWhere('id', 10)->orWhere('name', 'like', '%FAIZ%')->first();
if ($student) {
    echo "Student Name: " . $student->name . "\n";
    echo "Status: " . $student->status . "\n";
    echo "Gender: " . $student->gender . "\n";
    echo "Jamaah Status: " . ($student->user ? $student->user->jamaah_status : 'No User') . "\n";
} else {
    echo "Student not found.\n";
}
