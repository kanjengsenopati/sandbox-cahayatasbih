<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Bill;
use App\Models\Classroom;

echo "Total students count in DB: " . Student::count() . "\n";
echo "Sample student names:\n";
foreach (Student::limit(10)->get() as $s) {
    echo " - ID: {$s->id} | Name: '{$s->name}' | ClassroomID: {$s->classroom_id}\n";
}

$search1 = Student::whereRaw("LOWER(name) LIKE '%lutfiana%'")->get();
echo "LOWER Search Lutfiana count: " . $search1->count() . "\n";
foreach ($search1 as $s) {
    echo "  -> ID: {$s->id} | Name: '{$s->name}'\n";
}

$search2 = Student::whereRaw("LOWER(name) LIKE '%aliffia%'")->get();
echo "LOWER Search Aliffia count: " . $search2->count() . "\n";
foreach ($search2 as $s) {
    echo "  -> ID: {$s->id} | Name: '{$s->name}'\n";
}
