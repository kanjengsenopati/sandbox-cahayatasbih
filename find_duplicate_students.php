<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use Illuminate\Support\Facades\DB;

$duplicates = DB::table('students')
    ->whereNull('deleted_at')
    ->select('name', DB::raw('COUNT(*) as count'))
    ->groupBy('name')
    ->havingRaw('COUNT(*) > 1')
    ->get();

echo "Found " . $duplicates->count() . " duplicate student name groups.\n";

foreach ($duplicates as $dup) {
    $students = Student::where('name', $dup->name)
        ->with('classroom.school')
        ->get();
    
    echo "Group: '{$dup->name}'\n";
    foreach ($students as $s) {
        $billCount = $s->bills()->count();
        $school = $s->classroom->school->name ?? '-';
        $class = $s->classroom->name ?? '-';
        echo "  - ID: {$s->id} | NIS: {$s->nis} | UPT: {$school} | Class: {$class} | Bills: {$billCount} | Saldo: {$s->saldo}\n";
    }
}
