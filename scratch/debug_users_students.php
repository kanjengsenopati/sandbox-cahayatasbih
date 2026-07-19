<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;

$students = Student::with('user')->get();
echo "Total students: " . $students->count() . "\n";
foreach ($students as $student) {
    $user = $student->user;
    $userName = $user ? $user->name : 'N/A';
    $userEmail = $user ? $user->email : 'N/A';
    echo "Student: name='{$student->name}', id='{$student->id}', status='{$student->status}'\n";
    echo "  - Parent/User: name='{$userName}', email='{$userEmail}', user_id='{$student->user_id}'\n";
}
