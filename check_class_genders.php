<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Classroom;

$classrooms = Classroom::orderBy('name')->get();
echo "Classroom Gender Distribution (excluding unspecified):\n";
echo "Classroom Name | Male (L) | Female (P) | Unspecified\n";
echo "-----------------------------------------------------\n";

foreach ($classrooms as $c) {
    $students = Student::where('classroom_id', $c->id)->get();
    $total = $students->count();
    $males = $students->where('gender', 'L')->count();
    $females = $students->where('gender', 'P')->count();
    $unspecified = $students->filter(fn($s) => $s->gender !== 'L' && $s->gender !== 'P')->count();
    
    if ($total > 0) {
        echo "{$c->name} | L: $males | P: $females | Unspecified: $unspecified\n";
    }
}
