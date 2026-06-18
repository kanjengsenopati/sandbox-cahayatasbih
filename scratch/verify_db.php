<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $students = \App\Models\Student::take(5)->get();
    echo "Total students: " . \App\Models\Student::count() . "\n";
    foreach ($students as $student) {
        echo "ID: " . $student->id . " | Name: '" . $student->name . "' | Nickname: '" . $student->nickname . "' | City: '" . $student->city . "' | Province: '" . $student->province . "'\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
