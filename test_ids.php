<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$student = \App\Models\Student::where('name', 'like', '%MUHAMMAD ABYAN PRATAMA%')->first();
$rate = \App\Models\PaymentRate::latest()->first();

echo "Student ID: " . $student->id . "\n";
echo "Rate ID: " . $rate->id . "\n";
