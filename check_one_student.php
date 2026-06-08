<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use Illuminate\Support\Facades\DB;

$student = Student::where('name', 'like', '%NIZAM DWI RAMADHAN%')->first();
if ($student) {
    echo "Eloquent Model Student:\n";
    echo "ID: {$student->id} | Name: '{$student->name}' | Gender: '{$student->gender}'\n";
    
    $raw = DB::table('students')->where('id', $student->id)->first();
    echo "Raw DB Row:\n";
    echo "ID: {$raw->id} | Name: '{$raw->name}' | Gender: '{$raw->gender}'\n";
} else {
    echo "Student not found!\n";
}
