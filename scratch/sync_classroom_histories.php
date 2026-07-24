<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== SINKRONISASI PRESISI RIWAYAT KELAS (StudentClassroomHistory) ===\n";

// 1. Fetch formal bills (excluding PONDOK)
$bills = \App\Models\Bill::whereNotNull('classroom_id')
    ->whereNotNull('academic_year_id')
    ->whereHas('classroom', function ($q) {
        $q->where('name', '!=', 'PONDOK');
    })
    ->get()
    ->groupBy(function ($b) {
        return $b->student_id . '_' . $b->academic_year_id;
    });

echo "Discovered " . $bills->count() . " unique (student, academic_year) groups from tb_bills.\n";

$createdCount = 0;
$updatedCount = 0;

foreach ($bills as $key => $bGroup) {
    $first = $bGroup->first();
    $studentId = $first->student_id;
    $ayId = $first->academic_year_id;

    // Pick the classroom_id that appears most frequently in bills for this academic year
    $bestClassroomId = $bGroup->groupBy('classroom_id')
        ->sortByDesc(fn($g) => $g->count())
        ->keys()
        ->first();

    if (!$bestClassroomId) continue;

    $existing = \App\Models\StudentClassroomHistory::where('student_id', $studentId)
        ->where('academic_year_id', $ayId)
        ->first();

    if (!$existing) {
        \App\Models\StudentClassroomHistory::create([
            'student_id' => $studentId,
            'academic_year_id' => $ayId,
            'classroom_id' => $bestClassroomId,
        ]);
        $createdCount++;
    } else {
        if ($existing->classroom_id !== $bestClassroomId) {
            $existing->update(['classroom_id' => $bestClassroomId]);
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
