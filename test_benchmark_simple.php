<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$t0 = microtime(true);
$schools = \App\Models\School::orderBy('name')->hasSchool()->get();
$t1 = microtime(true);
echo "1. Schools: " . round($t1 - $t0, 4) . "s\n";

$academicYears = \App\Models\AcademicYear::where(function($query) {
    $query->where('is_active', true)
            ->orWhereHas('billTypes', function ($q) {
                $q->where('is_visible', true);
            });
})->orderBy('start_year', 'desc')->get();
$t2 = microtime(true);
echo "2. AcademicYears: " . round($t2 - $t1, 4) . "s\n";
