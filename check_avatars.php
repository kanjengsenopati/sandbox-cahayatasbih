<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;

$students = Student::whereNotNull('avatar')->take(5)->get();
foreach ($students as $student) {
    echo "ID: " . $student->id . " | Avatar: " . $student->avatar . "\n";
}
