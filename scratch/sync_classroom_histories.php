<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== SINKRONISASI PRESISI RIWAYAT KELAS (StudentClassroomHistory) ===\n";

// 1. Scan tb_bills with formal classroom_id (excluding PONDOK) per student & academic_year
$formalBillsWithClass = \App\Models\Bill::whereNotNull('classroom_id')
    ->whereNotNull('academic_year_id')
    ->whereHas('classroom', function ($q) {
        $q->where('name', '!=', 'PONDOK');
    })
    ->select('student_id', 'academic_year_id', 'classroom_id')
    ->distinct()
    ->get();

echo "Discovered " . $formalBillsWithClass->count() . " formal (student, academic_year, classroom) pairs from tb_bills.\n";

$createdCount = 0;
$updatedCount = 0;

foreach ($formalBillsWithClass as $row) {
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

// 2. Ensure current active classroom is recorded in StudentClassroomHistory for active academic year
$activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
if ($activeYear) {
    $students = \App\Models\Student::whereNotNull('classroom_id')->get();
    foreach ($students as $student) {
        if ($student->classroom && strtoupper($student->classroom->name) === 'PONDOK') {
            continue; // Skip pondok placeholder as active formal classroom
        }

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
        } elseif ($existing->classroom_id !== $student->classroom_id && $student->classroom_id) {
            $existing->update(['classroom_id' => $student->classroom_id]);
            $updatedCount++;
        }
    }
}

echo "FINISHED SINKRONISASI PRESISI:\n";
echo " - New histories created: {$createdCount}\n";
echo " - Existing histories updated: {$updatedCount}\n";
