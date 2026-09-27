<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Rates: " . \App\Models\PaymentRate::count() . "\n";
echo "PR_Students: " . \App\Models\PaymentRateStudent::count() . "\n";
echo "PR_Items: " . \App\Models\PaymentRateItem::count() . "\n";
echo "PR_Classrooms: " . \App\Models\PaymentRateClassroom::count() . "\n";
echo "BillTypes: " . \App\Models\BillType::count() . "\n";
echo "Bills: " . \App\Models\Bill::count() . "\n";
echo "Students: " . \App\Models\Student::count() . "\n";
