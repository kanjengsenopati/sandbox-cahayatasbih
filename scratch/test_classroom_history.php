<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$student = \App\Models\Student::with(['classroom', 'classroomHistories.classroom', 'classroomHistories.academicYear'])->first();
if (!$student) {
    echo "No student found\n";
    exit;
}

echo "Student: {$student->name}\n";
echo "Current active classroom: " . ($student->classroom?->name ?? 'None') . "\n";
echo "Classroom Histories (" . $student->classroomHistories->count() . "):\n";
foreach ($student->classroomHistories as $h) {
    echo " - AY: {$h->academicYear?->name} (ID: {$h->academic_year_id}) | Class: {$h->classroom?->name}\n";
}
