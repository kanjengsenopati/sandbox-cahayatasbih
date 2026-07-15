<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;

$count = Student::withTrashed()->count();
echo "Total students in DB (including deleted): {$count}\n";

$statuses = Student::withTrashed()->select('status', \Illuminate\Support\Facades\DB::raw('count(*) as total'))->groupBy('status')->get();
foreach ($statuses as $s) {
    echo "- Status: {$s->status} | Count: {$s->total}\n";
}

$students = Student::withTrashed()->limit(20)->get();
foreach ($students as $student) {
    echo "- Name: {$student->name} | Status: {$student->status}\n";
}
