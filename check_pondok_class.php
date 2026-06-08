<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Classroom;

$classroom = Classroom::where('name', 'PONDOK 2025')->first();
if (!$classroom) {
    echo "Classroom PONDOK 2025 not found!\n";
    exit;
}

$students = Student::where('classroom_id', $classroom->id)->get();
echo "Total students in PONDOK 2025: " . $students->count() . "\n";
echo "Male ('L'): " . $students->where('gender', 'L')->count() . "\n";
echo "Female ('P'): " . $students->where('gender', 'P')->count() . "\n";
echo "Unspecified: " . $students->filter(fn($s) => $s->gender !== 'L' && $s->gender !== 'P')->count() . "\n";

// Let's print some sample names to see if we can deduce gender
echo "\nSample names from PONDOK 2025:\n";
foreach ($students->take(15) as $s) {
    echo "Name: {$s->name}, Gender: '{$s->gender}'\n";
}
