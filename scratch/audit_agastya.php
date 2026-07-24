<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$student = \App\Models\Student::with(['classroom.school', 'classroomHistories.classroom.school', 'classroomHistories.academicYear'])
    ->where('name', 'LIKE', '%AGASTYA%')
    ->first();

if (!$student) {
    echo "Student AGASTYA not found\n";
    exit;
}

echo "=== STUDENT INFO ===\n";
echo "ID: {$student->id}\n";
echo "NIS: {$student->nis}\n";
echo "Name: {$student->name}\n";
echo "Current Classroom: " . ($student->classroom?->name ?? 'None') . " (ID: {$student->classroom_id})\n";
echo "Current UPT / School: " . ($student->classroom?->school?->name ?? 'None') . "\n";

echo "\n=== CLASSROOM HISTORIES (" . $student->classroomHistories->count() . ") ===\n";
foreach ($student->classroomHistories as $ch) {
    echo " - AY: {$ch->academicYear?->name} (ID: {$ch->academic_year_id}) | Class: {$ch->classroom?->name} (ID: {$ch->classroom_id}) | UPT: " . ($ch->classroom?->school?->name ?? '-') . "\n";
}

echo "\n=== BILLS BY ACADEMIC YEAR ===\n";
$bills = \App\Models\Bill::with(['academicYear', 'classroom.school', 'billType'])
    ->where('student_id', $student->id)
    ->get()
    ->groupBy('academic_year_id');

foreach ($bills as $ayId => $bGroup) {
    $ayName = $bGroup->first()->academicYear?->name ?? 'Unknown AY';
    echo "AY: {$ayName} (ID: {$ayId}) - Total Bills: " . $bGroup->count() . "\n";
    $classroomsInBills = $bGroup->pluck('classroom.name')->unique()->filter()->values()->toArray();
    echo "   Classrooms in bills: " . (implode(', ', $classroomsInBills) ?: 'None') . "\n";
    foreach ($bGroup->take(5) as $b) {
        echo "   * Bill: {$b->billType?->name} | Month: {$b->month} | Year: {$b->year} | ClassInBill: " . ($b->classroom?->name ?? 'NULL') . "\n";
    }
}
