<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PaymentRate;
use App\Models\Student;

$rate = PaymentRate::where('name', 'like', '%KHIDMAT%')->first();
$billType = $rate->billType;

$nameToCheck = strtoupper($billType->name . ' ' . ($billType->billItem?->name ?? ''));
$isPondok = str_contains($nameToCheck, 'PONDOK') || str_contains($nameToCheck, 'PPTQ');

$query = Student::query();
if ($isPondok) {
    $query->whereIn('status', ['ACTIVE', 'GRADUATED']);
} else {
    $query->where('status', 'ACTIVE');
}

$studentIds = $rate->paymentRateStudents->pluck('student_id')->toArray();
$query->whereIn('id', $studentIds);

$students = $query->get(['id', 'name', 'classroom_id', 'gender', 'user_id', 'student_sub_status_id']);

echo "Students count: " . $students->count() . "\n";
foreach ($students as $s) {
    echo "- " . $s->name . "\n";
}

// Check Muslih specifically
$muslih = Student::where('name', 'like', '%MUSLIH HIDAYAT%')->first();
echo "\nMuslih ID: " . $muslih->id . "\n";
echo "Is Muslih in studentIds array? " . (in_array($muslih->id, $studentIds) ? 'Yes' : 'No') . "\n";
