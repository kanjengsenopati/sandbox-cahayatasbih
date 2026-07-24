<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== SINKRONISASI RIWAYAT KELAS (StudentClassroomHistory) ===\n";

// 1. Scan tb_bills with non-null classroom_id and academic_year_id
$billsWithClass = \App\Models\Bill::whereNotNull('classroom_id')
    ->whereNotNull('academic_year_id')
    ->select('student_id', 'academic_year_id', 'classroom_id')
    ->distinct()
    ->get();

echo "Discovered " . $billsWithClass->count() . " unique (student, academic_year, classroom) pairs from tb_bills.\n";

$createdCount = 0;
$updatedCount = 0;

foreach ($billsWithClass as $row) {
    $existing = \App\Models\StudentClassroomHistory::where('student_id', $row->student_id)
        ->where('academic_year_id', $row->academic_year_id)
        ->first();

    if (!$existing) {
        \App\Models\StudentClassroomHistory::create([
            'student_id' => $row->student_id,
            'academic_year_id' => $row->academic_year_id,
            'classroom_id' => $row->classroom_id,
        ]);
        $createdCount++;
    } else {
        if ($existing->classroom_id !== $row->classroom_id) {
            $existing->update(['classroom_id' => $row->classroom_id]);
            $updatedCount++;
        }
    }
}

// 2. Also ensure current active classroom is recorded in StudentClassroomHistory for active academic year
$activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
if ($activeYear) {
    $students = \App\Models\Student::whereNotNull('classroom_id')->get();
    foreach ($students as $student) {
        $existing = \App\Models\StudentClassroomHistory::where('student_id', $student->id)
            ->where('academic_year_id', $activeYear->id)
            ->first();

        if (!$existing) {
            \App\Models\StudentClassroomHistory::create([
                'student_id' => $student->id,
                'academic_year_id' => $activeYear->id,
                'classroom_id' => $student->classroom_id,
            ]);
            $createdCount++;
        }
    }
}

echo "FINISHED SINKRONISASI:\n";
echo " - New histories created: {$createdCount}\n";
echo " - Existing histories updated: {$updatedCount}\n";
